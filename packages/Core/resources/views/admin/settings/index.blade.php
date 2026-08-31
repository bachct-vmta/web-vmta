@extends('core::layouts.admin')

@section('title', __('core::settings.heading'))
@section('page-title', __('core::settings.heading'))

@section('content')
<div class="max-w-4xl mx-auto p-6" x-data="{ tab: '{{ $groups->keys()->first() ?? 'site' }}' }">
    @if(session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
        @foreach($groups as $group => $_items)
            <button type="button"
                    @click="tab = '{{ $group }}'"
                    :class="tab === '{{ $group }}' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium transition">
                {{ __('core::settings.groups.'.$group) }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($groups as $group => $items)
            <div x-show="tab === '{{ $group }}'" class="space-y-5">
                @foreach($items as $setting)
                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                        @php
                            // Resolve human-friendly label from core::settings.keys.<key>;
                            // fall back to raw setting key when no translation exists.
                            $labelKey = 'core::settings.keys.'.$setting->key;
                            $label = trans()->has($labelKey) ? __($labelKey) : $setting->key;
                        @endphp
                        <label class="block text-sm font-semibold text-gray-700 mb-1" for="setting-{{ $loop->parent->index }}-{{ $loop->index }}">
                            {{ $label }}
                            <span class="ml-1 text-xs text-gray-400 font-mono font-normal">({{ $setting->key }})</span>
                            @if($setting->is_encrypted)
                                <span class="ml-2 inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded">
                                    <span class="material-symbols-rounded text-sm">lock</span>
                                    {{ __('core::settings.encrypted') }}
                                </span>
                            @endif
                        </label>
                        @if($setting->description)
                            <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                        @endif

                        @php $value = old('settings.'.$setting->key, $setting->is_encrypted ? '' : \Packages\Core\Src\Models\Setting::get($setting->key)); @endphp

                        @php $isMediaPicker = $setting->type === 'int' && \Illuminate\Support\Str::endsWith($setting->key, '_media_id'); @endphp

                        @if($isMediaPicker)
                            @php
                                $mediaModel = $value ? \Packages\Core\Src\Models\MediaFile::find($value) : null;
                            @endphp
                            <x-core::media-picker
                                :name="'settings['.$setting->key.']'"
                                :value="$value"
                                :preview-url="$mediaModel?->permalink"
                                store="id"
                            />
                        @elseif($setting->type === 'bool')
                            <label class="inline-flex items-center gap-2">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox"
                                       id="setting-{{ $loop->parent->index }}-{{ $loop->index }}"
                                       name="settings[{{ $setting->key }}]" value="1"
                                       @checked((bool) $value)
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ __('core::settings.enabled') }}</span>
                            </label>
                        @elseif($setting->type === 'int')
                            <input type="number"
                                   id="setting-{{ $loop->parent->index }}-{{ $loop->index }}"
                                   name="settings[{{ $setting->key }}]"
                                   value="{{ $value }}"
                                   class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @elseif($setting->type === 'json')
                            <textarea name="settings[{{ $setting->key }}]" rows="4"
                                      id="setting-{{ $loop->parent->index }}-{{ $loop->index }}"
                                      class="w-full font-mono text-sm bg-white border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value }}</textarea>
                        @else
                            <input type="{{ $setting->is_encrypted ? 'password' : 'text' }}"
                                   id="setting-{{ $loop->parent->index }}-{{ $loop->index }}"
                                   name="settings[{{ $setting->key }}]"
                                   value="{{ $value }}"
                                   placeholder="{{ $setting->is_encrypted ? __('core::settings.encrypted_placeholder') : '' }}"
                                   autocomplete="off"
                                   class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <span class="material-symbols-rounded">save</span>
                {{ __('core::settings.save') }}
            </button>
        </div>
    </form>

    {{-- Test mail form (separate to avoid nested forms inside the main settings form) --}}
    @if(isset($groups['mail']))
        <div x-show="tab === 'mail'" x-cloak class="mt-6 bg-white border border-gray-200 rounded-lg p-5">
            <form method="POST" action="{{ route('admin.settings.test-mail') }}" class="space-y-3">
                @csrf
                <label class="block text-sm font-semibold text-gray-700">
                    {{ __('core::settings.test_mail.button') }}
                </label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="email" name="email"
                           placeholder="{{ __('core::settings.test_mail.placeholder') }}"
                           class="flex-1 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-semibold">
                        <span class="material-symbols-rounded text-base">outgoing_mail</span>
                        {{ __('core::settings.test_mail.button') }}
                    </button>
                </div>
                <p class="text-xs text-gray-500">{{ __('core::settings.test_mail.placeholder') }}</p>
            </form>
        </div>
    @endif
</div>
@endsection
