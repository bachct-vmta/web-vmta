@extends('core::layouts.admin')

@section('title', __('chatbot::chatbot.documents.chunks_heading'))
@section('page-title', __('chatbot::chatbot.documents.chunks_heading'))

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-4">
    <a href="{{ route('admin.chatbot.documents.index') }}" class="text-sm text-slate-600 hover:underline">
        {{ __('chatbot::chatbot.documents.back_to_list') }}
    </a>

    @if($upstreamError ?? null)
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
            {{ __('chatbot::chatbot.documents.upstream_error', ['error' => $upstreamError]) }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-slate-800">{{ __('chatbot::chatbot.documents.chunks_heading') }}</h1>
    <p class="text-xs font-mono text-slate-500">document_id: {{ $documentId }}</p>

    @if(empty($chunks))
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
            {{ __('chatbot::chatbot.documents.chunks_empty') }}
        </div>
    @else
        <div class="space-y-3">
            @foreach($chunks as $chunk)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center gap-3 mb-2 text-xs">
                        <span class="font-mono text-slate-500">
                            {{ __('chatbot::chatbot.documents.chunk_index_label') }}{{ $chunk['chunk_index'] ?? '?' }}
                        </span>
                        @if(!empty($chunk['heading']))
                            <span class="text-slate-700">
                                <span class="text-slate-400">{{ __('chatbot::chatbot.documents.chunk_heading_label') }}:</span>
                                <span class="font-semibold">{{ $chunk['heading'] }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">{{ $chunk['content'] ?? '' }}</div>
                    @if(!empty($chunk['image_paths']))
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach((array) $chunk['image_paths'] as $img)
                                <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $img }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
