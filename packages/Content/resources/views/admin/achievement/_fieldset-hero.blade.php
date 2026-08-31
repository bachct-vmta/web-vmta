<form method="POST" action="{{ route(admin_route_name('achievement.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5">
    @csrf @method('PUT')

    @include('content::admin.achievement._locale-input', [
        'field' => 'title', 'label' => __('content::achievement.fields.title'), 'section' => $section,
    ])
    @include('content::admin.achievement._locale-input', [
        'field' => 'subtitle', 'label' => __('content::achievement.fields.eyebrow'), 'section' => $section,
    ])
    @include('content::admin.achievement._locale-input', [
        'field' => 'body', 'label' => __('content::achievement.fields.hero_body'), 'section' => $section,
        'type' => 'textarea', 'rows' => 5,
    ])

    <div>
        <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('content::achievement.fields.stats') }}</h3>
        @include('content::admin.achievement._items-repeater', [
            'section' => $section, 'count' => 3,
            'fields' => [
                'value' => ['label' => __('content::achievement.fields.stat_value'), 'type' => 'text'],
                'label' => ['label' => __('content::achievement.fields.stat_label'), 'type' => 'textarea'],
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
            {{ __('content::achievement.save') }}
        </button>
    </div>
</form>
