@php
    /**
     * Bullets sub-repeater — Alpine variable cardinality 1-8 per item.
     * Required vars:
     *   - $locale: string  e.g. 'vi'
     *   - $itemIndex: int  parent item index
     *   - $bullets: array<string>  existing bullet values
     * Optional:
     *   - $min: int  minimum bullets (default 1)
     *   - $maxBullets: int  maximum bullets (default 8)
     */
    $min = $min ?? 1;
    $maxBullets = $maxBullets ?? 8;
    $bulletCount = max($min, count($bullets) ?: $min);
@endphp
<div class="mt-2 space-y-2" x-data="{ count: {{ $bulletCount }} }">
    <p class="text-xs font-medium text-slate-500 uppercase">{{ __('content::content.home.fields.item_bullets') }}</p>
    @for($b = 0; $b < $maxBullets; $b++)
        <div x-show="count > {{ $b }}" x-cloak class="flex items-center gap-2">
            <span class="text-xs text-slate-400 w-4">{{ $b + 1 }}.</span>
            <input type="text"
                   name="translations[{{ $locale }}][items][{{ $itemIndex }}][bullets][{{ $b }}]"
                   value="{{ old("translations.{$locale}.items.{$itemIndex}.bullets.{$b}", $bullets[$b] ?? '') }}"
                   class="flex-1 rounded border-slate-300 text-sm"
                   placeholder="Bullet {{ $b + 1 }}">
        </div>
    @endfor
    <div class="flex gap-2 pt-1">
        <button type="button"
                @click="if (count < {{ $maxBullets }}) count++"
                class="rounded bg-teal-600 px-2 py-0.5 text-white text-xs hover:bg-teal-700">
            + Thêm bullet
        </button>
        <button type="button"
                @click="if (count > {{ $min }}) count--"
                class="rounded bg-slate-500 px-2 py-0.5 text-white text-xs hover:bg-slate-600">
            - Xoá cuối
        </button>
    </div>
</div>
