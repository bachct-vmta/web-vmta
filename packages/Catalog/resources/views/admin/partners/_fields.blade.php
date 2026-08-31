@php
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';
@endphp

{{-- Card 1: General --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('catalog::catalog.partner_sections.general') }}</h2>

    <div class="space-y-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.type') }} <span class="text-red-500">*</span></label>
            <select name="type" class="{{ $inputClass }} @error('type') border-red-500 @enderror">
                @foreach($partnerTypes as $t)
                    <option value="{{ $t }}" @selected(old('type', $partner->type) === $t)>{{ __('catalog::catalog.types.'.$t) }}</option>
                @endforeach
            </select>
            @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-core::media-picker
                name="logo_media_id"
                :value="$partner->logo_media_id"
                :preview-url="$partner->logoMedia?->permalink"
                :label="__('catalog::catalog.fields.logo')"
                store="id"
            />
            @error('logo_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $partner->is_active))
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">{{ __('catalog::catalog.fields.is_active') }}</span>
            </label>
        </div>
    </div>
</div>

{{-- Card 2: Contact --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('catalog::catalog.partner_sections.contact') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.website') }}</label>
            <input name="website" type="url" maxlength="255"
                   value="{{ old('website', $partner->website) }}"
                   class="{{ $inputClass }} @error('website') border-red-500 @enderror">
            @error('website')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.phone') }}</label>
            <input name="phone" maxlength="50"
                   value="{{ old('phone', $partner->phone) }}"
                   class="{{ $inputClass }} @error('phone') border-red-500 @enderror">
            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.email') }}</label>
            <input name="email" type="email" maxlength="255"
                   value="{{ old('email', $partner->email) }}"
                   class="{{ $inputClass }} @error('email') border-red-500 @enderror">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Card 3: Content (single locale 'vi') --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('catalog::catalog.partner_sections.content') }}</h2>

    <input type="hidden" name="translations[0][locale]" value="vi">

    <div class="space-y-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.name') }} <span class="text-red-500">*</span></label>
            <input name="translations[0][name]" required maxlength="255"
                   value="{{ old('translations.0.name', $tr?->name) }}"
                   class="{{ $inputClass }} @error('translations.0.name') border-red-500 @enderror">
            @error('translations.0.name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-core::ckeditor name="translations[0][description]"
                              :value="old('translations.0.description', $tr?->description ?? '')"
                              :label="__('catalog::catalog.fields.description')"
                              rows="10" />
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.fields.address') }}</label>
            <input name="translations[0][address]" maxlength="255"
                   value="{{ old('translations.0.address', $tr?->address) }}"
                   class="{{ $inputClass }} @error('translations.0.address') border-red-500 @enderror">
            @error('translations.0.address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
