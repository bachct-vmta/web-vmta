import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET() {
  try {
    const members: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM AllianceMember ORDER BY group_id ASC, id DESC;`);
    return NextResponse.json({ members });
  } catch (error) {
    console.error('Error fetching alliance members:', error);
    return NextResponse.json({ members: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { id, group_id, name_vi, name_en, badge, logo_url, address, phone, email, website, description_vi, description_en } = body;

    if (!group_id || !name_vi) {
      return NextResponse.json({ error: 'Missing group_id or name_vi' }, { status: 400 });
    }

    if (id) {
      await prisma.$executeRawUnsafe(
        `UPDATE AllianceMember SET group_id=?, name_vi=?, name_en=?, badge=?, logo_url=?, address=?, phone=?, email=?, website=?, description_vi=?, description_en=? WHERE id=?;`,
        group_id, name_vi, name_en || name_vi, badge || 'Chuẩn', logo_url || '', address || '', phone || '', email || '', website || '', description_vi || '', description_en || '', id
      );
    } else {
      await prisma.$executeRawUnsafe(
        `INSERT INTO AllianceMember (group_id, name_vi, name_en, badge, logo_url, address, phone, email, website, description_vi, description_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);`,
        group_id, name_vi, name_en || name_vi, badge || 'Chuẩn', logo_url || '', address || '', phone || '', email || '', website || '', description_vi || '', description_en || ''
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error saving alliance member:', error);
    return NextResponse.json({ error: 'Failed to save member' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM AllianceMember WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting alliance member:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
