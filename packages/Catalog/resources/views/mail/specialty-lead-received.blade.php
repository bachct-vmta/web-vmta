@php
    $specialty = $lead->specialty;
    $tr = $specialty?->translate($lead->locale ?: 'vi') ?? $specialty?->translations->first();
@endphp
<!DOCTYPE html>
<html lang="vi">
<body style="font-family: Arial, sans-serif; color: #111;">
    <h2>Lead mới — {{ $tr?->name ?? 'Chuyên khoa #'.$lead->specialty_id }}</h2>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse;">
        <tr><td><strong>Họ và tên:</strong></td><td>{{ $lead->name }}</td></tr>
        <tr><td><strong>Điện thoại:</strong></td><td>{{ $lead->phone }}</td></tr>
        <tr><td><strong>Email:</strong></td><td>{{ $lead->email ?: '—' }}</td></tr>
        <tr><td><strong>Nhu cầu:</strong></td><td>{{ $lead->demand ?: '—' }}</td></tr>
        <tr><td><strong>Lời nhắn:</strong></td><td>{{ $lead->message ?: '—' }}</td></tr>
        <tr><td><strong>Locale:</strong></td><td>{{ $lead->locale }}</td></tr>
        <tr><td><strong>IP:</strong></td><td>{{ $lead->ip_address ?: '—' }}</td></tr>
        <tr><td><strong>Tạo lúc:</strong></td><td>{{ optional($lead->created_at)->format('Y-m-d H:i') }}</td></tr>
    </table>

    @if(\Illuminate\Support\Facades\Route::has('admin.specialty_leads.show'))
        <p style="margin-top:16px">
            <a href="{{ route('admin.specialty_leads.show', $lead->id) }}"
               style="background:#0d9488;color:#fff;padding:10px 16px;text-decoration:none;border-radius:6px;">
                Xem trong Admin
            </a>
        </p>
    @endif
</body>
</html>
