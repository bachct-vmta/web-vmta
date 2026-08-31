<div class="flex items-center gap-4 pt-2">
    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium">
        {{ $submitLabel }}
    </button>
    <a href="{{ $cancelRoute }}" class="text-gray-500 hover:text-gray-700">
        {{ __('dental::dental.actions.cancel') }}
    </a>
</div>
