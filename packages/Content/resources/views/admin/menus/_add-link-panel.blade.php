@php
    /** @var \Packages\Content\Src\Models\Menu $menu */
    $supportedLocales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
@endphp
<div class="space-y-3 sticky top-4"
     x-data="addLinkPanel({
        menuId: {{ (int) $menu->id }},
        sourcesUrl: @js(route(admin_route_name('menus.sources'))),
        quickAddUrl: @js(route(admin_route_name('menus.items.quick-add'), $menu->id)),
        locales: @js($supportedLocales),
        firstLocale: @js($supportedLocales[0] ?? 'vi'),
     })">

    {{-- Accordion 1: Pages picker --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
        <button type="button" x-on:click="setTab(tab === 'page' ? null : 'page')"
                class="w-full px-4 py-3 flex items-center justify-between text-left">
            <span class="text-sm font-medium text-text-main dark:text-white">{{ __('content::content.picker.tab_page') }}</span>
            <span class="material-symbols-rounded text-[18px] text-slate-400 transition-transform"
                  :class="tab === 'page' ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="tab === 'page'" x-collapse class="px-3 pb-3 space-y-3">
            <input type="search"
                   x-model="q"
                   x-on:input.debounce.300ms="resetAndSearch()"
                   :placeholder="@js(__('content::content.picker.search_pages'))"
                   class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
            <ul class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700 border border-slate-100 dark:border-slate-700 rounded-md">
                <template x-for="row in results" :key="row.id">
                    <li class="flex items-center justify-between px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-text-main dark:text-white truncate" x-text="row.title"></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate" x-text="row.subtitle"></p>
                        </div>
                        <button type="button" x-on:click="addFromPicker(row)"
                                class="ml-2 shrink-0 px-2 py-1 text-xs rounded bg-primary text-white hover:bg-primary-700">+</button>
                    </li>
                </template>
                <li x-show="!loading && results.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('content::content.picker.no_results') }}</li>
            </ul>
            <button type="button" x-show="meta.current_page < meta.last_page" x-on:click="loadMore()"
                    class="w-full text-xs py-1.5 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                {{ __('content::content.picker.load_more') }}
            </button>
        </div>
    </div>

    {{-- Accordion 2: Posts picker --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
        <button type="button" x-on:click="setTab(tab === 'post' ? null : 'post')"
                class="w-full px-4 py-3 flex items-center justify-between text-left">
            <span class="text-sm font-medium text-text-main dark:text-white">{{ __('content::content.picker.tab_post') }}</span>
            <span class="material-symbols-rounded text-[18px] text-slate-400 transition-transform"
                  :class="tab === 'post' ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="tab === 'post'" x-collapse class="px-3 pb-3 space-y-3">
            <input type="search"
                   x-model="q"
                   x-on:input.debounce.300ms="resetAndSearch()"
                   :placeholder="@js(__('content::content.picker.search_posts'))"
                   class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
            <ul class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700 border border-slate-100 dark:border-slate-700 rounded-md">
                <template x-for="row in results" :key="row.id">
                    <li class="flex items-center justify-between px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-text-main dark:text-white truncate" x-text="row.title"></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate" x-text="row.subtitle"></p>
                        </div>
                        <button type="button" x-on:click="addFromPicker(row)"
                                class="ml-2 shrink-0 px-2 py-1 text-xs rounded bg-primary text-white hover:bg-primary-700">+</button>
                    </li>
                </template>
                <li x-show="!loading && results.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('content::content.picker.no_results') }}</li>
            </ul>
            <button type="button" x-show="meta.current_page < meta.last_page" x-on:click="loadMore()"
                    class="w-full text-xs py-1.5 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                {{ __('content::content.picker.load_more') }}
            </button>
        </div>
    </div>

    {{-- Accordion 3: Custom URL --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
        <button type="button" x-on:click="setTab(tab === 'url' ? null : 'url')"
                class="w-full px-4 py-3 flex items-center justify-between text-left">
            <span class="text-sm font-medium text-text-main dark:text-white">{{ __('content::content.picker.tab_url') }}</span>
            <span class="material-symbols-rounded text-[18px] text-slate-400 transition-transform"
                  :class="tab === 'url' ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="tab === 'url'" x-collapse class="px-3 pb-3 space-y-3">
            @foreach($supportedLocales as $loc)
                <div>
                    <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.picker.custom_label') }} ({{ $loc }})</label>
                    <input type="text" maxlength="255" x-model="custom.labels[@js($loc)]"
                           class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                </div>
            @endforeach
            <div>
                <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.picker.custom_url') }}</label>
                <input type="text" maxlength="500" x-model="custom.url" placeholder="https://"
                       class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">{{ __('content::content.menu_item.target') }}</label>
                <select x-model="custom.target"
                        class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                    <option value="_self">{{ __('content::content.menu_item.target_self') }}</option>
                    <option value="_blank">{{ __('content::content.menu_item.target_blank') }}</option>
                </select>
            </div>
            <button type="button" x-on:click="addCustomUrl()"
                    class="w-full px-3 py-2 text-sm rounded-md bg-primary text-white hover:bg-primary-700">+ {{ __('content::content.picker.add') }}</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('addLinkPanel', (config) => ({
            menuId: config.menuId,
            sourcesUrl: config.sourcesUrl,
            quickAddUrl: config.quickAddUrl,
            locales: config.locales,
            firstLocale: config.firstLocale,
            tab: 'page',
            q: '',
            results: [],
            meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
            loading: false,
            custom: {
                labels: Object.fromEntries(config.locales.map(l => [l, ''])),
                url: '',
                target: '_self',
            },
            init() {
                this.search();
            },
            setTab(t) {
                this.tab = t;
                if (t === 'page' || t === 'post') {
                    this.resetAndSearch();
                }
            },
            async resetAndSearch() {
                this.results = [];
                this.meta = { current_page: 1, last_page: 1, per_page: 15, total: 0 };
                await this.search(1);
            },
            async loadMore() {
                await this.search(this.meta.current_page + 1, true);
            },
            async search(page = 1, append = false) {
                if (this.tab !== 'page' && this.tab !== 'post') { return; }
                this.loading = true;
                try {
                    const url = new URL(this.sourcesUrl, window.location.origin);
                    url.searchParams.set('type', this.tab);
                    url.searchParams.set('q', this.q || '');
                    url.searchParams.set('page', String(page));
                    const res = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) { throw new Error('HTTP ' + res.status); }
                    const json = await res.json();
                    this.results = append ? [...this.results, ...json.data] : json.data;
                    this.meta = json.meta;
                } catch (e) {
                    window.menuBuilderToast && window.menuBuilderToast(e.message, false);
                } finally {
                    this.loading = false;
                }
            },
            buildTranslations(label, url) {
                const out = [];
                const trimmedLabel = String(label || '').trim();
                for (const loc of this.locales) {
                    out.push({ locale: loc, label: trimmedLabel || ('#' + loc), url: url || null });
                }
                return out;
            },
            async addFromPicker(row) {
                const payload = {
                    link_type: 'morph',
                    target_type: row.type,
                    target_id: row.id,
                    translations: this.buildTranslations(row.title, null),
                };
                await this.submit(payload);
            },
            async addCustomUrl() {
                const trimmed = String(this.custom.url || '').trim();
                if (!trimmed) {
                    window.menuBuilderToast && window.menuBuilderToast(@js(__('content::content.menu_item.url_scheme_invalid')), false);
                    return;
                }
                const translations = this.locales.map(loc => ({
                    locale: loc,
                    label: (this.custom.labels[loc] || '').trim() || trimmed,
                    url: trimmed,
                }));
                const payload = {
                    link_type: 'url',
                    open_new_tab: this.custom.target === '_blank',
                    translations,
                };
                const ok = await this.submit(payload);
                if (ok) {
                    this.custom.url = '';
                    this.custom.target = '_self';
                    for (const loc of this.locales) { this.custom.labels[loc] = ''; }
                }
            },
            async submit(payload) {
                try {
                    const res = await fetch(this.quickAddUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    let json = null;
                    try { json = await res.json(); } catch (_) {}
                    if (!res.ok) {
                        const msg = json?.errors ? Object.values(json.errors)[0] : (json?.message || ('HTTP ' + res.status));
                        throw new Error(Array.isArray(msg) ? msg[0] : msg);
                    }
                    if (window.menuTreeAppend && json?.item) {
                        window.menuTreeAppend(json.item);
                    }
                    window.menuBuilderToast && window.menuBuilderToast(@js(__('content::content.menu_item.created')), true);
                    return true;
                } catch (e) {
                    window.menuBuilderToast && window.menuBuilderToast(e.message, false);
                    return false;
                }
            },
        }));
    });
</script>
@endpush
