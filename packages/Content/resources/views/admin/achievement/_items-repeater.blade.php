@php
    /**
     * Items repeater with optional media field.
     * Required vars:
     *   - $section: ?AchievementSection
     *   - $count: int — fixed cardinality
     *   - $fields: array<string, array{label: string, type?: 'text'|'textarea'|'media'}>
     * Optional:
     *   - $locales: array<string> (default ['vi','en'])
     */
    $locales = $locales ?? ['vi', 'en'];
@endphp
<div class="space-y-4">
    @foreach($locales as $locale)
        @php
            $tr = $section?->translations?->firstWhere('locale', $locale);
            $existing = $tr?->items ?? [];
        @endphp
        <details class="rounded border border-slate-200 p-3" open>
            <summary class="font-medium text-sm text-slate-700 cursor-pointer">
                {{ __('content::content.tabs.translation', ['locale' => strtoupper($locale)]) }}
            </summary>
            <div class="mt-3 space-y-3">
                @for($i = 0; $i < $count; $i++)
                    <fieldset class="rounded bg-slate-50 p-3 border border-slate-200">
                        <legend class="text-xs uppercase text-slate-500 px-1">#{{ $i + 1 }}</legend>
                        <div class="grid grid-cols-1 md:grid-cols-{{ min(count($fields), 3) }} gap-3">
                            @foreach($fields as $field => $config)
                                @php
                                    $label = is_array($config) ? ($config['label'] ?? $field) : $config;
                                    $type = is_array($config) ? ($config['type'] ?? 'text') : 'text';
                                    $val = old("translations.{$locale}.items.{$i}.{$field}", $existing[$i][$field] ?? '');
                                @endphp
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                                    @if($type === 'media')
                                        <input type="number" min="1"
                                               name="translations[{{ $locale }}][items][{{ $i }}][{{ $field }}]"
                                               value="{{ $val }}"
                                               placeholder="Media ID"
                                               class="w-full rounded border-slate-300 text-sm">
                                    @elseif($type === 'textarea')
                                        <textarea name="translations[{{ $locale }}][items][{{ $i }}][{{ $field }}]"
                                                  rows="3"
                                                  class="w-full rounded border-slate-300 text-sm">{{ $val }}</textarea>
                                    @else
                                        <input type="text"
                                               name="translations[{{ $locale }}][items][{{ $i }}][{{ $field }}]"
                                               value="{{ $val }}"
                                               class="w-full rounded border-slate-300 text-sm">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endfor
            </div>
        </details>
    @endforeach
</div>
