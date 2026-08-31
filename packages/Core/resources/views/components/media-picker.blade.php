{{--
    Media Picker — opens the Media Manager popup to pick a file.
    Usage:
        <x-core::media-picker
            name="hero_media_id"
            :value="$model->hero_media_id"
            :preview-url="$model->heroMedia?->permalink"
            label="Ảnh hero"
            store="id"
        />
    store="id" (default) writes media_files.id into the hidden input; use
    store="permalink" to write the file path string instead.
--}}

@props([
    'name',
    'value' => null,
    'previewUrl' => null,
    'label' => null,
    'store' => 'id',
    'required' => false,
])

@php
    $componentId = 'media-picker-'.\Illuminate\Support\Str::random(8);
    $mediaUrl = route('admin.media.index').'?popup=1&for=media-picker';
    $currentValue = old($name, $value);
    $resolvedPreview = $previewUrl;
    if ($resolvedPreview && ! preg_match('#^https?://#', $resolvedPreview)) {
        $base = rtrim(config('file-manager.base_path', '/uploads'), '/');
        $resolvedPreview = url($base.'/'.ltrim($resolvedPreview, '/'));
    }
@endphp

<div
    class="media-picker"
    x-data="mediaPicker({
        initialValue: @js($currentValue),
        initialPreview: @js($resolvedPreview),
        store: @js($store),
        mediaUrl: @js($mediaUrl),
    })"
>
    @if($label)
        <label class="block text-sm font-medium mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="flex items-start gap-3">
        <div class="w-24 h-24 flex-shrink-0 rounded border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
            <template x-if="preview">
                <img :src="preview" alt="" class="w-full h-full object-cover">
            </template>
            <template x-if="! preview">
                <span class="text-xs text-slate-400">No image</span>
            </template>
        </div>

        <div class="flex-1 space-y-2">
            <input type="hidden" :name="@js($name)" :value="value">
            <div class="flex gap-2">
                <button type="button" @click="open()"
                        class="rounded bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5">
                    {{ __('Chọn ảnh') }}
                </button>
                <button type="button" x-show="value" @click="clear()"
                        class="rounded bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs px-3 py-1.5">
                    {{ __('Bỏ') }}
                </button>
            </div>
            <p class="text-xs text-slate-500 font-mono break-all" x-text="value ? @js($store === 'id' ? 'ID: ' : '') + value : ''"></p>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
// Track the currently-active picker globally. Only this instance consumes
// the next "media_selected" postMessage — prevents cross-picker bleed when
// the user opens picker A, cancels it, then opens picker B and selects.
window._activeMediaPicker = window._activeMediaPicker || null;

// Single global message handler (registered once), forwards to the active picker.
if (! window._mediaPickerGlobalHandlerInstalled) {
    window.addEventListener('message', function (e) {
        if (!e.data || e.data.type !== 'media_selected') return;
        const picker = window._activeMediaPicker;
        if (!picker) return;
        picker.value = picker.store === 'permalink' ? e.data.url : (e.data.id || '');
        picker.preview = e.data.url;
        window._activeMediaPicker = null; // consumed
    });
    window._mediaPickerGlobalHandlerInstalled = true;
}

window.mediaPicker = function (cfg) {
    return {
        value: cfg.initialValue || '',
        preview: cfg.initialPreview || '',
        store: cfg.store || 'id',
        mediaUrl: cfg.mediaUrl,

        open() {
            // Claim ownership — overwrites any previous claim (cancelled pickers).
            window._activeMediaPicker = this;
            window.open(
                this.mediaUrl,
                'media_picker_' + Math.random().toString(36).slice(2),
                'width=1100,height=720,resizable=yes,scrollbars=yes'
            );
        },

        clear() {
            this.value = '';
            this.preview = '';
        },
    };
};
</script>
@endPushOnce
