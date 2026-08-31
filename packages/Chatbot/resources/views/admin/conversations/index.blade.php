@extends('core::layouts.admin')

@section('title', __('chatbot::chatbot.conversations.heading'))
@section('page-title', __('chatbot::chatbot.conversations.heading'))

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">
    @if($upstreamError ?? null)
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
            {{ __('chatbot::chatbot.conversations.upstream_error', ['error' => $upstreamError]) }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ __('chatbot::chatbot.conversations.heading') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('chatbot::chatbot.conversations.description') }}</p>
    </div>

    {{-- Search bar --}}
    <form method="GET" action="{{ route('admin.chatbot.conversations.index') }}" class="flex gap-2">
        <input type="text" name="q" value="{{ $q }}" minlength="2"
               placeholder="{{ __('chatbot::chatbot.conversations.search_placeholder') }}"
               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2">
            {{ __('chatbot::chatbot.conversations.search') }}
        </button>
        @if($q)
            <a href="{{ route('admin.chatbot.conversations.index') }}" class="rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold px-4 py-2">
                {{ __('chatbot::chatbot.conversations.reset') }}
            </a>
        @endif
    </form>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        @if(empty($conversations))
            <div class="p-8 text-center text-slate-500 text-sm">
                {{ __('chatbot::chatbot.conversations.list_empty') }}
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.conversations.col_title') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.conversations.col_owner') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.conversations.col_provider') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.conversations.col_group') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.conversations.col_updated_at') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('chatbot::chatbot.conversations.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($conversations as $conv)
                        <tr>
                            <td class="px-4 py-3 text-slate-800 font-medium">{{ $conv['title'] ?? '(no title)' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $conv['owner_username'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $conv['ai_provider'] ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $conv['document_group'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $conv['updated_at'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.chatbot.conversations.show', $conv['id']) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                    {{ __('chatbot::chatbot.conversations.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination (only when not searching) --}}
    @if(! $q && count($conversations) >= $limit)
        <div class="flex justify-end gap-2">
            @if($skip > 0)
                <a href="{{ route('admin.chatbot.conversations.index', ['skip' => max(0, $skip - $limit), 'limit' => $limit]) }}"
                   class="rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold px-4 py-2">←</a>
            @endif
            <a href="{{ route('admin.chatbot.conversations.index', ['skip' => $skip + $limit, 'limit' => $limit]) }}"
               class="rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold px-4 py-2">→</a>
        </div>
    @endif
</div>
@endsection
