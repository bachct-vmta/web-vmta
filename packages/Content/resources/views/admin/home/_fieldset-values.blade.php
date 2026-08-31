<form method="POST" action="{{ route(admin_route_name('home.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-home-fieldset="values">
    @csrf @method('PUT')
    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.home.fields.title'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'subtitle', 'label' => __('content::content.home.fields.subtitle'), 'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._items-repeater', [
        'section' => $section, 'count' => 3,
        'fields' => [
            'title' => __('content::content.home.fields.item_title'),
            'body' => __('content::content.home.fields.item_body'),
        ],
    ])
    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.home.save') }}</button>
    </div>
</form>
