<form method="POST" action="{{ route(admin_route_name('contact.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-contact-fieldset="hero">
    @csrf @method('PUT')

    <div class="flex justify-end pb-3 mb-3 border-b border-slate-100">
        @include('content::admin.partials._translate-button', ['spec' => [
            'scalars' => [
                ['from' => 'translations[vi][title]', 'to' => 'translations[en][title]', 'kind' => 'text'],
                ['from' => 'translations[vi][subtitle]', 'to' => 'translations[en][subtitle]', 'kind' => 'text'],
                ['from' => 'translations[vi][body]', 'to' => 'translations[en][body]', 'kind' => 'textarea'],
                ['from' => 'translations[vi][extra]', 'to' => 'translations[en][extra]', 'kind' => 'textarea'],
            ],
        ]])
    </div>


    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('inquiry::inquiry.admin.contact.fields.banner_title'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'subtitle', 'label' => __('inquiry::inquiry.admin.contact.fields.heading'), 'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'body', 'label' => __('inquiry::inquiry.admin.contact.fields.subheading'), 'section' => $section, 'type' => 'textarea'])
    @include('content::admin.home._locale-input', ['field' => 'extra', 'label' => __('inquiry::inquiry.admin.contact.fields.subheading2'), 'section' => $section, 'type' => 'textarea'])

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('inquiry::inquiry.admin.contact.save') }}</button>
    </div>
</form>
