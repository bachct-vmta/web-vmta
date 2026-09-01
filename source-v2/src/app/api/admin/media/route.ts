import { NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

export async function GET() {
  try {
    const assets: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM MediaAsset ORDER BY id DESC;`);
    return NextResponse.json({ assets });
  } catch (error) {
    console.error('Error fetching media assets:', error);
    return NextResponse.json({ assets: [] });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM MediaAsset WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting media asset:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
