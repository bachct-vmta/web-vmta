{{--
    Reusable VI→EN translate button + engine.

    Usage:
        @include('content::admin.partials._translate-button', ['spec' => [...]])

    All DOM lookups are scoped to the <form> that contains the button, so multiple
    section forms on one page (each reusing the same translations[vi][...] field
    names) don't collide. Falls back to document scope if the button is not in a form.

    $spec shape (all keys optional unless noted):
    [
        'endpoint' => '/admin/translate',   // default
        'chunk'    => 50,                    // max fields per request
        'scalars'  => [
            ['from' => 'translations[vi][title]', 'to' => 'translations[en][title]',
             'kind' => 'text'|'textarea'|'ckeditor', 'slugTo' => 'translations[en][slug]'],
            // 'verbatim' => true  → copy source→target as-is (no translation)
        ],
        'repeaters' => [
            // shape 'plain'  — fixed DOM inputs by name (incl. nested [bullets][b]).
            //   subfields may set 'verbatim'=>true to copy VI→EN without translating
            //   (e.g. per-item image_url / icon_media_id).
            ['shape' => 'plain', 'rows' => 2,
             'fromBase' => 'translations[vi][items]', 'toBase' => 'translations[en][items]',
             'subfields' => [['name'=>'audience'], ['name'=>'bullets','array'=>true,'max'=>8]]],

            // shape 'alpine-flat' / 'alpine-nested' — items live in Alpine state.
            //   EN items are CLONED from VI (carrying media ids, urls, emails, phones,
            //   and any other untranslated keys) and then the listed subfields are
            //   overwritten with their translations. Only list TRANSLATABLE subfields.
            ['shape' => 'alpine-nested',
             'viRootSel' => '[data-tr-repeater="strengths"][data-locale="0"]',
             'enRootSel' => '[data-tr-repeater="strengths"][data-locale="1"]',
             'subfields' => [['path'=>'title'], ['path'=>'bullets','array'=>true],
                             ['path'=>'cta_primary.label']]],
        ],
    ]

    The button only renders for the EN side; the engine reads VI fields and writes EN.
--}}

@php $spec = $spec ?? []; @endphp

<div x-data="{ vmtaTranslating: false, vmtaError: '' }" class="inline-flex flex-col items-start gap-1">
    <button type="button"
            x-on:click="vmtaTranslate(@js($spec), $data, $el)"
            :disabled="vmtaTranslating"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
        <span x-show="!vmtaTranslating">🌐 Dịch từ Tiếng Việt</span>
        <span x-show="vmtaTranslating" x-cloak class="flex items-center gap-1">
            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Đang dịch...
        </span>
    </button>
    <p x-show="vmtaError" x-text="vmtaError" x-cloak class="text-xs text-red-600"></p>
</div>

