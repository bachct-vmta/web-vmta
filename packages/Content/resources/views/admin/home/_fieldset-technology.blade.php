<form method="POST" action="{{ route(admin_route_name('home.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-home-fieldset="technology">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', [
        'field'   => 'title',
        'label'   => __('content::content.home.fields.title'),
        'section' => $section,
    ])

    {{-- 2 subgroups (fixed), each with title + bullets --}}
    @foreach(['vi', 'en'] as $locale)
        @php
            $tr       = $section?->translations?->firstWhere('locale', $locale);
            $existing = $tr?->items ?? [];
        @endphp
        <details class="rounded border border-slate-200 p-3" open>
            <summary class="font-medium text-sm text-slate-700 cursor-pointer">
                {{ __('content::content.tabs.translation', ['locale' => strtoupper($locale)]) }}
            </summary>
            <div class="mt-3 space-y-4">
                @for($i = 0; $i < 2; $i++)
                    <fieldset class="rounded bg-slate-50 p-3 border border-slate-200">
                        <legend class="text-xs uppercase text-slate-500 px-1">
                            {{ __('content::content.home.fields.item_title') }} #{{ $i + 1 }}
                        </legend>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-slate-600 mb-1">
                                {{ __('content::content.home.fields.item_title') }}
                            </label>
                            <input type="text"
                                   name="translations[{{ $locale }}][items][{{ $i }}][title]"
                                   value="{{ old("translations.{$locale}.items.{$i}.title", $existing[$i]['title'] ?? '') }}"
                                   class="w-full rounded border-slate-300 text-sm">
                        </div>
                        @php $bullets = old("translations.{$locale}.items.{$i}.bullets", $existing[$i]['bullets'] ?? ['']); @endphp
                        @include('content::admin.home._bullets-repeater', [
                            'locale'     => $locale,
                            'itemIndex'  => $i,
                            'bullets'    => is_array($bullets) ? $bullets : [''],
                            'min'        => 1,
                            'maxBullets' => 8,
                        ])
                    </fieldset>
                @endfor
            </div>
        </details>
    @endforeach

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit"
                class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
            {{ __('content::content.home.save') }}
        </button>
    </div>
</form>
