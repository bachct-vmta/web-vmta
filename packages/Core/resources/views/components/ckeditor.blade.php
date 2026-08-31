{{--
    CKEditor 5 Blade Component
    
    Usage:
        <x-core::ckeditor name="content" :value="$post->content ?? ''" label="Nội dung" rows="20" />
--}}

@props([
    'name',
    'value' => '',
    'label' => null,
    'rows' => 15,
    'required' => false,
    'placeholder' => '',
])

@php
    $editorId = 'editor-' . \Illuminate\Support\Str::random(8);
    $uploadUrl = route('admin.media.ckeditor.upload');
    $mediaUrl = route('admin.media.index') . '?popup=1&for=ckeditor';
@endphp

<div class="ck-editor-wrapper">
    @if($label)
        <label for="{{ $editorId }}" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $editorId }}"
        name="{{ $name }}"
        class="editor-ckeditor"
        rows="{{ $rows }}"
        data-upload-url="{{ $uploadUrl }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
    >{{ old($name, $value) }}</textarea>

    <div class="flex gap-2 flex-wrap">
        {{-- Media Gallery Button --}}
        <button type="button"
                class="editor-media-btn js-editor-media-btn"
                data-editor-id="{{ $editorId }}"
                title="Mở Media Manager">
            <span class="material-symbols-rounded text-[16px]">perm_media</span>
            Media Gallery
        </button>

        {{-- Insert HLS video — works regardless of CKEditor plugin availability.
             Inserts <video data-hls-src> which the public HLS bootstrap picks up. --}}
        <button type="button"
                class="editor-media-btn js-editor-hls-btn"
                data-editor-id="{{ $editorId }}"
                title="Chèn video HLS (.m3u8)">
            <span class="material-symbols-rounded text-[16px]">smart_display</span>
            Chèn video HLS
        </button>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Load editor assets once per page --}}
@pushOnce('styles')
    <link rel="stylesheet" href="{{ asset('assets/core/editor/editor.css') }}">
@endPushOnce

@pushOnce('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/38.1.1/super-build/ckeditor.js"></script>
    <script src="{{ asset('assets/core/editor/ckeditor-upload-adapter.js') }}?v={{ filemtime(public_path('assets/core/editor/ckeditor-upload-adapter.js')) }}"></script>
    <script src="{{ asset('assets/core/editor/editor-init.js') }}?v={{ filemtime(public_path('assets/core/editor/editor-init.js')) }}"
            data-editor-upload-url="{{ $uploadUrl }}"
            data-editor-media-url="{{ $mediaUrl }}"></script>
@endPushOnce