@once
<script>
(function () {
    'use strict';

    // Scope for all DOM lookups during a translate run (the enclosing <form>, or
    // document as fallback). Set per run; runs are serialized (button disabled while busy).
    var ROOT = null;
    function scope() { return ROOT || document; }

    function fieldEl(name) {
        return scope().querySelector('[name="' + name + '"]');
    }

    function slugify(s) {
        return (s || '')
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd').replace(/Đ/g, 'D')
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function getPath(o, p) {
        if (o == null) return undefined;
        if (p.indexOf('.') < 0) return o[p];
        return p.split('.').reduce(function (a, k) { return a == null ? undefined : a[k]; }, o);
    }
    function setPath(o, p, v) {
        if (p.indexOf('.') < 0) { o[p] = v; return v; }
        var ks = p.split('.'), cur = o;
        for (var i = 0; i < ks.length - 1; i++) { if (cur[ks[i]] == null) cur[ks[i]] = {}; cur = cur[ks[i]]; }
        cur[ks[ks.length - 1]] = v;
        return v;
    }

    // ---- field read/write (scalars) -------------------------------------
    function readField(name, kind) {
        var el = fieldEl(name);
        if (!el) return '';
        if (kind === 'ckeditor') return (el._ckEditor && el._ckEditor.getData()) || '';
        return el.value || '';
    }
    function writeField(f, val) {
        if (!val) return;
        var el = fieldEl(f.to);
        if (!el) return;
        if (f.kind === 'ckeditor') { if (el._ckEditor) el._ckEditor.setData(val); }
        else { el.value = val; }
        if (f.slugTo) { var s = fieldEl(f.slugTo); if (s) s.value = slugify(val); }
    }

    // ---- collectors -----------------------------------------------------
    // Each collector: { sources(): string[], apply(translated: string[]): void }

    function scalarCollector(scalars) {
        return {
            sources: function () {
                return scalars.filter(function (f) { return !f.verbatim; })
                              .map(function (f) { return readField(f.from, f.kind); });
            },
            apply: function (t) {
                var ti = 0;
                scalars.forEach(function (f) {
                    if (f.verbatim) writeField(f, readField(f.from, f.kind)); // copy VI→EN as-is
                    else writeField(f, t[ti++]);
                });
            },
        };
    }

    function name3(base, i, sub, bi) {
        return bi == null ? (base + '[' + i + '][' + sub + ']')
                          : (base + '[' + i + '][' + sub + '][' + bi + ']');
    }

    // Number of *visible* bullet rows on the VI side for an array subfield.
    // _bullets-repeater keeps all `max` inputs in the DOM and only toggles visibility
    // via an Alpine `count`; its "remove" button decrements `count` WITHOUT clearing the
    // hidden inputs. So we must read EXACTLY `count` rows (not all of them), otherwise
    // soft-deleted bullets get resurrected on the EN side.
    function arrayCount(base, i, sf) {
        var max = sf.max || 8;
        var first = fieldEl(name3(base, i, sf.name, 0));
        var wrap = first && first.closest('[x-data]');
        if (wrap && window.Alpine) {
            var d = Alpine.$data(wrap);
            if (d && typeof d.count !== 'undefined') return Math.max(0, Math.min(max, d.count));
        }
        // Fallback (no Alpine count): highest non-empty index + 1.
        var n = 0;
        for (var b = 0; b < max; b++) { var el = fieldEl(name3(base, i, sf.name, b)); if (el && el.value) n = b + 1; }
        return n;
    }

    function plainCollector(r) {
        var recs = [];     // translatable/verbatim cells
        var groups = [];   // array subfields: {i, sf, count} → clear EN tail + sync count
        return {
            sources: function () {
                var out = []; recs.length = 0; groups.length = 0;
                for (var i = 0; i < r.rows; i++) {
                    r.subfields.forEach(function (sf) {
                        if (sf.array) {
                            var n = arrayCount(r.fromBase, i, sf);
                            groups.push({ i: i, sf: sf, count: n });
                            for (var bi = 0; bi < n; bi++) {
                                var rn = name3(r.fromBase, i, sf.name, bi);
                                if (!fieldEl(rn)) continue;
                                recs.push({ i: i, sf: sf, bi: bi });
                                if (!sf.verbatim) out.push(readField(rn, 'text'));
                            }
                        } else {
                            var rn2 = name3(r.fromBase, i, sf.name, null);
                            if (!fieldEl(rn2)) return;
                            recs.push({ i: i, sf: sf, bi: null });
                            if (!sf.verbatim) out.push(readField(rn2, 'text'));
                        }
                    });
                }
                return out;
            },
            apply: function (tr) {
                var ti = 0;
                recs.forEach(function (rec) {
                    var el = fieldEl(name3(r.toBase, rec.i, rec.sf.name, rec.bi));
                    if (rec.sf.verbatim) {
                        var src = readField(name3(r.fromBase, rec.i, rec.sf.name, rec.bi), 'text');
                        if (el && src) el.value = src;          // copy VI→EN as-is
                    } else {
                        var v = tr[ti++];
                        if (!el) return;
                        if (rec.bi !== null) el.value = v || ''; // array cell: mirror VI exactly (clear if empty)
                        else if (v) el.value = v;                // scalar: fill only (don't clobber)
                    }
                });
                // Mirror VI bullet COUNT onto EN: clear the tail and sync the Alpine `count`,
                // so EN shows exactly the VI rows — no resurrected hidden bullets.
                groups.forEach(function (g) {
                    var max = g.sf.max || 8;
                    for (var b = g.count; b < max; b++) {
                        var el = fieldEl(name3(r.toBase, g.i, g.sf.name, b));
                        if (el) el.value = '';
                    }
                    var first = fieldEl(name3(r.toBase, g.i, g.sf.name, 0));
                    var wrap = first && first.closest('[x-data]');
                    if (wrap && window.Alpine) {
                        var d = Alpine.$data(wrap);
                        if (d && typeof d.count !== 'undefined') d.count = g.count;
                    }
                });
            },
        };
    }

    function alpineData(sel) {
        var el = sel && scope().querySelector(sel);
        return (el && window.Alpine) ? Alpine.$data(el) : null;
    }

    function alpineCollector(r) {
        var viData = alpineData(r.viRootSel);
        var enData = alpineData(r.enRootSel);
        var recs = [];
        return {
            sources: function () {
                var out = []; recs.length = 0;
                if (!viData || !Array.isArray(viData.items)) return out;
                viData.items.forEach(function (item, i) {
                    r.subfields.forEach(function (sf) {
                        var val = getPath(item, sf.path);
                        if (sf.array) {
                            (Array.isArray(val) ? val : []).forEach(function (bv, bi) {
                                recs.push({ i: i, sf: sf, bi: bi }); out.push(bv || '');
                            });
                        } else {
                            recs.push({ i: i, sf: sf, bi: null }); out.push(val || '');
                        }
                    });
                });
                return out;
            },
            apply: function (tr) {
                if (!enData || !viData) return;
                // EN mirrors VI structurally (carries media ids, urls, emails, phones,
                // sort orders, and every other untranslated key), then translatable
                // subfields are overwritten with their translations.
                enData.items = JSON.parse(JSON.stringify(viData.items || []));
                recs.forEach(function (rec, idx) {
                    var v = tr[idx]; if (!v) return;
                    var item = enData.items[rec.i]; if (!item) return;
                    if (rec.bi !== null) {
                        var arr = getPath(item, rec.sf.path);
                        if (!Array.isArray(arr)) { arr = []; setPath(item, rec.sf.path, arr); }
                        arr[rec.bi] = v;
                    } else {
                        setPath(item, rec.sf.path, v);
                    }
                });
            },
        };
    }

    function buildCollectors(spec) {
        var cs = [];
        if (spec.scalars && spec.scalars.length) cs.push(scalarCollector(spec.scalars));
        (spec.repeaters || []).forEach(function (r) {
            cs.push(r.shape === 'plain' ? plainCollector(r) : alpineCollector(r));
        });
        return cs;
    }

    async function translateBatch(sources, spec, csrf) {
        var chunk = spec.chunk || 50;
        var endpoint = spec.endpoint || '/admin/translate';
        var result = new Array(sources.length).fill('');
        for (var off = 0; off < sources.length; off += chunk) {
            var slice = sources.slice(off, off + chunk);
            if (slice.every(function (s) { return !s; })) continue;
            var resp = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ fields: slice }),
            });
            if (!resp.ok) throw new Error('translate failed');
            var data = await resp.json();
            var translated = data.translated || [];
            for (var k = 0; k < slice.length; k++) result[off + k] = translated[k] || '';
        }
        return result;
    }

    // ctx = the Alpine component scope ($data) holding vmtaTranslating / vmtaError.
    // btnEl = the button element, used to scope DOM lookups to its enclosing <form>.
    window.vmtaTranslate = async function (spec, ctx, btnEl) {
        ROOT = (btnEl && btnEl.closest) ? btnEl.closest('form') : null;
        ctx.vmtaTranslating = true;
        ctx.vmtaError = '';
        try {
            var csrfEl = scope().querySelector('[name=_token]') || document.querySelector('[name=_token]');
            var csrf = csrfEl ? csrfEl.value : '';

            var collectors = buildCollectors(spec || {});
            var all = [];
            var spans = [];
            collectors.forEach(function (c) {
                var s = c.sources() || [];
                spans.push([all.length, s.length]);
                for (var i = 0; i < s.length; i++) all.push(s[i]);
            });

            var translated = await translateBatch(all, spec || {}, csrf);

            collectors.forEach(function (c, idx) {
                var off = spans[idx][0], len = spans[idx][1];
                c.apply(translated.slice(off, off + len));
            });
        } catch (e) {
            ctx.vmtaError = 'Dịch thất bại. Thử lại sau.';
        } finally {
            ctx.vmtaTranslating = false;
            ROOT = null;
        }
    };
})();
</script>
@endonce
