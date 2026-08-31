@extends('core::layouts.admin')

@section('title', __('chatbot::chatbot.conversations.detail_heading'))
@section('page-title', __('chatbot::chatbot.conversations.detail_heading'))

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-4">
    <a href="{{ route('admin.chatbot.conversations.index') }}" class="text-sm text-slate-600 hover:underline">
        {{ __('chatbot::chatbot.conversations.back_to_list') }}
    </a>

    @if($upstreamError ?? null)
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
            {{ __('chatbot::chatbot.conversations.upstream_error', ['error' => $upstreamError]) }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-slate-800">{{ __('chatbot::chatbot.conversations.detail_heading') }}</h1>
    <p class="text-xs font-mono text-slate-500">conversation_id: {{ $conversationId }}</p>

    @if(empty($messages))
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
            {{ __('chatbot::chatbot.conversations.no_messages') }}
        </div>
    @else
        <div class="space-y-3">
            @foreach($messages as $msg)
                @php
                    $isUser = ($msg['role'] ?? null) === 'user';
                    $messageContent = (string) ($msg['content'] ?? '');
                @endphp
                <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-3 {{ $isUser ? 'bg-primary-600 text-white' : 'bg-white border border-slate-200 text-slate-800' }}">
                        <div class="text-xs font-semibold opacity-70 mb-1">
                            {{ $isUser ? __('chatbot::chatbot.conversations.role_user') : __('chatbot::chatbot.conversations.role_assistant') }}
                            @if(!$isUser && !empty($msg['ai_provider']))
                                <span class="ml-2 font-normal">({{ $msg['ai_provider'] }}{{ !empty($msg['model_used']) ? ' / '.$msg['model_used'] : '' }})</span>
                            @endif
                        </div>
                        @if($isUser)
                            <div class="text-sm whitespace-pre-wrap leading-relaxed">{{ $messageContent }}</div>
                        @else
                            <div class="prose prose-sm max-w-none text-sm leading-relaxed prose-a:text-primary-700 prose-pre:whitespace-pre-wrap">
                                {!! clean(\Illuminate\Support\Str::markdown($messageContent, [
                                    'html_input' => 'strip',
                                    'allow_unsafe_links' => false,
                                ])) !!}
                            </div>
                        @endif

                        @if(!$isUser && !empty($msg['source_chunks']))
                            <details class="mt-3 text-xs">
                                <summary class="cursor-pointer text-slate-500 hover:text-slate-700">
                                    {{ __('chatbot::chatbot.conversations.sources_heading') }} ({{ count($msg['source_chunks']) }})
                                </summary>
                                <div class="mt-2 space-y-2">
                                    @foreach($msg['source_chunks'] as $src)
                                        <div class="rounded bg-slate-50 border border-slate-200 p-2">
                                            <div class="font-semibold text-slate-700">{{ $src['document_name'] ?? '—' }}</div>
                                            @if(!empty($src['heading']))
                                                <div class="text-slate-500">{{ $src['heading'] }}</div>
                                            @endif
                                            <div class="mt-1 text-slate-600">{{ $src['content_preview'] ?? '' }}</div>
                                            @if(isset($src['similarity']))
                                                <div class="mt-1 text-slate-400 font-mono">sim: {{ number_format($src['similarity'], 4) }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif

                        <div class="mt-2 text-[10px] opacity-50">{{ $msg['created_at'] ?? '' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
