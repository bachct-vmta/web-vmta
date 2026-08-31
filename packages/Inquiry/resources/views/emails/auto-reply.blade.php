@php
    /** @var \Packages\Inquiry\Src\Models\Inquiry $inquiry */
    $loc = $inquiry->locale;
    $greeting = __('inquiry::inquiry.auto_reply_greeting', ['name' => $inquiry->name], $loc);
    $body = __('inquiry::inquiry.auto_reply_body', [], $loc);
    $signature = __('inquiry::inquiry.auto_reply_signature', [], $loc);
@endphp
<!DOCTYPE html>
<html lang="{{ $inquiry->locale }}">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto;">
    <p>{{ $greeting }}</p>

    <p>{{ $body }}</p>

    @if($inquiry->message)
        <blockquote style="border-left: 3px solid #d1d5db; padding-left: 12px; color: #4b5563;">
            {{ $inquiry->message }}
        </blockquote>
    @endif

    <p style="margin-top: 24px;">{!! nl2br(e($signature)) !!}</p>

    <p style="color: #9ca3af; font-size: 12px;">Ref: #{{ $inquiry->id }}</p>
</body>
</html>
