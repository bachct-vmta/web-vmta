import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET() {
  try {
    const inquiries: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM Inquiry ORDER BY id DESC;`);
    return NextResponse.json({ inquiries });
  } catch (error) {
    console.error('Error fetching inquiries:', error);
    return NextResponse.json({ inquiries: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { full_name, phone, email, organization, content } = body;

    if (!full_name || !email) {
      return NextResponse.json({ error: 'Missing full_name or email' }, { status: 400 });
    }

    await prisma.$executeRawUnsafe(
      `INSERT INTO Inquiry (full_name, phone, email, organization, content) VALUES (?, ?, ?, ?, ?);`,
      full_name, phone || '', email, organization || '', content || ''
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error creating inquiry:', error);
    return NextResponse.json({ error: 'Failed to create inquiry' }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const body = await req.json();
    const { id, status } = body;

    if (!id || !status) {
      return NextResponse.json({ error: 'Missing id or status' }, { status: 400 });
    }

    await prisma.$executeRawUnsafe(`UPDATE Inquiry SET status = ? WHERE id = ?;`, status, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating inquiry:', error);
    return NextResponse.json({ error: 'Failed to update' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM Inquiry WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting inquiry:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
