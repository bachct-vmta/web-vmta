<form method="POST" action="{{ route(admin_route_name('about.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-about-fieldset="who_are">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.about.fields.title'), 'section' => $section])

    {{-- Per-locale illustration image — VI on /vi/, EN on /en/ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-core::media-picker
            name="image_1_media_id"
            :value="$section?->image_1_media_id"
            :preview-url="$section?->image1?->permalink"
            label="Ảnh (VI)"
            store="id"
        />
        <x-core::media-picker
            name="image_2_media_id"
            :value="$section?->image_2_media_id"
            :preview-url="$section?->image2?->permalink"
            label="Ảnh (EN)"
            store="id"
        />
    </div>

    {{-- Body: rich-text editor per locale --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['vi', 'en'] as $locale)
            @php $tr = $section?->translations?->firstWhere('locale', $locale); @endphp
            <x-core::ckeditor
                :name="'translations[' . $locale . '][body]'"
                :value="old('translations.' . $locale . '.body', $tr?->body ?? '')"
                :label="__('content::content.about.fields.body') . ' (' . strtoupper($locale) . ')'"
                rows="10" />
        @endforeach
    </div>

    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            {{ __('content::content.about.fields.mission_cards') }} <span class="text-xs text-slate-400">(tối thiểu 1, thêm/xoá tuỳ ý)</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section'  => $section,
            'count'    => 3,
            'variable' => true,
            'min'      => 1,
            'fields'   => [
                'title' => __('content::content.about.fields.item_title'),
                'body'  => __('content::content.about.fields.item_body'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.about.save') }}</button>
    </div>
</form>
