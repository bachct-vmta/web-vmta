{{--
  Chatbot floating widget — injected into Site public layout via @stack('scripts').
  Renders on every public page (previously hidden on Inquiry routes; that
  restriction was lifted per UX request — admin wants the chatbot reachable
  even while the user is on the contact form).
--}}
@php
    $hideWidget = false;
    $hotline = \Packages\Core\Src\Models\Setting::get('site.phone') ?? config('chatbot.hotline_phone', '');
    // Inquiry package mounts its routes under the `inquiry.` name group, so
    // the full name is `inquiry.{locale}.contact.show`. The earlier `{locale}.contact.show`
    // form silently failed (caught + swallowed) → contact CTA never rendered.
    $inquiryUrl = '';
    try {
        $inquiryUrl = route('inquiry.'.\Illuminate\Support\Facades\App::getLocale().'.contact.show', ['source' => 'chatbot_overflow']);
    } catch (\Throwable) {}

    $sqRaw = \Packages\Core\Src\Models\Setting::get('chatbot.suggested_questions');
    $suggestedQuestions = is_string($sqRaw) ? json_decode($sqRaw, true) : $sqRaw;
    if (! is_array($suggestedQuestions)) { $suggestedQuestions = []; }
@endphp

@if(!$hideWidget)
{{-- Scoped markdown styling for bot bubbles. Keeps the chat compact while
     letting headers, lists, code, and links render clearly. --}}
