import React from 'react';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { prisma } from '@/lib/prisma';

export default async function PostDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  const isVi = locale === 'vi';

  let post: any = null;
  let relatedPosts: any[] = [];

  try {
    const rawPosts: any[] = await prisma.$queryRawUnsafe(
      `SELECT * FROM Post WHERE (slug_vi = ? OR slug_en = ? OR slug = ?) LIMIT 1;`,
      slug, slug, slug
    );
    if (rawPosts && rawPosts.length > 0) {
      post = rawPosts[0];
    }
    relatedPosts = await prisma.$queryRawUnsafe(
      `SELECT * FROM Post WHERE (slug_vi != ? AND slug != ?) LIMIT 3;`,
      slug, slug
    );
  } catch (err) {
    console.error('Error loading post detail:', err);
  }

  if (!post) {
    return notFound();
  }

  const title = isVi ? (post.title_vi || post.title_en) : (post.title_en || post.title_vi);
  const summary = isVi ? (post.summary_vi || post.summary_en) : (post.summary_en || post.summary_vi);
  const content = isVi ? (post.content_vi || post.content_en) : (post.content_en || post.content_vi);

  return (
    <article className="bg-white font-sans space-y-0 pb-20">
      {/* Hero Header */}
      <section className="relative h-[320px] overflow-hidden bg-slate-900 text-white">
        {post.image_url && (
          <img
            src={post.image_url}
            alt=""
            className="absolute inset-0 h-full w-full object-cover opacity-30"
          />
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/60 to-transparent"></div>
        <div className="relative mx-auto flex h-full max-w-5xl flex-col justify-end px-4 py-8 z-10 space-y-3">
          <div className="flex items-center gap-2">
            <span className="text-[10px] font-bold uppercase px-3 py-1 bg-[#0b7f7c] text-white rounded-full">
              {post.category || 'Sự kiện VMTA'}
            </span>
            <span className="text-xs text-slate-300">
              📅 {String(post.created_at || '').substring(0, 10)} | ✍️ {post.author}
            </span>
          </div>
          <h1 className="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight text-white">
            {title}
          </h1>
          <nav className="text-xs text-teal-300 font-semibold" aria-label="breadcrumb">
            <Link href={`/${locale}`} className="hover:underline">
              {isVi ? 'Trang chủ' : 'Home'}
            </Link>
            <span className="mx-2">/</span>
            <Link href={`/${locale}/tin-tuc`} className="hover:underline">
              {isVi ? 'Tin tức' : 'News'}
            </Link>
            <span className="mx-2">/</span>
            <span className="text-slate-400 truncate max-w-xs">{title}</span>
          </nav>
        </div>
      </section>

      {/* Main Content Area */}
      <section className="max-w-4xl mx-auto px-4 pt-10 pb-16">
        <div className="p-6 bg-teal-50/70 border-l-4 border-[#0b7f7c] rounded-r-2xl mb-8 font-bold text-sm md:text-base text-slate-800 leading-relaxed italic">
          {summary}
        </div>

        {/* Render HTML Content */}
        <div
          className="prose prose-slate max-w-none text-sm md:text-base leading-relaxed text-slate-800 space-y-4"
          dangerouslySetInnerHTML={{ __html: content || `<p>${summary}</p>` }}
        />

        {/* Share & Author Footer */}
        <div className="mt-12 pt-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500 font-semibold">
          <div>
            <span>{isVi ? 'Tác giả:' : 'Author:'} </span>
            <strong className="text-[#0b7f7c]">{post.author}</strong>
          </div>
          <div className="flex items-center gap-2">
            <Link
              href={`/${locale}/tin-tuc`}
              className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition"
            >
              ← {isVi ? 'Quay lại tin tức' : 'Back to news'}
            </Link>
          </div>
        </div>

        {/* Related Posts */}
        {relatedPosts.length > 0 && (
          <div className="mt-16 pt-8 border-t border-slate-200 space-y-6">
            <h3 className="text-xl font-extrabold uppercase text-[#0b7f7c]">
              {isVi ? 'BÀI VIẾT LIÊN QUAN' : 'RELATED ARTICLES'}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {relatedPosts.map((rp) => {
                const rpTitle = isVi ? (rp.title_vi || rp.title_en) : (rp.title_en || rp.title_vi);
                const rpSlug = isVi ? (rp.slug_vi || rp.slug) : (rp.slug_en || rp.slug);
                return (
                  <article key={rp.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-2 shadow-xs">
                    {rp.image_url && (
                      <Link href={`/${locale}/tin-tuc/${rpSlug}`}>
                        <img src={rp.image_url} alt="" className="aspect-[16/10] w-full object-cover rounded-xl mb-2" />
                      </Link>
                    )}
                    <h4 className="font-bold text-xs text-[#0b7f7c] line-clamp-2">
                      <Link href={`/${locale}/tin-tuc/${rpSlug}`}>{rpTitle}</Link>
                    </h4>
                  </article>
                );
              })}
            </div>
          </div>
        )}
      </section>
    </article>
  );
}
