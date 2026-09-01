import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const page_slug = searchParams.get('page_slug') || 'home';

    const rawSections: any[] = await prisma.$queryRawUnsafe(`
      SELECT 
        s.id as section_id,
        s.page_slug,
        s.section_key,
        s."order" as section_order,
        s.is_active,
        t.locale,
        t.title,
        t.subtitle,
        t.body,
        t.extra_json
      FROM CmsSection s
      LEFT JOIN CmsSectionTranslation t ON s.id = t.section_id
      WHERE s.page_slug = '${page_slug}'
      ORDER BY s."order" ASC, s.id ASC
    `);

    // Group translations by section
    const sectionsMap = new Map<string, any>();

    (rawSections || []).forEach((row) => {
      if (!sectionsMap.has(row.section_key)) {
        sectionsMap.set(row.section_key, {
          id: row.section_id,
          page_slug: row.page_slug,
          section_key: row.section_key,
          order: row.section_order || 0,
          is_active: row.is_active ?? true,
          translations: [],
        });
      }
      if (row.locale) {
        sectionsMap.get(row.section_key).translations.push({
          locale: row.locale,
          title: row.title || '',
          subtitle: row.subtitle || '',
          body: row.body || '',
          extra_json: row.extra_json,
        });
      }
    });

    return NextResponse.json({ sections: Array.from(sectionsMap.values()) });
  } catch (error) {
    console.error('Error fetching sections:', error);
    return NextResponse.json({ sections: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { page_slug, section_key, order, vi, en } = body;

    if (!page_slug || !section_key) {
      return NextResponse.json({ error: 'Missing page_slug or section_key' }, { status: 400 });
    }

    const setOrder = order || 1;

    // Insert section if not exists
    await prisma.$executeRawUnsafe(
      `INSERT OR IGNORE INTO CmsSection (page_slug, section_key, "order") VALUES (?, ?, ?);`,
      page_slug,
      section_key,
      setOrder
    );

    const rows: any = await prisma.$queryRawUnsafe(
      `SELECT id FROM CmsSection WHERE page_slug = ? AND section_key = ? LIMIT 1;`,
      page_slug,
      section_key
    );

    if (rows && rows.length > 0) {
      const sectionId = rows[0].id;

      // Update order if provided
      if (order !== undefined) {
        await prisma.$executeRawUnsafe(
          `UPDATE CmsSection SET "order" = ? WHERE id = ?;`,
          order,
          sectionId
        );
      }

      // Upsert VI
      if (vi) {
        const extraVi = vi.extra_json ? JSON.stringify(vi.extra_json) : null;
        await prisma.$executeRawUnsafe(
          `INSERT INTO CmsSectionTranslation (section_id, locale, title, subtitle, body, extra_json)
           VALUES (?, 'vi', ?, ?, ?, ?)
           ON CONFLICT(section_id, locale) DO UPDATE SET title=?, subtitle=?, body=?, extra_json=?;`,
          sectionId, vi.title || '', vi.subtitle || '', vi.body || '', extraVi,
          vi.title || '', vi.subtitle || '', vi.body || '', extraVi
        );
      }

      // Upsert EN
      if (en) {
        const extraEn = en.extra_json ? JSON.stringify(en.extra_json) : null;
        await prisma.$executeRawUnsafe(
          `INSERT INTO CmsSectionTranslation (section_id, locale, title, subtitle, body, extra_json)
           VALUES (?, 'en', ?, ?, ?, ?)
           ON CONFLICT(section_id, locale) DO UPDATE SET title=?, subtitle=?, body=?, extra_json=?;`,
          sectionId, en.title || '', en.subtitle || '', en.body || '', extraEn,
          en.title || '', en.subtitle || '', en.body || '', extraEn
        );
      }
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error saving section:', error);
    return NextResponse.json({ error: 'Failed to save section' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const page_slug = searchParams.get('page_slug');
    const section_key = searchParams.get('section_key');

    if (!page_slug || !section_key) {
      return NextResponse.json({ error: 'Missing page_slug or section_key' }, { status: 400 });
    }

    await prisma.$executeRawUnsafe(
      `DELETE FROM CmsSection WHERE page_slug = ? AND section_key = ?;`,
      page_slug,
      section_key
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting section:', error);
    return NextResponse.json({ error: 'Failed to delete section' }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const body = await req.json();
    const { page_slug, ordered_keys } = body;

    if (!page_slug || !Array.isArray(ordered_keys)) {
      return NextResponse.json({ error: 'Invalid parameters' }, { status: 400 });
    }

    for (let idx = 0; idx < ordered_keys.length; idx++) {
      const key = ordered_keys[idx];
      await prisma.$executeRawUnsafe(
        `UPDATE CmsSection SET "order" = ? WHERE page_slug = ? AND section_key = ?;`,
        idx + 1,
        page_slug,
        key
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error reordering sections:', error);
    return NextResponse.json({ error: 'Failed to reorder sections' }, { status: 500 });
  }
}