<style>
    /* ── User bubble ── */
    .chat-user {
        background-color: #0b7f7c;
        color: #fff;
        font-size: .875rem;
        line-height: 1.5;
        word-break: break-word;
    }

    /* ── Bot markdown bubble ── */
    .chat-md { word-break: break-word; }
    .chat-md > *:first-child { margin-top: 0; }
    .chat-md > *:last-child  { margin-bottom: 0; }
    .chat-md p               { margin: 0 0 .4em; line-height: 1.5; }
    .chat-md h1, .chat-md h2, .chat-md h3, .chat-md h4 {
        font-weight: 600; line-height: 1.35; margin: .5em 0 .25em;
        font-size: .875rem;
    }
    .chat-md strong          { font-weight: 600; }
    .chat-md em              { font-style: italic; }
    .chat-md ul, .chat-md ol { margin: .25em 0 .5em; padding-left: 1.2em; }
    .chat-md ul              { list-style: disc; }
    .chat-md ol              { list-style: decimal; }
    .chat-md li              { margin: .15em 0; }
    .chat-md a               { color: #0b7f7c; text-decoration: underline; word-break: break-word; }
    .chat-md code            { background: rgba(0,0,0,.06); padding: 1px 4px; border-radius: 4px; font-size: .85em; }
    .chat-md pre             { background: rgba(0,0,0,.06); padding: .5em; border-radius: 6px; overflow-x: auto; margin: .4em 0; }
    .chat-md pre code        { background: transparent; padding: 0; }
    .chat-md blockquote      { border-left: 3px solid rgba(0,0,0,.15); padding-left: .6em; color: rgba(0,0,0,.7); margin: .4em 0; }
    .chat-md hr              { border: none; border-top: 1px solid rgba(0,0,0,.12); margin: .6em 0; }
    .chat-md table           { border-collapse: collapse; margin: .4em 0; font-size: .85em; }
    .chat-md th, .chat-md td { border: 1px solid rgba(0,0,0,.15); padding: .25em .5em; }

    .chat-launcher-label {
        animation: chat-launcher-label-float 2.8s ease-in-out infinite;
        transform-origin: bottom right;
    }

    @keyframes chat-launcher-label-float {
        0%, 100% { transform: translateY(0) scale(1); }
        45% { transform: translateY(-3px) scale(1.01); }
    }

    @media (prefers-reduced-motion: reduce) {
        .chat-launcher-label { animation: none; }
    }
</style>

<div
    x-data="chatbotWidget()"
    x-init="init()"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
>
    {{-- Toggle button with ping ring when closed --}}
    <div class="relative">
        <span
            x-show="!open"
            x-transition.opacity.duration.200ms
            class="chat-launcher-label pointer-events-none absolute z-10 inline-flex rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium leading-tight text-slate-700 shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            style="right: 0; bottom: calc(100% + 0.625rem); width: max-content; max-width: min(15rem, calc(100vw - 3rem)); display: none"
        >
            {{ trans('chatbot::chatbot.launcher_label') }}
            <span
                class="absolute size-2 rotate-45 border-b border-r border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900"
                style="right: 1.25rem; bottom: -0.3125rem"
            ></span>
        </span>
        <span
            x-show="!open"
            class="absolute inset-0 rounded-full bg-primary opacity-60 animate-ping"
            style="animation-duration: 1.8s"
        ></span>
        <button
            @click="toggle()"
            aria-label="{{ trans('chatbot::chatbot.title') }}"
            class="relative w-14 h-14 rounded-full bg-primary text-white shadow-lg flex items-center justify-center hover:bg-primary/90 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 bg-[#0b7f7c]"
        >
            <span class="material-symbols-rounded text-2xl" x-show="!open">chat</span>
            <span class="material-symbols-rounded text-2xl" x-show="open">close</span>
        </button>
    </div>

    {{-- Chat panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="w-[360px] max-w-[calc(100vw-3rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden"
        style="height: 480px; display: none"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-[#0b7f7c] text-white rounded-t-2xl">
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-xl">smart_toy</span>
                <span class="font-semibold text-sm">{{ trans('chatbot::chatbot.title') }}</span>
            </div>
            <span
                x-show="!overflow"
                class="text-xs bg-white/20 rounded-full px-2 py-0.5"
                x-text="remainingLabel"
            ></span>
        </div>

        {{-- Messages --}}
        <div
            x-ref="messages"
            class="flex-1 overflow-y-auto p-4 space-y-3 text-sm"
        >
            {{-- Greeting --}}
            <div class="flex gap-2">
                <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="material-symbols-rounded text-primary text-base">smart_toy</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl rounded-tl-none px-3 py-2 max-w-[85%]">
                    {{ trans('chatbot::chatbot.greeting') }}
                </div>
            </div>

            {{-- Suggested questions — hidden once user sends first message --}}
            @if(count($suggestedQuestions) > 0)
            <div x-show="messages.length === 0 && !streaming" class="space-y-2 pt-1">
                @foreach($suggestedQuestions as $sq)
                <button type="button"
                        @click="input = '{{ addslashes(e($sq)) }}'; send()"
                        class="w-full text-left text-sm px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-[#0b7f7c] hover:text-[#0b7f7c] transition-colors">
                    {{ $sq }}
                </button>
                @endforeach
            </div>
            @endif

            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex gap-2'">
                    {{-- Bot avatar --}}
                    <div x-show="msg.role === 'bot'" class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="material-symbols-rounded text-primary text-base">smart_toy</span>
                    </div>
                    <div
                        :class="msg.role === 'user'
                            ? 'chat-user rounded-2xl rounded-tr-none px-3 py-2 max-w-[85%]'
                            : 'chat-md bg-slate-100 dark:bg-slate-800 rounded-2xl rounded-tl-none px-3 py-2 max-w-[85%]'"
                        x-html="msg.html"
                    ></div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="streaming" class="flex gap-2">
                <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="material-symbols-rounded text-primary text-base">smart_toy</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl rounded-tl-none px-3 py-2">
                    <span class="inline-flex gap-1">
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- CTA overflow panel --}}
        <div x-show="overflow" class="px-4 py-4 bg-amber-50 dark:bg-amber-900/20 border-t border-amber-200 dark:border-amber-700 text-center space-y-3">
            <p class="text-sm text-slate-700 dark:text-slate-200 font-medium">{{ trans('chatbot::chatbot.limit_reached') }}</p>
            <p class="text-xs text-slate-500">{{ trans('chatbot::chatbot.cta_subtitle') }}</p>
            <div class="flex flex-wrap gap-2 justify-center">
                <button @click="newChat()"
                        class="flex items-center gap-1.5 text-sm font-medium text-white px-4 py-2 rounded-lg transition-colors bg-[#0b7f7c] hover:bg-[#096e6b]">
                    <span class="material-symbols-rounded text-base">add_comment</span>
                    {{ trans('chatbot::chatbot.cta_new_chat') }}
                </button>
                @if($hotline)
                <a href="tel:{{ $hotline }}"
                   class="flex items-center gap-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition-colors">
                    <span class="material-symbols-rounded text-base">phone</span>
                    {{ trans('chatbot::chatbot.cta_hotline') }}
                </a>
                @endif
                @if($inquiryUrl)
                <a href="{{ $inquiryUrl }}"
                   class="flex items-center gap-1.5 text-sm font-medium text-white bg-[#d31e45] hover:bg-[#b81a3c] px-4 py-2 rounded-lg transition-colors">
                    <span class="material-symbols-rounded text-base">edit_note</span>
                    {{ trans('chatbot::chatbot.cta_form') }}
                </a>
                @endif
            </div>
        </div>

        {{-- Input --}}
        <div x-show="!overflow" class="px-3 py-3 border-t border-slate-100 dark:border-slate-700 flex gap-2">
            <input
                x-ref="input"
                x-model="input"
                @keydown.enter.prevent="send()"
                :disabled="streaming"
                type="text"
                placeholder="{{ trans('chatbot::chatbot.placeholder') }}"
                class="flex-1 text-sm rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-primary focus:border-primary disabled:opacity-50 px-3 py-2"
                maxlength="2000"
            >
            <button
                @click="send()"
                :disabled="streaming || !input.trim()"
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-primary text-white hover:bg-primary/90 disabled:opacity-40 transition-colors flex-shrink-0 bg-[#0b7f7c]"
            >
                <span class="material-symbols-rounded text-lg">send</span>
            </button>
        </div>
    </div>
</div>

<script>
window.__chatbotConfig = {
    sessionUrl: '{{ route("chatbot.session") }}',
    messageUrl: '{{ route("chatbot.message") }}',
    newChatUrl: '{{ route("chatbot.new-chat") }}',
    csrfToken: '{{ csrf_token() }}',
    remainingTpl: '{{ trans("chatbot::chatbot.remaining", ["count" => "__COUNT__"]) }}',
    errorMsg: '{{ trans("chatbot::chatbot.upstream_error") }}',
};
</script>
@endif
