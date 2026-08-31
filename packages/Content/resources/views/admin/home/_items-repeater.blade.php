@php
    /**
     * Items repeater — supports fixed OR variable cardinality.
     * Required vars:
     *   - $section: ?HomeSection
     *   - $locales: array<string> e.g. ['vi','en']
     *   - $count: int — default count / fixed cardinality
     *   - $fields: array<string, string> field=>label keys
     * Optional vars:
     *   - $variable: bool — if true, render Alpine add/remove buttons (default false)
     *   - $min: int — minimum items when variable (default $count)
     *   - $max: int — maximum items when variable (default $count)
     */
    $locales = $locales ?? ['vi', 'en'];
    $variable = $variable ?? false;
    $min = $min ?? $count;
    $max = $max ?? $count;
    // Optional button labels (default to generic "thẻ" wording).
    $addLabel = $addLabel ?? '+ Thêm thẻ';
    $removeLabel = $removeLabel ?? 'Xoá thẻ';
@endphp
<div class="space-y-4">
    @foreach($locales as $locale)
        @php
            $tr = $section?->translations?->firstWhere('locale', $locale);
            $existing = $tr?->items ?? [];
            $initCount = max($min, count($existing) ?: $count);
        @endphp
        <details class="rounded border border-slate-200 p-3" open>
            <summary class="font-medium text-sm text-slate-700 cursor-pointer">
                {{ __('content::content.tabs.translation', ['locale' => strtoupper($locale)]) }}
            </summary>
            @if($variable)
                @php
                    // Seed rows from prior input (validation redo) or stored items; always keep >= 1 row.
                    $seed = old("translations.{$locale}.items", $existing);
                    $seed = is_array($seed) ? array_values($seed) : [];
                    if (count($seed) === 0) {
                        $seed = [array_fill_keys(array_keys($fields), '')];
                    }
                @endphp
                <div class="mt-3 space-y-3"
                     x-data="{
                        items: {{ \Illuminate\Support\Js::from($seed) }},
                        fieldKeys: {{ \Illuminate\Support\Js::from(array_keys($fields)) }},
                        min: {{ (int) $min }},
                        blankItem() { const o = {}; this.fieldKeys.forEach(f => o[f] = ''); return o; },
                        addItem() { this.items.push(this.blankItem()); },
                        removeItem(i) { if (this.items.length > this.min) this.items.splice(i, 1); }
                     }">
                    <template x-for="(item, idx) in items" :key="idx">
                        <fieldset class="rounded bg-slate-50 p-3 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs uppercase text-slate-500 px-1">#<span x-text="idx + 1"></span></span>
                                <button type="button"
                                        @click="removeItem(idx)"
                                        x-show="items.length > min"
                                        class="rounded bg-rose-500 px-2 py-1 text-white text-xs hover:bg-rose-600">
                                    {{ $removeLabel }}
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($fields), 3) }} gap-3">
                                @foreach($fields as $field => $label)
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                                        <input type="text"
                                               :name="`translations[{{ $locale }}][items][${idx}][{{ $field }}]`"
                                               x-model="item['{{ $field }}']"
                                               class="w-full rounded border-slate-300 text-sm">
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </template>
                    <div class="pt-1">
                        <button type="button"
                                @click="addItem()"
                                class="rounded bg-teal-600 px-3 py-1 text-white text-xs hover:bg-teal-700">
                            {{ $addLabel }}
                        </button>
                    </div>
                </div>
            @else
            <div class="mt-3 space-y-3">
                    @for($i = 0; $i < $count; $i++)
                        <fieldset class="rounded bg-slate-50 p-3 border border-slate-200">
                            <legend class="text-xs uppercase text-slate-500 px-1">#{{ $i + 1 }}</legend>
                            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($fields), 3) }} gap-3">
                                @foreach($fields as $field => $label)
                                    @php $val = old("translations.{$locale}.items.{$i}.{$field}", $existing[$i][$field] ?? ''); @endphp
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                                        <input type="text"
                                               name="translations[{{ $locale }}][items][{{ $i }}][{{ $field }}]"
                                               value="{{ $val }}"
                                               class="w-full rounded border-slate-300 text-sm">
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endfor
            </div>
            @endif
        </details>
    @endforeach
</div>
