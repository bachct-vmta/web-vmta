<form method="POST" action="{{ route(admin_route_name('about.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-about-fieldset="why_choose">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.about.fields.title'), 'section' => $section])

    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            {{ __('content::content.about.fields.reason_cards') }}
            <span class="text-xs text-slate-400">({{ __('content::content.about.fields.reason_cards_hint') }})</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section'     => $section,
            'count'       => 4,
            'variable'    => true,
            'min'         => 0,
            'addLabel'    => __('content::content.about.fields.reason_add'),
            'removeLabel' => __('content::content.about.fields.reason_remove'),
            'fields'      => [
                'title' => __('content::content.about.fields.item_title'),
                'body'  => __('content::content.about.fields.item_body'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.about.save') }}</button>
    </div>
</form>
