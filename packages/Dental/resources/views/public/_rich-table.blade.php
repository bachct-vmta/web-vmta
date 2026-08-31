{{--
    Render bảng do admin soạn bằng CKEditor — Figma 39:36.

    Biến: $html (HTML thô từ CSDL), $variant 'price'|'comparison'

    BẢO MẬT: HTML nhập từ CKEditor nên bắt buộc đi qua profile Purifier `post_body`
    — đây là profile duy nhất giữ thẻ bảng, profile mặc định xoá sạch <table>.
--}}
@php
    $html = trim((string) ($html ?? ''));
    $variant = $variant ?? 'price';
@endphp

@if($html !== '')
    {{-- overflow-x-auto để bảng 5 cột không kéo ngang cả trang trên điện thoại --}}
    <div @class([
        'vmta-rich-table mx-auto w-full overflow-x-auto' => true,
        'vmta-rich-table--price' => $variant === 'price',
        'vmta-rich-table--comparison' => $variant === 'comparison',
    ])>
        {!! clean($html, 'post_body') !!}
    </div>
@endif
