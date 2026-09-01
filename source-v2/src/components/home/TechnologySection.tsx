'use client';

import React from 'react';

interface TechnologySectionProps {
  locale: string;
}

export const TechnologySection: React.FC<TechnologySectionProps> = ({ locale }) => {
  const isVi = locale === 'vi';

  const techGroups = isVi
    ? [
        {
          title: 'Trung tâm Điều phối Vận hành – Bộ não Của liên minh',
          bullets: [
            'Hệ thống tiếp nhận hồ sơ thông minh',
            'Hệ thống điều phối lịch trình theo thời gian thực',
          ],
        },
        {
          title: 'Quản trị sự phục hồi dựa trên dữ liệu',
          bullets: [
            'Hệ thống quản lý dữ liệu hậu điều trị',
            'Hệ thống phân tích và cá nhân hóa chăm sóc sức khỏe',
          ],
        },
      ]
    : [
        {
          title: 'Operations Hub — the brain of the alliance',
          bullets: [
            'Intelligent intake-record system',
            'Real-time itinerary coordination',
          ],
        },
        {
          title: 'Data-driven recovery management',
          bullets: [
            'Post-treatment data management',
            'Healthcare analytics and personalisation',
          ],
        },
      ];

  return (
    <section className="relative overflow-hidden py-24 md:py-32" data-home-section="technology">
      {/* Photo banner */}
      <div className="absolute inset-0">
        <img
          src="/images/home/technology/bg.jpg"
          className="w-full h-full object-cover"
          alt=""
          aria-hidden="true"
        />
        <div className="absolute inset-0 bg-white/80 md:bg-white/70"></div>
      </div>

      <div className="relative max-w-7xl mx-auto px-4 z-10">
        <h2 className="text-3xl md:text-5xl font-bold uppercase text-[#0b7f7c] mb-8 max-w-3xl leading-tight">
          {isVi ? 'CÔNG NGHỆ & SỰ KHÁC BIỆT' : 'TECHNOLOGY & DIFFERENTIATION'}
        </h2>

        <div className="space-y-8 max-w-3xl">
          {techGroups.map((group, idx) => (
            <div key={idx} className="bg-white/90 p-6 rounded-2xl border border-teal-100 shadow-md">
              <h3 className="text-lg font-bold text-[#d31e45] uppercase mb-3">{group.title}</h3>
              <ul className="space-y-2">
                {group.bullets.map((bullet, bIdx) => (
                  <li key={bIdx} className="flex items-start gap-3">
                    <span className="mt-2 h-2 w-2 rounded-full bg-[#0b7f7c] shrink-0"></span>
                    <span className="text-sm font-semibold text-slate-800">{bullet}</span>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
