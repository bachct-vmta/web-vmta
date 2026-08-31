{{--
    Select có ô tìm kiếm, dựng bằng Alpine để không thêm thư viện ngoài.

    Biến: $name, $options (array<int|string, string>), $selected, $label, $required
--}}
@php
    $options = $options ?? [];
    $selected = $selected ?? null;
    $required = $required ?? false;
    $items = collect($options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->all();
    $selectedLabel = $options[$selected] ?? '';
@endphp

<div x-data="dentalSearchableSelect(@js($items), @js((string) $selected), @js($selectedLabel))"
     @click.outside="close()"
     @keydown.escape.window="close()"
     class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}@if($required) <span class="text-red-500">*</span>@endif
    </label>

    <input type="hidden" name="{{ $name }}" :value="value">

    <div class="relative">
        <input type="text" x-model="search" @focus="open = true" @click="open = true"
               :placeholder="label || @js(__('dental::dental.actions.search_placeholder'))"
               autocomplete="off"
               class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 pr-9 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        <button type="button" @click="open = ! open"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400" tabindex="-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <ul x-show="open" x-cloak
        class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
        <template x-for="item in filtered()" :key="item.value">
            <li>
                <button type="button" @click="pick(item)"
                        :class="item.value === value ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'"
                        class="block w-full px-4 py-2 text-left text-sm"
                        x-text="item.label"></button>
            </li>
        </template>
        <li x-show="! filtered().length" class="px-4 py-2 text-sm text-gray-400">—</li>
    </ul>
</div>

@once
    @push('scripts')
        <script>
            window.dentalSearchableSelect = function (items, initialValue, initialLabel) {
                return {
                    items: items,
                    value: initialValue || '',
                    label: initialLabel || '',
                    search: initialLabel || '',
                    open: false,
                    normalise(v) {
                        return (v || '').normalize('NFD').replace(/[̀-ͯ]/g, '')
                            .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
                    },
                    filtered() {
                        // Vừa chọn xong thì ô hiện đúng nhãn, lúc đó hiện lại toàn bộ danh sách
                        if (this.search === this.label) return this.items;
                        const q = this.normalise(this.search);
                        return this.items.filter(i => this.normalise(i.label).includes(q));
                    },
                    pick(item) {
                        this.value = item.value;
                        this.label = item.label;
                        this.search = item.label;
                        this.open = false;
                    },
                    close() {
                        this.open = false;
                        this.search = this.label;
                    },
                };
            };
        </script>
    @endpush
@endonce
