{{--
    Bộ chọn nhiều ảnh cho gallery chứng nhận.

    Dùng lại popup Media Manager của Core qua postMessage `media_selected`, nhưng không đăng ký
    vào window._activeMediaPicker để handler đơn ảnh của Core bỏ qua (nó return sớm khi không có
    picker nào đang hoạt động).

    Ghi ra một hidden input dạng "12,34,56" đúng định dạng NormalisesGalleryInput mong đợi.

    Biến: $name, $items (array<int, array{id:int,url:string}>), $label
--}}
@php
    $items = $items ?? [];
    $pickerId = 'gallery-'.\Illuminate\Support\Str::random(6);
@endphp

<div x-data="dentalGalleryPicker(@js($items), @js(route('admin.media.index').'?popup=1&for=media-picker'), @js($pickerId))"
     x-id="['gallery']">
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>

    <input type="hidden" name="{{ $name }}" :value="items.map(i => i.id).join(',')">

    <template x-if="items.length">
        <ul class="mb-3 flex list-none flex-wrap gap-3 p-0">
            <template x-for="(item, index) in items" :key="item.id">
                <li class="relative">
                    <img :src="item.url" alt=""
                         class="h-24 w-20 rounded-lg border border-gray-200 object-cover">

                    <button type="button" @click="remove(index)"
                            class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow"
                            :aria-label="'Bỏ ảnh ' + (index + 1)">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <div class="mt-1 flex justify-center gap-1">
                        <button type="button" @click="move(index, -1)" :disabled="index === 0"
                                class="rounded bg-gray-100 px-2 text-xs text-gray-600 disabled:opacity-40">←</button>
                        <button type="button" @click="move(index, 1)" :disabled="index === items.length - 1"
                                class="rounded bg-gray-100 px-2 text-xs text-gray-600 disabled:opacity-40">→</button>
                    </div>
                </li>
            </template>
        </ul>
    </template>

    <button type="button" @click="add()"
            class="rounded bg-blue-600 px-3 py-1.5 text-xs text-white hover:bg-blue-700">
        {{ __('dental::dental.actions.add_image') }}
    </button>
    <span class="ml-2 text-xs text-gray-500" x-text="items.length + ' ảnh'"></span>
</div>

@once
    @push('scripts')
        <script>
            window.dentalGalleryPicker = function (initial, mediaUrl, pickerId) {
                return {
                    items: initial || [],
                    add() {
                        // Nhận đúng một lần chọn rồi tự gỡ listener, tránh cộng dồn qua các lần mở
                        const handler = (e) => {
                            if (!e.data || e.data.type !== 'media_selected') return;
                            window.removeEventListener('message', handler);
                            const id = parseInt(e.data.id, 10);
                            if (!id || this.items.some(i => i.id === id)) return;
                            this.items.push({ id: id, url: e.data.url });
                        };
                        window.addEventListener('message', handler);

                        window.open(
                            mediaUrl,
                            'dental_gallery_' + Math.random().toString(36).slice(2),
                            'width=1100,height=720,resizable=yes,scrollbars=yes'
                        );
                    },
                    remove(index) {
                        this.items.splice(index, 1);
                    },
                    move(index, delta) {
                        const target = index + delta;
                        if (target < 0 || target >= this.items.length) return;
                        [this.items[index], this.items[target]] = [this.items[target], this.items[index]];
                    },
                };
            };
        </script>
    @endpush
@endonce
