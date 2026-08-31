<form method="POST" action="{{ route(admin_route_name('alliance.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-alliance-fieldset="standards">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.alliance.fields.title'), 'section' => $section])

    {{-- Single CKEditor per locale for the standards body (HTML sanitized server-side via Purifier). --}}
    <div class="space-y-4">
        @foreach(['vi', 'en'] as $locale)
            @php
                $tr = $section?->translations?->firstWhere('locale', $locale);
                $bodyVal = old("translations.{$locale}.body", $tr?->body ?? '');
            @endphp
            <details class="rounded border border-slate-200 p-3" open>
                <summary class="font-medium text-sm text-slate-700 cursor-pointer">
                    {{ __('content::content.tabs.translation', ['locale' => strtoupper($locale)]) }}
                </summary>
                <div class="mt-3">
                    <x-core::ckeditor
                        :name="'translations['.$locale.'][body]'"
                        :value="$bodyVal"
                        :label="__('content::content.alliance.fields.standards_items')"
                        rows="12"
                    />
                </div>
            </details>
        @endforeach
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.alliance.save') }}</button>
    </div>
</form>
