<form method="POST" action="{{ route(admin_route_name('alliance.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-alliance-fieldset="overview">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title',    'label' => __('content::content.alliance.fields.title'),    'section' => $section])
    @include('content::admin.home._locale-input', ['field' => 'subtitle', 'label' => __('content::content.alliance.fields.subtitle'), 'section' => $section])

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['vi', 'en'] as $locale)
            @php $tr = $section?->translations?->firstWhere('locale', $locale); @endphp
            <x-core::ckeditor
                :name="'translations[' . $locale . '][body]'"
                :value="old('translations.' . $locale . '.body', $tr?->body ?? '')"
                :label="__('content::content.alliance.fields.body') . ' (' . strtoupper($locale) . ')'"
                rows="10" />
        @endforeach
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.alliance.save') }}</button>
    </div>
</form>
