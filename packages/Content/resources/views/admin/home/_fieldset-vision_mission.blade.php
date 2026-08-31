<form method="POST" action="{{ route(admin_route_name('home.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-home-fieldset="vision_mission">
    @csrf @method('PUT')

    {{-- Video URL (HLS m3u8, YouTube embed, or Vimeo player) --}}
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('content::content.home.fields.video_url') }}</label>
        <input type="text" name="video_url"
               value="{{ old('video_url', $section?->video_url) }}"
               placeholder="https://youtube.com/embed/… or https://…/stream.m3u8"
               class="w-full rounded border-slate-300 text-sm">
        <p class="text-xs text-slate-400 mt-1">Chấp nhận: YouTube embed, Vimeo player, HLS .m3u8</p>
    </div>

    {{-- TẦM NHÌN title --}}
    @include('content::admin.home._locale-input', [
        'field'   => 'title',
        'label'   => __('content::content.home.fields.title') . ' (TẦM NHÌN)',
        'section' => $section,
    ])

    {{-- SỨ MỆNH heading override --}}
    @include('content::admin.home._locale-input', [
        'field'   => 'subtitle',
        'label'   => __('content::content.home.fields.subtitle') . ' (SỨ MỆNH heading)',
        'section' => $section,
    ])

    {{-- Vision body text --}}
    @include('content::admin.home._locale-input', [
        'field'   => 'body',
        'label'   => __('content::content.home.fields.body'),
        'section' => $section,
        'type'    => 'textarea',
    ])

    {{-- 3 audience cards (fixed) --}}
    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            Audience cards <span class="text-xs text-slate-400">(exactly 3)</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section' => $section,
            'count'   => 3,
            'fields'  => [
                'audience' => __('content::content.home.fields.item_audience'),
                'body'     => __('content::content.home.fields.item_body'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit"
                class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
            {{ __('content::content.home.save') }}
        </button>
    </div>
</form>
