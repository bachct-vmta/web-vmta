<button
    class="nkd-btn {{ $class }}"
    type="{{ $type }}"
@if(! empty($attrs) && count($attrs) > 0)
    @foreach($attrs as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
@endif
>
@if(isset($icon))
    {!! $icon !!}
@endif
{{ $text }}
</button>

