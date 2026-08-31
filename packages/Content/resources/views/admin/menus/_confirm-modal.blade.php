<div x-data="{
        open: false,
        title: '',
        body: '',
        targetId: null,
        async confirm() {
            if (!this.targetId) { return; }
            const id = this.targetId;
            this.open = false;
            try {
                await window.menuItemDelete(id);
                const li = document.querySelector('.menu-builder-tree li[data-id=&quot;' + id + '&quot;]');
                if (li) {
                    li.style.transition = 'opacity 200ms ease, transform 200ms ease';
                    li.style.opacity = '0';
                    li.style.transform = 'scale(0.95)';
                    setTimeout(() => { li.remove(); }, 220);
                }
                window.menuBuilderToast && window.menuBuilderToast(@js(__('content::content.menu_item.deleted')), true);
            } catch (e) {
                window.menuBuilderToast && window.menuBuilderToast(e.message || @js(__('content::content.menu.error_generic')), false);
            }
        }
     }"
     x-on:menu-builder-confirm-delete.window="
        targetId = $event.detail.id;
        title = @js(__('content::content.menu.delete_confirm_title'));
        body = @js(__('content::content.menu.delete_confirm_body')).replace(':label', $event.detail.label || '');
        open = true;
     ">
    <div x-show="open" x-cloak
         x-transition.opacity
         class="fixed inset-0 z-40 bg-black/40 flex items-center justify-center p-4"
         x-on:click.self="open = false">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 max-w-sm w-full shadow-soft-xl"
             x-on:keydown.escape.window="open = false">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-base font-medium text-text-main dark:text-white" x-text="title"></h3>
            </div>
            <div class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300" x-text="body"></div>
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2 bg-slate-50 dark:bg-slate-800/60 rounded-b-xl">
                <button type="button" x-on:click="open = false"
                        class="px-3 py-1.5 text-sm rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                    {{ __('content::content.actions.cancel') }}
                </button>
                <button type="button" x-on:click="confirm()"
                        class="px-3 py-1.5 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">
                    {{ __('content::content.actions.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
