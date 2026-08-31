{{-- Nestable + AJAX init for the menu builder. Pushed to layout stacks; loads only on /admin/menus/{id}/edit. --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/nestable/jquery.nestable.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/nestable/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/nestable/jquery.nestable.min.js') }}"></script>
    <script>
    (function () {
        // Keep Alpine.js safe — release $ and jQuery from window.
        const $jq = window.jQuery.noConflict(true);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const $dd = $jq('.menu-builder-tree');
        if ($dd.length === 0) { return; }

        const menuId = $dd.data('menu-id');
        const reorderUrl = $dd.data('reorder-url');
        const patchUrlTpl = $dd.data('patch-url-template');
        const deleteUrlTpl = $dd.data('delete-url-template');

        $dd.nestable({ maxDepth: 3, group: menuId });

        // Toast helper.
        window.menuBuilderToast = function (message, success) {
            window.dispatchEvent(new CustomEvent('menu-builder-toast', {
                detail: { message: String(message ?? ''), success: !!success }
            }));
        };

        // Inflight counter (saving indicator).
        window.menuBuilderInflight = window.menuBuilderInflight ?? { count: 0 };
        function setInflight(delta) {
            window.menuBuilderInflight.count = Math.max(0, window.menuBuilderInflight.count + delta);
            window.dispatchEvent(new CustomEvent('menu-builder-inflight', {
                detail: { count: window.menuBuilderInflight.count }
            }));
        }

        // Unified fetch wrapper.
        async function apiFetch(url, options) {
            setInflight(1);
            try {
                const res = await fetch(url, {
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options?.headers ?? {})
                    }
                });
                let payload = null;
                try { payload = await res.json(); } catch (_) {}
                if (!res.ok) {
                    const msg = payload?.message || payload?.errors
                        ? (typeof payload.errors === 'object' ? Object.values(payload.errors)[0] : payload.message)
                        : ('HTTP ' + res.status);
                    throw new Error(Array.isArray(msg) ? msg[0] : msg);
                }
                return payload;
            } finally {
                setInflight(-1);
            }
        }

        // Reorder save: 200ms debounce on nestable change.
        let saveTimer = null;
        $dd.on('change', function () {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveTree, 200);
        });

        function saveTree() {
            const tree = $dd.nestable('serialize');
            apiFetch(reorderUrl, {
                method: 'POST',
                body: JSON.stringify({ tree: tree })
            })
                .then(() => window.menuBuilderToast(@json(__('content::content.menu.reorder.saved')), true))
                .catch((e) => window.menuBuilderToast(e.message || @json(__('content::content.menu.reorder.error')), false));
        }

        // PATCH item helper — used by inline edit row in Phase 3.
        window.menuItemPatch = function (id, payload) {
            const url = patchUrlTpl.replace('__ID__', String(id));
            return apiFetch(url, { method: 'PATCH', body: JSON.stringify(payload) });
        };

        // DELETE item helper — used by delete flow.
        window.menuItemDelete = function (id) {
            const url = deleteUrlTpl.replace('__ID__', String(id));
            return apiFetch(url, { method: 'DELETE' });
        };

        // Append new item to root (Phase 4 quick-add).
        window.menuTreeAppend = function (item) {
            const $list = $dd.children('ol.dd-list');
            const $li = $jq(buildItemMarkup(item));
            if ($list.length) {
                $list.prepend($li);
            } else {
                $dd.html('<ol class="dd-list">' + $li.prop('outerHTML') + '</ol>');
            }
            // Re-init nestable so the new row picks up handlers.
            $dd.nestable('destroy');
            $dd.nestable({ maxDepth: 3, group: menuId });
        };

        function buildItemMarkup(item) {
            const safeLabel = $jq('<div/>').text(item.displayLabel ?? item.label ?? ('#' + item.id)).html();
            const iconHtml = item.icon
                ? '<span class="material-symbols-rounded text-[18px] text-slate-400">' + $jq('<div/>').text(item.icon).html() + '</span>'
                : '';
            return ''
                + '<li class="dd-item dd3-item" data-id="' + item.id + '">'
                +   '<div class="dd-handle dd3-handle">' + @json(__('content::content.menu.drag_handle')) + '</div>'
                +   '<div class="dd3-content flex items-center justify-between gap-3">'
                +     '<div class="flex items-center gap-3 min-w-0 flex-1">'
                +       iconHtml
                +       '<span class="font-medium truncate" data-role="row-label">' + safeLabel + '</span>'
                +     '</div>'
                +     '<div class="flex items-center gap-1 shrink-0">'
                +       '<button type="button" data-action="edit" data-item-id="' + item.id + '" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300"><span class="material-symbols-rounded text-[18px]">edit</span></button>'
                +       '<button type="button" data-action="delete" data-item-id="' + item.id + '" data-label="' + safeLabel + '" class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600"><span class="material-symbols-rounded text-[18px]">delete</span></button>'
                +     '</div>'
                +   '</div>'
                + '</li>';
        }

        // Delegate delete button clicks (Phase 5 wires the confirm modal).
        $dd.on('click', 'button[data-action="delete"]', function () {
            const btn = this;
            const id = btn.getAttribute('data-item-id');
            const label = btn.getAttribute('data-label') ?? '';
            window.dispatchEvent(new CustomEvent('menu-builder-confirm-delete', {
                detail: { id: id, label: label }
            }));
        });

        // Delegate edit button clicks (Phase 3 wires Alpine expand state).
        $dd.on('click', 'button[data-action="edit"]', function () {
            const id = this.getAttribute('data-item-id');
            window.dispatchEvent(new CustomEvent('menu-builder-edit', { detail: { id: id } }));
        });
    })();
    </script>
@endpush
