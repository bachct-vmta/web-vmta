import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/db';

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { name, phone, email, service, message, honeypot } = body;

    // Honeypot anti-spam check
    if (honeypot) {
      return NextResponse.json({ success: true, message: 'Form submitted successfully' });
    }

    if (!name || !phone) {
      return NextResponse.json({ error: 'Họ tên và số điện thoại là bắt buộc' }, { status: 400 });
    }

    const inquiry = await prisma.inquiry.create({
      data: {
        name,
        phone,
        email: email || null,
        service: service || null,
        message: message || null,
        status: 'pending',
      },
    });

    return NextResponse.json({
      success: true,
      message: 'Gửi yêu cầu tư vấn thành công! VMTA sẽ liên hệ lại với bạn trong thời gian sớm nhất.',
      data: inquiry,
    });
  } catch (error: unknown) {
    const message = error instanceof Error ? error.message : 'Lỗi hệ thống khi gửi yêu cầu';
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
