import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET() {
  try {
    const categories: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM MedicalCategory ORDER BY type ASC, id ASC;`);
    const packages: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM MedicalPackage ORDER BY id DESC;`);

    return NextResponse.json({ categories, packages });
  } catch (error) {
    console.error('Error fetching medical packages:', error);
    return NextResponse.json({ categories: [], packages: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const {
      id,
      category_key,
      title_vi,
      title_en,
      subtitle_vi,
      subtitle_en,
      duration_vi,
      duration_en,
      price_vi,
      price_en,
      facility_name,
      image_url,
      itinerary_vi,
      itinerary_en,
    } = body;

    if (!category_key || !title_vi) {
      return NextResponse.json({ error: 'Missing category_key or title_vi' }, { status: 400 });
    }

    if (id) {
      await prisma.$executeRawUnsafe(
        `UPDATE MedicalPackage SET category_key=?, title_vi=?, title_en=?, subtitle_vi=?, subtitle_en=?, duration_vi=?, duration_en=?, price_vi=?, price_en=?, facility_name=?, image_url=?, itinerary_vi=?, itinerary_en=? WHERE id=?;`,
        category_key, title_vi, title_en || title_vi, subtitle_vi || '', subtitle_en || '', duration_vi || '', duration_en || '', price_vi || '', price_en || '', facility_name || '', image_url || '', itinerary_vi || '', itinerary_en || '', id
      );
    } else {
      await prisma.$executeRawUnsafe(
        `INSERT INTO MedicalPackage (category_key, title_vi, title_en, subtitle_vi, subtitle_en, duration_vi, duration_en, price_vi, price_en, facility_name, image_url, itinerary_vi, itinerary_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);`,
        category_key, title_vi, title_en || title_vi, subtitle_vi || '', subtitle_en || '', duration_vi || '', duration_en || '', price_vi || '', price_en || '', facility_name || '', image_url || '', itinerary_vi || '', itinerary_en || ''
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error saving medical package:', error);
    return NextResponse.json({ error: 'Failed to save package' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM MedicalPackage WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting package:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
