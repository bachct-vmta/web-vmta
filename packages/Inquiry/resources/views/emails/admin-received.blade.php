@php
    /** @var \Packages\Inquiry\Src\Models\Inquiry $inquiry */
    $priorityLabel = __('inquiry::inquiry.priority.' . $inquiry->priority->value);
    $sourceLabel = __('inquiry::inquiry.source.' . $inquiry->source->value);
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ __('inquiry::inquiry.admin_received_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #1f2937;">
    <h2>{{ __('inquiry::inquiry.admin_received_heading') }}</h2>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse;">
        <tr><td><strong>{{ __('inquiry::inquiry.field_name') }}</strong></td><td>{{ $inquiry->name }}</td></tr>
        <tr><td><strong>{{ __('inquiry::inquiry.field_phone') }}</strong></td><td>{{ $inquiry->phone }}</td></tr>
        @if($inquiry->email)
            <tr><td><strong>{{ __('inquiry::inquiry.field_email') }}</strong></td><td>{{ $inquiry->email }}</td></tr>
        @endif
        @if($inquiry->industry)
            <tr><td><strong>{{ __('inquiry::inquiry.field_industry') }}</strong></td><td>{{ $inquiry->industry }}</td></tr>
        @endif
        @if($inquiry->company_name)
            <tr><td><strong>{{ __('inquiry::inquiry.field_company_name') }}</strong></td><td>{{ $inquiry->company_name }}</td></tr>
        @endif
        <tr><td><strong>{{ __('inquiry::inquiry.field_priority') }}</strong></td><td>{{ $priorityLabel }}</td></tr>
        <tr><td><strong>{{ __('inquiry::inquiry.field_source') }}</strong></td><td>{{ $sourceLabel }}</td></tr>
        <tr><td><strong>{{ __('inquiry::inquiry.field_locale') }}</strong></td><td>{{ $inquiry->locale }}</td></tr>
        @if($inquiry->source_ref_type)
            <tr><td><strong>{{ __('inquiry::inquiry.field_ref') }}</strong></td><td>{{ $inquiry->source_ref_type }}#{{ $inquiry->source_ref_id }}</td></tr>
        @endif
    </table>

    @if($inquiry->message)
        <h3>{{ __('inquiry::inquiry.field_message') }}</h3>
        <p>{{ $inquiry->message }}</p>
    @endif

    <hr>
    <p style="color: #6b7280; font-size: 12px;">
        Inquiry #{{ $inquiry->id }} · {{ $inquiry->created_at?->format('Y-m-d H:i') }}
    </p>
</body>
</html>
