import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export interface SectionContent {
  title?: string;
  subtitle?: string;
  body?: string;
  cta_label?: string;
  cta_url?: string;
  video_url?: string;
  image_url?: string;
  image_url_vi?: string;
  image_url_en?: string;
  items?: any[];
  bullets?: string[];
  extra_json?: any;
}

export async function getOrderedPageSections(pageSlug: string): Promise<string[]> {
  try {
    const rawSections: any[] = await prisma.$queryRawUnsafe(`
      SELECT section_key FROM CmsSection
      WHERE page_slug = '${pageSlug}' AND is_active = 1
      ORDER BY "order" ASC, id ASC
    `);
    if (rawSections && rawSections.length > 0) {
      return rawSections.map((s) => s.section_key);
    }
  } catch (error) {
    console.error(`Error fetching ordered sections for page ${pageSlug}:`, error);
  }
  return [];
}

export async function getSectionContent(
  pageSlug: string,
  sectionKey: string,
  locale: string,
  fallback: SectionContent = {}
): Promise<SectionContent> {
  try {
    const rawTranslations: any[] = await prisma.$queryRawUnsafe(`
      SELECT 
        t.locale,
        t.title,
        t.subtitle,
        t.body,
        t.extra_json
      FROM CmsSection s
      JOIN CmsSectionTranslation t ON s.id = t.section_id
      WHERE s.page_slug = '${pageSlug}' AND s.section_key = '${sectionKey}'
    `);

    if (rawTranslations && rawTranslations.length > 0) {
      const tr = rawTranslations.find((t: any) => t.locale === locale) || rawTranslations[0];
      const viTr = rawTranslations.find((t: any) => t.locale === 'vi') || {};

      let parsedExtra: any = fallback.extra_json || {};
      let parsedViExtra: any = {};

      if (tr.extra_json) {
        try { parsedExtra = JSON.parse(tr.extra_json); } catch {}
      }
      if (viTr.extra_json) {
        try { parsedViExtra = JSON.parse(viTr.extra_json); } catch {}
      }

      // Locale-specific image URL fallback logic
      let imageUrl = parsedExtra.image_url || parsedExtra[`image_url_${locale}`] || parsedViExtra.image_url_vi || parsedViExtra.image_url || fallback.image_url || '';
      let videoUrl = parsedExtra.video_url || parsedViExtra.video_url || fallback.video_url || '';
      let ctaLabel = parsedExtra.cta_label || fallback.cta_label || '';
      let ctaUrl = parsedExtra.cta_url || fallback.cta_url || '';
      let items = parsedExtra.items !== undefined ? parsedExtra.items : fallback.items;
      let bullets = parsedExtra.bullets !== undefined ? parsedExtra.bullets : fallback.bullets;

      return {
        title: tr.title !== null && tr.title !== undefined ? tr.title : (fallback.title || ''),
        subtitle: tr.subtitle !== null && tr.subtitle !== undefined ? tr.subtitle : (fallback.subtitle || ''),
        body: tr.body !== null && tr.body !== undefined ? tr.body : (fallback.body || ''),
        cta_label: ctaLabel,
        cta_url: ctaUrl,
        video_url: videoUrl,
        image_url: imageUrl,
        image_url_vi: parsedViExtra.image_url_vi || parsedViExtra.image_url || imageUrl,
        image_url_en: parsedExtra.image_url_en || imageUrl,
        items,
        bullets,
        extra_json: parsedExtra,
      };
    }
  } catch (error) {
    console.error(`Error loading section ${pageSlug}/${sectionKey}:`, error);
  }

  return {
    title: fallback.title || '',
    subtitle: fallback.subtitle || '',
    body: fallback.body || '',
    cta_label: fallback.cta_label || '',
    cta_url: fallback.cta_url || '',
    video_url: fallback.video_url || '',
    image_url: fallback.image_url || '',
    image_url_vi: fallback.image_url_vi || fallback.image_url || '',
    image_url_en: fallback.image_url_en || fallback.image_url || '',
    items: fallback.items,
    bullets: fallback.bullets,
    extra_json: fallback.extra_json,
  };
}
