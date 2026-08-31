@extends('core::layouts.admin')

@section('title', __('chatbot::chatbot.document_groups.heading'))
@section('page-title', __('chatbot::chatbot.document_groups.heading'))

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">{{ session('status') }}</div>
    @endif

    @if(session('upstream_error'))
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
            {{ __('chatbot::chatbot.document_groups.upstream_error', ['error' => session('upstream_error')]) }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ __('chatbot::chatbot.document_groups.heading') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('chatbot::chatbot.document_groups.description') }}</p>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        @if(empty($groups))
            <div class="p-8 text-center text-slate-500 text-sm">
                {{ __('chatbot::chatbot.document_groups.list_empty') }}
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.document_groups.col_code') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.document_groups.col_name') }}</th>
                        <th class="px-4 py-3">{{ __('chatbot::chatbot.document_groups.col_created_at') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('chatbot::chatbot.document_groups.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($groups as $g)
                        <tr>
                            <td class="px-4 py-3 font-mono font-semibold text-slate-700">{{ $g['code'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $g['name'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $g['created_at'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.chatbot.document-groups.destroy', $g['code']) }}"
                                      onsubmit="return confirm(@js(__('chatbot::chatbot.document_groups.confirm_delete', ['code' => $g['code'] ?? ''])))"
                                      class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-semibold">
                                        {{ __('chatbot::chatbot.document_groups.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Create form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800 mb-4">
            {{ __('chatbot::chatbot.document_groups.new_heading') }}
        </h2>
        <form method="POST" action="{{ route('admin.chatbot.document-groups.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    {{ __('chatbot::chatbot.document_groups.field_code') }}
                </label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       pattern="[A-Za-z0-9_\-]+" maxlength="50"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono uppercase"
                       style="text-transform: uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    {{ __('chatbot::chatbot.document_groups.field_name') }}
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="200"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <button type="submit" class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 text-sm font-semibold">
                    {{ __('chatbot::chatbot.document_groups.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
