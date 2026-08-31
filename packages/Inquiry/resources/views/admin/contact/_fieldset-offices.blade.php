<form method="POST" action="{{ route(admin_route_name('contact.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-contact-fieldset="offices">
    @csrf @method('PUT')

    <div class="flex justify-end pb-3 mb-3 border-b border-slate-100">
        @include('content::admin.partials._translate-button', ['spec' => [
            'scalars' => [
                ['from' => 'translations[vi][title]', 'to' => 'translations[en][title]', 'kind' => 'text'],
            ],
            'repeaters' => [
                ['shape' => 'alpine-flat',
                 'viRootSel' => '[data-tr-repeater="offices"][data-locale="vi"]',
                 'enRootSel' => '[data-tr-repeater="offices"][data-locale="en"]',
                 'subfields' => [['path' => 'name'], ['path' => 'address'], ['path' => 'note']]],
            ],
        ]])
    </div>


    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('inquiry::inquiry.admin.contact.fields.section_heading'), 'section' => $section])

    {{-- Illustration + map embed: single value (not translated) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-core::media-picker
            name="image_1_media_id"
            :value="$section?->image_1_media_id"
            :preview-url="$section?->image1?->permalink"
            :label="__('inquiry::inquiry.admin.contact.fields.image')"
            store="id"
        />
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ __('inquiry::inquiry.admin.contact.fields.map_embed') }}
            </label>
            <textarea name="map_embed" rows="4" class="w-full rounded border-slate-300 text-sm"
                      placeholder="<iframe src=...></iframe>">{{ old('map_embed', $section?->map_embed) }}</textarea>
            <p class="text-xs text-slate-400 mt-1">{{ __('inquiry::inquiry.admin.contact.fields.map_embed_hint') }}</p>
        </div>
    </div>

    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            {{ __('inquiry::inquiry.admin.contact.fields.offices') }} <span class="text-xs text-slate-400">(tối thiểu 1, thêm/xoá tuỳ ý)</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section'     => $section,
            'repeaterKey' => 'offices',
            'count'       => 3,
            'variable'    => true,
            'min'         => 1,
            'addLabel'    => '+ Thêm văn phòng',
            'removeLabel' => 'Xoá hàng',
            'fields'      => [
                'name'    => __('inquiry::inquiry.admin.contact.fields.office_name'),
                'address' => __('inquiry::inquiry.admin.contact.fields.office_address'),
                'email'   => __('inquiry::inquiry.admin.contact.fields.office_email'),
                'phone'   => __('inquiry::inquiry.admin.contact.fields.office_phone'),
                'note'    => __('inquiry::inquiry.admin.contact.fields.office_note'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('inquiry::inquiry.admin.contact.save') }}</button>
    </div>
</form>
