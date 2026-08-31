<form method="POST" action="{{ route(admin_route_name('alliance.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-alliance-fieldset="join_form">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title',     'label' => __('content::content.alliance.fields.title'),     'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'body',      'label' => __('content::content.alliance.fields.body'),      'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._locale-input', ['field' => 'cta_label', 'label' => __('content::content.alliance.fields.cta_label'), 'section' => $section])

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.alliance.save') }}</button>
    </div>
</form>
