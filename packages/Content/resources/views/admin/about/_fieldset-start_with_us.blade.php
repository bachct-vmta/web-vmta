<form method="POST" action="{{ route(admin_route_name('about.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-about-fieldset="start_with_us">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title',     'label' => __('content::content.about.fields.title'),     'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'body',      'label' => __('content::content.about.fields.body'),      'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._locale-input', ['field' => 'cta_label', 'label' => __('content::content.about.fields.cta1_label'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'cta_link',  'label' => __('content::content.about.fields.cta1_link'),  'section' => $section, 'type' => 'url'])
    @include('content::admin.home._locale-input', ['field' => 'subtitle',  'label' => __('content::content.about.fields.cta2_label'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'cta2_link', 'label' => __('content::content.about.fields.cta2_link'),  'section' => $section, 'type' => 'url'])

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.about.save') }}</button>
    </div>
</form>
