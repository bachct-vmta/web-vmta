@php
    /** @var \Packages\Content\Src\Models\MenuItem $item */
    $locale = app()->getLocale();
    $label = $item->translations->firstWhere('locale', $locale)?->label
        ?? $item->translations->first()?->label
        ?? '#'.$item->id;
    $url = $item->translations->firstWhere('locale', $locale)?->url
        ?? $item->translations->first()?->url;
    $linkPreview = $item->link_type === 'url'
        ? ($url ?? '—')
        : ($item->target_type . '#' . $item->target_id);
    $supportedLocales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
    $translationsByLocale = [];
    foreach ($supportedLocales as $loc) {
        $row = $item->translations->firstWhere('locale', $loc);
        $translationsByLocale[$loc] = [
            'label' => $row?->label ?? '',
            'url' => $row?->url ?? '',
        ];
    }
@endphp
<li class="dd-item dd3-item" data-id="{{ $item->id }}"
    x-data="{
        open: false,
        tab: @js($supportedLocales[0] ?? 'vi'),
        saving: false,
        itemId: {{ (int) $item->id }},
        scalars: {
            icon: @js($item->icon ?? ''),
            css_class: @js($item->css_class ?? ''),
            target: @js($item->open_new_tab ? '_blank' : '_self'),
            is_active: {{ $item->is_active ? 'true' : 'false' }},
            link_type: @js($item->link_type),
            target_type: @js($item->target_type ?? ''),
            target_id: {{ $item->target_id ? (int) $item->target_id : 'null' }},
        },
        translations: @js($translationsByLocale),
        errors: {},
        toggle() { this.open = !this.open; },
        async patch(payload) {
            this.saving = true;
            this.errors = {};
            try {
                const res = await window.menuItemPatch(this.itemId, payload);
                if (res?.displayLabel) {
                    const lbl = this.$el.querySelector('[data-role=row-label]');
                    if (lbl) { lbl.textContent = res.displayLabel; }
                }
            } catch (e) {
                window.menuBuilderToast(e.message || @js(__('content::content.menu.error_generic')), false);
            } finally {
                this.saving = false;
            }
        },
        saveTranslation(loc) {
            this.patch({ translations: [{ locale: loc, label: this.translations[loc].label, url: this.translations[loc].url }] });
        },
        saveScalars() {
            this.patch({
                link_type: this.scalars.link_type,
                target_type: this.scalars.target_type || null,
                target_id: this.scalars.target_id || null,
                icon: this.scalars.icon || null,
                css_class: this.scalars.css_class || null,
                open_new_tab: this.scalars.target === '_blank',
                is_active: this.scalars.is_active,
            });
        }
    }"
    x-on:menu-builder-edit.window="if ($event.detail.id == itemId) { open = !open; }">
    <div class="dd-handle dd3-handle"></div>
    <div class="dd3-content flex items-center justify-between gap-3 pl-[64px]">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            @if($item->icon)
                <span class="material-symbols-rounded text-[18px] text-slate-400">{{ $item->icon }}</span>
            @endif
            <span class="font-medium truncate" data-role="row-label">{{ $label }}</span>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate hidden md:inline">{{ $linkPreview }}</span>
            @unless($item->is_active)
                <span class="text-[10px] uppercase rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 px-1.5 py-0.5">inactive</span>
            @endunless
            <span x-show="saving" x-cloak class="text-xs text-primary">⟳</span>
        </div>
        <div class="flex items-center gap-1 shrink-0">
            @if(auth()->user()?->hasPermission('content.edit'))
                <button type="button"
                        data-action="edit"
                        data-item-id="{{ $item->id }}"
                        class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300"
                        title="{{ __('content::content.actions.edit') }}">
                    <span class="material-symbols-rounded text-[18px]">edit</span>
                </button>
            @endif
            @if(auth()->user()?->hasPermission('content.delete'))
                <button type="button"
                        data-action="delete"
                        data-item-id="{{ $item->id }}"
                        data-label="{{ $label }}"
                        class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600"
                        title="{{ __('content::content.actions.delete') }}">
                    <span class="material-symbols-rounded text-[18px]">delete</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Inline edit panel (Alpine x-collapse). --}}
    <div x-show="open" x-collapse x-cloak class="px-4 pt-1 pb-3 ml-11 -mt-1 rounded-b-md border-x border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
        {{-- Tab bar (locales) --}}
        <div class="flex items-center gap-1 mb-3 border-b border-slate-200 dark:border-slate-700">
            @foreach($supportedLocales as $loc)
                <button type="button"
                        x-on:click="tab = @js($loc)"
                        :class="tab === @js($loc) ? 'border-primary text-primary font-medium' : 'border-transparent text-slate-500'"
                        class="px-3 py-1.5 text-xs uppercase border-b-2 -mb-px">{{ $loc }}</button>
            @endforeach
        </div>

        {{-- Per-locale translation fields --}}
        @foreach($supportedLocales as $loc)
            <div x-show="tab === @js($loc)" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.label') }} ({{ $loc }})</label>
                    <input type="text" maxlength="255"
                           x-model="translations[@js($loc)].label"
                           x-on:change.debounce.400ms="saveTranslation(@js($loc))"
                           class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.url') }} ({{ $loc }})</label>
                    <input type="text" maxlength="500"
                           x-model="translations[@js($loc)].url"
                           x-on:change.debounce.400ms="saveTranslation(@js($loc))"
                           placeholder="https://"
                           class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 font-mono">
                </div>
            </div>
        @endforeach

        {{-- Scalar fields --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
            <div>
                <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.icon') }}</label>
                <input type="text" maxlength="60" x-model="scalars.icon" x-on:change.debounce.400ms="saveScalars"
                       class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.css_class') }}</label>
                <input type="text" maxlength="255" x-model="scalars.css_class" x-on:change.debounce.400ms="saveScalars"
                       class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.target') }}</label>
                <select x-model="scalars.target" x-on:change="saveScalars"
                        class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                    <option value="_self">{{ __('content::content.menu_item.target_self') }}</option>
                    <option value="_blank">{{ __('content::content.menu_item.target_blank') }}</option>
                </select>
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 text-xs">
                    <input type="checkbox" x-model="scalars.is_active" x-on:change="saveScalars">
                    {{ __('content::content.fields.is_active') }}
                </label>
            </div>
        </div>
    </div>

    @if($item->children->isNotEmpty())
        <ol class="dd-list">
            @foreach($item->children as $child)
                @include('content::admin.menus._dd-item', ['item' => $child])
            @endforeach
        </ol>
    @endif
</li>
