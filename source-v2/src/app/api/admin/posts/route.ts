import { NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

export async function GET(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const slug = searchParams.get('slug');

    if (slug) {
      const posts: any[] = await prisma.$queryRawUnsafe(
        `SELECT * FROM Post WHERE (slug_vi = ? OR slug_en = ? OR slug = ?) LIMIT 1;`,
        slug, slug, slug
      );
      if (posts && posts.length > 0) {
        return NextResponse.json({ post: posts[0] });
      }
      return NextResponse.json({ error: 'Post not found' }, { status: 404 });
    }

    const posts: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM Post ORDER BY id DESC;`);
    return NextResponse.json({ posts });
  } catch (error) {
    console.error('Error fetching posts:', error);
    return NextResponse.json({ posts: [] });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const {
      id,
      slug_vi,
      slug_en,
      category,
      title_vi,
      title_en,
      summary_vi,
      summary_en,
      content_vi,
      content_en,
      image_url,
      author,
      status,
      meta_title_vi,
      meta_title_en,
      meta_description_vi,
      meta_description_en,
      meta_keywords_vi,
      meta_keywords_en,
    } = body;

    if (!title_vi) {
      return NextResponse.json({ error: 'Missing title_vi' }, { status: 400 });
    }

    const generateSlug = (text: string) =>
      text
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const finalSlugVi = slug_vi || generateSlug(title_vi);
    const finalSlugEn = slug_en || generateSlug(title_en || title_vi);
    const finalStatus = status || 'published';
    const isPub = finalStatus === 'published' ? 1 : 0;

    if (id) {
      await prisma.$executeRawUnsafe(
        `UPDATE Post SET slug=?, slug_vi=?, slug_en=?, category=?, title_vi=?, title_en=?, summary_vi=?, summary_en=?, content_vi=?, content_en=?, image_url=?, author=?, status=?, is_published=?, meta_title_vi=?, meta_title_en=?, meta_description_vi=?, meta_description_en=?, meta_keywords_vi=?, meta_keywords_en=? WHERE id=?;`,
        finalSlugVi, finalSlugVi, finalSlugEn, category || 'Sự kiện VMTA', title_vi, title_en || title_vi, summary_vi || '', summary_en || '', content_vi || '', content_en || '', image_url || '', author || 'Ban Biên Tập VMTA', finalStatus, isPub, meta_title_vi || title_vi, meta_title_en || title_en || title_vi, meta_description_vi || summary_vi || '', meta_description_en || summary_en || '', meta_keywords_vi || '', meta_keywords_en || '', id
      );
    } else {
      await prisma.$executeRawUnsafe(
        `INSERT INTO Post (slug, slug_vi, slug_en, category, title_vi, title_en, summary_vi, summary_en, content_vi, content_en, image_url, author, status, is_published, meta_title_vi, meta_title_en, meta_description_vi, meta_description_en, meta_keywords_vi, meta_keywords_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);`,
        finalSlugVi, finalSlugVi, finalSlugEn, category || 'Sự kiện VMTA', title_vi, title_en || title_vi, summary_vi || '', summary_en || '', content_vi || '', content_en || '', image_url || '', author || 'Ban Biên Tập VMTA', finalStatus, isPub, meta_title_vi || title_vi, meta_title_en || title_en || title_vi, meta_description_vi || summary_vi || '', meta_description_en || summary_en || '', meta_keywords_vi || '', meta_keywords_en || ''
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error saving post:', error);
    return NextResponse.json({ error: 'Failed to save post' }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: 'Missing id' }, { status: 400 });

    await prisma.$executeRawUnsafe(`DELETE FROM Post WHERE id = ?;`, id);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting post:', error);
    return NextResponse.json({ error: 'Failed to delete' }, { status: 500 });
  }
}
