<div class="space-y-4 bg-white border border-slate-200 rounded p-5">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">{{ __('content::achievement.cases.heading') }}</h3>
        <a href="{{ route(admin_route_name('achievement.cases.create')) }}"
           class="rounded-md bg-emerald-600 px-4 py-2 text-white text-sm font-semibold hover:bg-emerald-700">
            + {{ __('content::achievement.cases.create_new') }}
        </a>
    </div>

    @if($cases->isEmpty())
        <p class="text-sm text-slate-500 italic">{{ __('content::achievement.cases.empty') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('content::achievement.cases.col_title') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('content::achievement.cases.col_slug') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('content::achievement.cases.col_sort') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('content::achievement.cases.col_active') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('content::achievement.cases.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($cases as $case)
                        @php $caseT = $case->translations->firstWhere('locale', 'vi'); @endphp
                        <tr>
                            <td class="px-3 py-2">{{ $caseT?->title ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-500 font-mono text-xs">{{ $case->slug }}</td>
                            <td class="px-3 py-2">{{ $case->sort_order }}</td>
                            <td class="px-3 py-2">
                                @if($case->is_active)
                                    <span class="rounded bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">{{ __('content::achievement.cases.active') }}</span>
                                @else
                                    <span class="rounded bg-slate-200 text-slate-700 px-2 py-0.5 text-xs">{{ __('content::achievement.cases.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <a href="{{ route(admin_route_name('achievement.cases.edit'), $case) }}"
                                   class="text-blue-600 hover:underline text-sm">{{ __('content::achievement.cases.edit') }}</a>
                                <a href="{{ route(admin_route_name('achievement.cases.detail.edit'), $case) }}"
                                   class="text-teal-600 hover:underline text-sm"
                                   title="{{ __('content::achievement.cases.form.detail_page_title') }}">{{ __('content::achievement.cases.detail') }}</a>
                                <form method="POST"
                                      action="{{ route(admin_route_name('achievement.cases.destroy'), $case) }}"
                                      class="inline"
                                      onsubmit="return confirm('{{ __('content::achievement.cases.confirm_delete') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">{{ __('content::achievement.cases.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
