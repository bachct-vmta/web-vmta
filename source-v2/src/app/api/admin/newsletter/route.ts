import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET() {
  try {
    const subscribers: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM NewsletterSubscriber ORDER BY id DESC;`);
    return NextResponse.json({ subscribers });
  } catch (error) {
    console.error('Error fetching subscribers:', error);
    return NextResponse.json({ subscribers: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { email } = body;

    if (!email) {
      return NextResponse.json({ error: 'Missing email' }, { status: 400 });
    }

    await prisma.$executeRawUnsafe(
      `INSERT OR IGNORE INTO NewsletterSubscriber (email) VALUES (?);`,
      email
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error adding subscriber:', error);
    return NextResponse.json({ error: 'Failed to subscribe' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM NewsletterSubscriber WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting subscriber:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
