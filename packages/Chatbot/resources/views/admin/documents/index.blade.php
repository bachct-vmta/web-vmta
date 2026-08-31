@extends('core::layouts.admin')

@section('title', __('chatbot::chatbot.documents.heading'))
@section('page-title', __('chatbot::chatbot.documents.heading'))

@php
    $statusBadge = [
        'pending' => 'bg-slate-100 text-slate-600',
        'processing' => 'bg-amber-100 text-amber-800',
        'processed' => 'bg-green-100 text-green-800',
        'error' => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">{{ session('status') }}</div>
    @endif

    @if($upstreamError ?? null)
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
            {{ __('chatbot::chatbot.documents.upstream_error', ['error' => $upstreamError]) }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ __('chatbot::chatbot.documents.heading') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('chatbot::chatbot.documents.description') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.chatbot.documents.process-all') }}"
              onsubmit="return confirm(@js(__('chatbot::chatbot.documents.confirm_process_all')))">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2">
                <span class="material-symbols-rounded text-base">all_inclusive</span>
                {{ __('chatbot::chatbot.documents.process_all') }}
            </button>
        </form>
    </div>

    {{-- Search bar --}}
    <form method="GET" action="{{ route('admin.chatbot.documents.index') }}" class="flex gap-2">
        <input type="text" name="q" value="{{ $q }}"
               placeholder="{{ __('chatbot::chatbot.documents.search_placeholder') }}"
               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2">
            {{ __('chatbot::chatbot.documents.search') }}
        </button>
        @if($q)
            <a href="{{ route('admin.chatbot.documents.index') }}" class="rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold px-4 py-2">
                {{ __('chatbot::chatbot.documents.reset') }}
            </a>
        @endif
    </form>

    {{-- Documents table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        @if(empty($documents))
            <div class="p-8 text-center text-slate-500 text-sm">
                {{ __('chatbot::chatbot.documents.list_empty') }}
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_filename') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_type') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_group') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_status') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_chunks') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_uploaded_by') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.documents.col_created_at') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('chatbot::chatbot.documents.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($documents as $doc)
                        @php $status = $doc['status'] ?? 'pending'; @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="text-slate-800 font-medium break-all">{{ $doc['filename'] ?? '—' }}</div>
                                @if($status === 'error' && !empty($doc['error_message']))
                                    <div class="text-xs text-red-600 mt-1">{{ $doc['error_message'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 uppercase text-xs">{{ $doc['file_type'] ?? '' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $doc['group_code'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusBadge[$status] ?? $statusBadge['pending'] }}">
                                    {{ __('chatbot::chatbot.documents.status.'.$status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $doc['total_chunks'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $doc['uploaded_by_username'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $doc['created_at'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                @if($status !== 'processing')
                                    <form method="POST" action="{{ route('admin.chatbot.documents.process', $doc['id']) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-800 text-xs font-semibold">
                                            {{ __('chatbot::chatbot.documents.process') }}
                                        </button>
                                    </form>
                                @endif
                                @if(($doc['total_chunks'] ?? 0) > 0)
                                    <a href="{{ route('admin.chatbot.documents.chunks', $doc['id']) }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                        {{ __('chatbot::chatbot.documents.view_chunks') }}
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.chatbot.documents.destroy', $doc['id']) }}"
                                      onsubmit="return confirm(@js(__('chatbot::chatbot.documents.confirm_delete')))"
                                      class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-semibold">
                                        {{ __('chatbot::chatbot.documents.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Upload form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800 mb-4">
            {{ __('chatbot::chatbot.documents.upload_heading') }}
        </h2>
        @if(empty($groups))
            <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                {{ __('chatbot::chatbot.documents.no_groups') }}
                <a href="{{ route('admin.chatbot.document-groups.index') }}" class="underline font-semibold">→</a>
            </p>
        @else
            <form method="POST" action="{{ route('admin.chatbot.documents.store') }}" enctype="multipart/form-data"
                  class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        {{ __('chatbot::chatbot.documents.field_file') }}
                    </label>
                    <input type="file" name="file" required
                           accept=".doc,.docx,.pdf,.txt,.md,.markdown"
                           class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        {{ __('chatbot::chatbot.documents.field_group') }}
                    </label>
                    <select name="group_code" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach($groups as $g)
                            <option value="{{ $g['code'] }}" @selected(old('group_code') === $g['code'])>
                                {{ $g['code'] }} — {{ $g['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2">
                        {{ __('chatbot::chatbot.documents.submit_upload') }}
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- Scan form --}}
    @if(!empty($groups))
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-base font-semibold text-slate-800 mb-1">
                {{ __('chatbot::chatbot.documents.scan_heading') }}
            </h2>
            <p class="text-xs text-slate-500 mb-3">{{ __('chatbot::chatbot.documents.scan_description') }}</p>
            <form method="POST" action="{{ route('admin.chatbot.documents.scan') }}" class="flex gap-2 items-end">
                @csrf
                <select name="group_code" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach($groups as $g)
                        <option value="{{ $g['code'] }}">{{ $g['code'] }} — {{ $g['name'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2">
                    {{ __('chatbot::chatbot.documents.scan_button') }}
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
