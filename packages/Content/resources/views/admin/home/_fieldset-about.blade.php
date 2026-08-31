<form method="POST" action="{{ route(admin_route_name('home.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-home-fieldset="about">
    @csrf @method('PUT')
    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.home.fields.title'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'subtitle', 'label' => __('content::content.home.fields.subtitle'), 'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._locale-input', ['field' => 'body', 'label' => __('content::content.home.fields.body'), 'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._locale-input', ['field' => 'cta_label', 'label' => __('content::content.home.fields.cta_label'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'cta_url', 'label' => __('content::content.home.fields.cta_url'), 'section' => $section])
    @include('content::admin.home._items-repeater', [
        'section' => $section, 'count' => 3,
        'fields' => ['bullet' => __('content::content.home.fields.item_bullet')],
    ])
    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.home.save') }}</button>
    </div>
</form>
