'use client';

import React from 'react';
import Link from 'next/link';

interface NewsTeaserSectionProps {
  locale: string;
}

export const NewsTeaserSection: React.FC<NewsTeaserSectionProps> = ({ locale }) => {
  const isVi = locale === 'vi';

  const posts = [
    {
      id: 1,
      title: isVi
        ? 'Ra Mắt Liên Minh Du Lịch Y Tế Việt Nam (VMTA): Nâng Tầm Chăm Sóc Sức Khỏe Cho Du Khách'
        : 'Launch of Vietnam Medical Tourism Alliance (VMTA)',
      date: '15/08/2026',
      image: '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg',
      author: 'Ban Biên Tập VMTA',
      excerpt: isVi
        ? 'Sự kiện quy tụ đại diện Bộ Y tế, các bệnh viện hàng đầu và doanh nghiệp lữ hành nhằm thúc đẩy chiến lược du lịch y tế Việt Nam.'
        : 'Gathering representatives from Ministry of Health, accredited hospitals, and travel corporations.',
    },
    {
      id: 2,
      title: isVi
        ? 'Việt Nam Trở Thành Điểm Đến Hàng Đầu Về Nha Khoa Thẩm Mỹ & Cấy Ghép Implant'
        : 'Vietnam Emerges as Top Destination for Dental Tourism',
      date: '10/08/2026',
      image: '/images/news/making-vietnam-medical-tourism.webp',
      author: 'Chuyên gia Y tế',
      excerpt: isVi
        ? 'Với chi phí tiết kiệm 60% và đội ngũ bác sĩ tay nghề cao, Việt Nam đón hàng trăm ngàn lượt khách quốc tế làm răng mỗi năm.'
        : 'Save 60% with international standard care, welcoming thousands of dental tourists every year.',
    },
    {
      id: 3,
      title: isVi
        ? 'Thứ Trưởng Bộ Y Tế Trần Văn Thuấn Đánh Giá Cao Mô Hình Kết Nối Y Tế - Du Lịch'
        : 'Deputy Minister of Health Praises Healthcare Tourism Model',
      date: '05/08/2026',
      image: '/images/news/thu-truong-bo-y-te-tran-van-thuan.png',
      author: 'Tin Tức VTV',
      excerpt: isVi
        ? 'Hệ sinh thái kết nối tạo điều kiện thuận lợi cho du khách trải nghiệm các kỹ thuật y khoa tiên tiến nhất.'
        : 'Creating optimal conditions for international tourists to access advanced medical techniques.',
    },
  ];

  return (
    <section className="py-16 md:py-24 bg-slate-50 border-t border-slate-200/60" data-home-section="news-teaser">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold uppercase text-[#0b7f7c]">
              {isVi ? 'TIN TỨC & NỔI BẬT' : 'LATEST MEDICAL NEWS'}
            </h2>
            <p className="mt-2 text-sm text-slate-600 font-semibold">
              {isVi ? 'Cập nhật tin tức y tế và sự kiện nổi bật của VMTA' : 'Latest medical updates and events'}
            </p>
          </div>
          <Link
            href={`/${locale}/tin-tuc`}
            className="mt-4 md:mt-0 text-[#0b7f7c] font-bold text-sm uppercase hover:underline"
          >
            {isVi ? 'Xem tất cả tin tức →' : 'View all news →'}
          </Link>
        </div>

        <div className="custom-post-grid">
          {posts.map((post) => (
            <div key={post.id} className="post-item shadow-md border border-slate-100 flex flex-col justify-between">
              <div>
                <div className="post-thumb">
                  <img src={post.image} alt={post.title} />
                </div>
                <div className="post-content px-4">
                  <div className="post-author">
                    <span className="font-bold text-xs">{post.author}</span>
                    <span>•</span>
                    <span className="text-xs text-slate-400">{post.date}</span>
                  </div>
                  <h3 className="post-title">
                    <Link href={`/${locale}/tin-tuc`}>{post.title}</Link>
                  </h3>
                  <p className="post-excerpt text-xs text-slate-600 leading-relaxed">{post.excerpt}</p>
                </div>
              </div>

              <div className="p-4 pt-0">
                <Link href={`/${locale}/tin-tuc`} className="text-xs font-bold uppercase text-[#0b7f7c] hover:underline">
                  {isVi ? 'Đọc tiếp →' : 'Read more →'}
                </Link>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
