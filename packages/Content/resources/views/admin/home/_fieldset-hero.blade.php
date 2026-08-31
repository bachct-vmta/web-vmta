<form method="POST" action="{{ route(admin_route_name('home.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-home-fieldset="hero">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', [
        'field' => 'title',
        'label' => __('content::content.home.fields.title'),
        'section' => $section,
    ])
    @include('content::admin.home._locale-input', [
        'field' => 'subtitle',
        'label' => __('content::content.home.fields.subtitle'),
        'section' => $section,
        'type' => 'textarea',
    ])
    @include('content::admin.home._locale-input', [
        'field' => 'body',
        'label' => __('content::content.home.fields.body'),
        'section' => $section,
        'type' => 'textarea',
    ])
    @include('content::admin.home._locale-input', [
        'field' => 'cta_label',
        'label' => __('content::content.home.fields.cta_label'),
        'section' => $section,
    ])
    @include('content::admin.home._locale-input', [
        'field' => 'cta_url',
        'label' => __('content::content.home.fields.cta_url'),
        'section' => $section,
    ])

    <div class="rounded border border-slate-200 bg-slate-50 p-3">
        {{-- Hidden companion guarantees a 0 is submitted when the checkbox is unchecked. --}}
        <input type="hidden" name="marquee_active" value="0">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" name="marquee_active" value="1"
                   class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                   @checked(old('marquee_active', $section?->marquee_active ?? true))>
            <span class="text-sm font-medium text-slate-700">
                {{ __('content::content.home.fields.marquee_active') }}
            </span>
        </label>
        <p class="mt-1 ml-7 text-xs text-slate-400">{{ __('content::content.home.fields.marquee_active_hint') }}</p>
    </div>

    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            Marquee items <span class="text-xs text-slate-400">(min 4 / max 10 — {{ __('content::content.home.fields.item_label') }} + {{ __('content::content.home.fields.item_value') }})</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section'  => $section,
            'count'    => 4,
            'variable' => true,
            'min'      => 4,
            'max'      => 10,
            'fields'   => [
                'label' => __('content::content.home.fields.item_label'),
                'value' => __('content::content.home.fields.item_value'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit"
                class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
            {{ __('content::content.home.save') }}
        </button>
    </div>
</form>
