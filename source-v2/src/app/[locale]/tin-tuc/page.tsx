import React from 'react';
import Link from 'next/link';
import { prisma } from '@/lib/prisma';

export default async function NewsPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  let posts: any[] = [];
  try {
    posts = await prisma.$queryRawUnsafe(`SELECT * FROM Post WHERE is_published = 1 ORDER BY id DESC;`);
  } catch (err) {
    console.error('Error fetching posts for web:', err);
  }

  const featured = posts[0] || {
    title_vi: 'Thứ trưởng Bộ Y tế trao quyết định thành lập Liên Minh Du Lịch Y Tế Việt Nam (VMTA)',
    title_en: 'Deputy Minister of Health presents decision establishing VMTA',
    summary_vi: 'Sự kiện đánh dấu bước ngoặt lớn đưa Du lịch Y tế Việt Nam hội nhập chuẩn mực SLA toàn cầu.',
    summary_en: 'Event marks major milestone establishing SLA-grade Vietnam Medical Tourism.',
    image_url: '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg',
    slug_vi: 'ra-mat-he-sinh-thai-du-lich-y-te',
    slug_en: 'ra-mat-he-sinh-thai-du-lich-y-te',
  };

  const featuredSlug = isVi ? (featured.slug_vi || featured.slug) : (featured.slug_en || featured.slug || featured.slug_vi);

  return (
    <div className="bg-white font-sans space-y-0 pb-16">
      {/* 1. Hero */}
      <section className="relative h-[280px] overflow-hidden bg-white">
        <img
          src="/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png"
          alt=""
          className="absolute inset-0 h-full w-full object-cover opacity-20"
        />
        <div className="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 py-8 z-10">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold uppercase text-[#0b7f7c]">
            {isVi ? 'TIN TỨC & BÁO CHÍ' : 'NEWS & PRESS RELEASES'}
          </h1>
          <nav className="mt-2 text-xs text-[#0b7f7c] font-semibold" aria-label="breadcrumb">
            <Link href={`/${locale}`} className="hover:underline">
              {isVi ? 'Trang chủ' : 'Home'}
            </Link>
            <span className="mx-2">/</span>
            <span>{isVi ? 'Tin tức' : 'News'}</span>
          </nav>
        </div>
      </section>

      {/* 2. Main News Layout */}
      <section className="bg-white pt-8 pb-16">
        <div className="mx-auto max-w-7xl px-4">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {/* Left Featured Block */}
            <div className="lg:col-span-8">
              <article className="relative overflow-hidden bg-[#d4eceb] rounded-3xl group shadow-md">
                <Link href={`/${locale}/tin-tuc/${featuredSlug}`}>
                  <img
                    src={featured.image_url || '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg'}
                    alt=""
                    className="aspect-[16/10] w-full object-cover transition group-hover:scale-105 duration-300"
                  />
                  <div className="absolute inset-0 flex flex-col justify-end bg-gradient-to-b from-[#0775720d] via-[#0775728a] to-[#077572f2] p-6">
                    <span className="text-[10px] font-bold uppercase text-teal-200 bg-teal-900/60 px-2.5 py-1 rounded w-fit mb-2">
                      {featured.category || 'Sự kiện VMTA'}
                    </span>
                    <h2 className="text-xl md:text-2xl font-extrabold leading-snug text-white">
                      {isVi ? featured.title_vi : featured.title_en}
                    </h2>
                  </div>
                </Link>
              </article>

              <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {posts.slice(1, 3).map((post) => {
                  const postSlug = isVi ? (post.slug_vi || post.slug) : (post.slug_en || post.slug || post.slug_vi);
                  return (
                    <article key={post.id} className="rounded-2xl overflow-hidden bg-slate-50 border border-slate-200 shadow-sm">
                      {post.image_url && (
                        <Link href={`/${locale}/tin-tuc/${postSlug}`} className="block overflow-hidden">
                          <img src={post.image_url} alt="" className="aspect-[16/10] w-full object-cover" />
                        </Link>
                      )}
                      <h3 className="bg-[#69ccc7] p-4 text-xs font-extrabold leading-snug">
                        <Link href={`/${locale}/tin-tuc/${postSlug}`} className="line-clamp-2 text-[#064744]">
                          {isVi ? post.title_vi : post.title_en}
                        </Link>
                      </h3>
                    </article>
                  );
                })}
              </div>
            </div>

            {/* Right Sidebar: Latest News */}
            <aside className="lg:col-span-4 flex flex-col pt-1">
              <h2 className="mb-4 text-xl font-extrabold uppercase text-[#0b7f7c]">
                {isVi ? 'TIN MỚI NHẤT' : 'LATEST ARTICLES'}
              </h2>
              <ul className="space-y-2">
                {posts.map((post, i) => {
                  const postSlug = isVi ? (post.slug_vi || post.slug) : (post.slug_en || post.slug || post.slug_vi);
                  return (
                    <li
                      key={post.id}
                      className={`${
                        i === 0 ? 'bg-[#0b7f7c] text-white' : 'bg-slate-50 text-[#0b7f7c] border border-slate-200'
                      } p-4 rounded-xl text-xs md:text-sm font-extrabold leading-snug hover:opacity-90 transition`}
                    >
                      <Link href={`/${locale}/tin-tuc/${postSlug}`}>{isVi ? post.title_vi : post.title_en}</Link>
                    </li>
                  );
                })}
              </ul>
            </aside>
          </div>

          {/* All Posts Grid */}
          <section className="mt-16 pt-8 border-t border-slate-200">
            <h2 className="mb-6 text-2xl md:text-3xl font-extrabold uppercase text-[#0b7f7c]">
              {isVi ? 'TẤT CẢ BÀI VIẾT & SỰ KIỆN' : 'ALL POSTS & PRESS RELEASES'}
            </h2>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {posts.map((post) => {
                const postSlug = isVi ? (post.slug_vi || post.slug) : (post.slug_en || post.slug || post.slug_vi);
                return (
                  <article key={post.id} className="bg-slate-50/60 p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                    {post.image_url && (
                      <Link href={`/${locale}/tin-tuc/${postSlug}`} className="block overflow-hidden rounded-2xl">
                        <img src={post.image_url} alt="" className="aspect-[16/10] w-full object-cover hover:scale-105 transition duration-300" />
                      </Link>
                    )}
                    <div className="space-y-2">
                      <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                        {post.category}
                      </span>
                      <h3 className="text-base font-extrabold text-[#0b7f7c] leading-snug">
                        <Link href={`/${locale}/tin-tuc/${postSlug}`}>{isVi ? post.title_vi : post.title_en}</Link>
                      </h3>
                      <p className="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {isVi ? post.summary_vi : post.summary_en}
                      </p>
                    </div>
                    <div className="pt-2 flex items-center justify-between text-xs text-[#0b7f7c] font-semibold border-t border-slate-200">
                      <Link href={`/${locale}/tin-tuc/${postSlug}`} className="text-[#0b7f7c] hover:underline font-bold">
                        {isVi ? 'Xem chi tiết >' : 'Read more >'}
                      </Link>
                      <span className="text-[11px] text-slate-400">📅 {String(post.created_at || '').substring(0, 10)}</span>
                    </div>
                  </article>
                );
              })}
            </div>
          </section>
        </div>
      </section>
    </div>
  );
}
