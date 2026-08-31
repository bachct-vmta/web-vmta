@extends('core::layouts.admin')

@section('title', trans('chatbot::chatbot.settings_title'))
@section('page-title', trans('chatbot::chatbot.settings_title'))

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-rounded text-primary">smart_toy</span>
            {{ trans('chatbot::chatbot.settings_title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ trans('chatbot::chatbot.settings_description') }}
        </p>
    </div>

    <form id="chatbot-settings-form" action="{{ route('admin.chatbot.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Card: API Configuration --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-rounded text-primary text-xl">api</span>
                {{ trans('chatbot::chatbot.settings_section_api') }}
            </h2>

            <div class="space-y-4">
                <div>
                    <label for="document_group" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ trans('chatbot::chatbot.document_group_label') }}
                    </label>
                    <input type="text"
                           id="document_group"
                           name="document_group"
                           value="{{ old('document_group', $settings['document_group']) }}"
                           placeholder="{{ trans('chatbot::chatbot.document_group_hint') }}"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary text-sm"
                           maxlength="50">
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ trans('chatbot::chatbot.document_group_help') }}</p>
                </div>

                <div>
                    <label for="ai_provider" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ trans('chatbot::chatbot.ai_provider_label') }}
                    </label>
                    <input type="text"
                           id="ai_provider"
                           name="ai_provider"
                           value="{{ old('ai_provider', $settings['ai_provider']) }}"
                           placeholder="{{ trans('chatbot::chatbot.ai_provider_hint') }}"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary text-sm"
                           maxlength="50">
                </div>
            </div>
        </div>

        {{-- Card: Session Limits --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-rounded text-primary text-xl">timer</span>
                {{ trans('chatbot::chatbot.settings_section_limits') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="max_messages_per_session" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ trans('chatbot::chatbot.max_messages_label') }}
                    </label>
                    <input type="number"
                           id="max_messages_per_session"
                           name="max_messages_per_session"
                           value="{{ old('max_messages_per_session', $settings['max_messages_per_session']) }}"
                           min="1" max="100"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary text-sm"
                           required>
                </div>

                <div>
                    <label for="session_ttl" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ trans('chatbot::chatbot.session_ttl_label') }}
                    </label>
                    <input type="number"
                           id="session_ttl"
                           name="session_ttl"
                           value="{{ old('session_ttl', $settings['session_ttl']) }}"
                           min="3600" max="604800" step="3600"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary text-sm"
                           required>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ trans('chatbot::chatbot.session_ttl_help') }}</p>
                </div>
            </div>
        </div>

        {{-- Card: Suggested Questions --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
                <span class="material-symbols-rounded text-primary text-xl">quiz</span>
                {{ trans('chatbot::chatbot.suggested_questions_label') }}
            </h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">{{ trans('chatbot::chatbot.suggested_questions_help') }}</p>

            <div class="space-y-3">
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-slate-400 w-5 text-center flex-shrink-0">{{ $i + 1 }}</span>
                    <input type="text"
                           name="suggested_questions[]"
                           value="{{ old('suggested_questions.'.$i, $settings['suggested_questions'][$i] ?? '') }}"
                           placeholder="{{ trans('chatbot::chatbot.suggested_question_placeholder', ['n' => $i + 1]) }}"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary text-sm"
                           maxlength="200">
                </div>
                @endfor
            </div>
        </div>

        {{-- Save --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                <span class="material-symbols-rounded text-lg">save</span>
                {{ trans('core::settings.save') }}
            </button>
        </div>
    </form>
</div>

{{-- Inline toast notification --}}
<div id="chatbot-toast"
     class="fixed top-6 right-6 z-[9999] max-w-sm transition-all duration-300 translate-x-[120%] opacity-0 pointer-events-none">
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium">
        <span id="chatbot-toast-icon" class="material-symbols-rounded text-lg"></span>
        <span id="chatbot-toast-msg"></span>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const toast = document.getElementById('chatbot-toast');
    const inner = toast.querySelector('div');
    const icon = document.getElementById('chatbot-toast-icon');
    const msg = document.getElementById('chatbot-toast-msg');
    let hideTimer;

    function showToast(type, text) {
        clearTimeout(hideTimer);
        msg.textContent = text;
        if (type === 'success') {
            inner.className = 'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium bg-emerald-50 border-emerald-200 text-emerald-700';
            icon.textContent = 'check_circle';
        } else {
            inner.className = 'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium bg-red-50 border-red-200 text-red-700';
            icon.textContent = 'error';
        }
        toast.classList.remove('translate-x-[120%]', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-x-0', 'opacity-100');
        hideTimer = setTimeout(() => {
            toast.classList.add('translate-x-[120%]', 'opacity-0', 'pointer-events-none');
            toast.classList.remove('translate-x-0', 'opacity-100');
        }, 3500);
    }

    document.getElementById('chatbot-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('[type=submit]');
        btn.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const data = await res.json();
            showToast(data.success ? 'success' : 'error', data.message || 'Error');
        } catch (err) {
            console.error('[chatbot-settings]', err);
            showToast('error', 'Unexpected error');
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endpush
@endsection
