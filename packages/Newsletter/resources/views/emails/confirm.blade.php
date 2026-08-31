<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subscriber->email }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <div style="max-width:520px;margin:30px auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;">
        <h2 style="color:#0b7f7c;">VMTA Newsletter</h2>
        <p>{{ __('newsletter::newsletter.confirm_intro', [], $subscriber->locale) }}</p>
        <p style="text-align:center;margin:24px 0;">
            <a href="{{ $confirmUrl }}"
               style="display:inline-block;background:#0b7f7c;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;">
                {{ __('newsletter::newsletter.confirm_button', [], $subscriber->locale) }}
            </a>
        </p>
        <p style="font-size:12px;color:#6b7280;">
            {{ $confirmUrl }}
        </p>
    </div>
</body>
</html>
