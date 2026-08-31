@php
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';

    // showSchedule=false thì ẩn thời điểm xuất bản và thứ tự; controller tự đặt published_at khi publish
    $showSchedule = $showSchedule ?? true;

    // Bản ghi mới mặc định lấy thời điểm hiện tại để bật published là hiện ngay
    $publishedValue = old('published_at', $model->published_at?->format('Y-m-d\TH:i'));
    if (empty($publishedValue) && ! $model->exists) {
        $publishedValue = now()->format('Y-m-d\TH:i');
    }
@endphp

<div>
    <label class="{{ $labelClass }}">{{ __('dental::dental.fields.status') }} <span class="text-red-500">*</span></label>
    <select name="status" class="{{ $inputClass }} @error('status') border-red-500 @enderror">
        @foreach(\Packages\Dental\Src\Enums\PublishStatus::options() as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $model->status) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

@if($showSchedule)
    <div>
        <label class="{{ $labelClass }}">{{ __('dental::dental.fields.published_at') }}</label>
        <input name="published_at" type="datetime-local" value="{{ $publishedValue }}"
               class="{{ $inputClass }} @error('published_at') border-red-500 @enderror">
        @error('published_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">{{ __('dental::dental.fields.sort_order') }}</label>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $model->sort_order ?? 0) }}"
               class="{{ $inputClass }} @error('sort_order') border-red-500 @enderror">
        @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
@endif
