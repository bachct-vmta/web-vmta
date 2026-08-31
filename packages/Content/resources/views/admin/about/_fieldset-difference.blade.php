<form method="POST" action="{{ route(admin_route_name('about.update'), ['position' => $position->value]) }}"
      class="space-y-4 bg-white border border-slate-200 rounded p-5" data-about-fieldset="difference">
    @csrf @method('PUT')

    @include('content::admin.home._locale-input', ['field' => 'title', 'label' => __('content::content.about.fields.title'), 'section' => $section])

    {{-- Bullet points: dynamic, at least 1, admin can add/remove rows --}}
    <div>
        <p class="text-sm font-medium text-slate-700 mb-2">
            {{ __('content::content.about.fields.bullet') }} <span class="text-xs text-slate-400">(tối thiểu 1, thêm/xoá tuỳ ý)</span>
        </p>
        @include('content::admin.home._items-repeater', [
            'section'     => $section,
            'count'       => 4,
            'variable'    => true,
            'min'         => 1,
            'addLabel'    => '+ Thêm Bullet',
            'removeLabel' => 'Xoá hàng',
            'fields'      => [
                'text' => __('content::content.about.fields.bullet'),
            ],
        ])
    </div>

    <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">{{ __('content::content.about.save') }}</button>
    </div>
</form>
