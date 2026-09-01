'use client';

import React from 'react';

interface WhyVNSectionProps {
  locale: string;
}

export const WhyVNSection: React.FC<WhyVNSectionProps> = ({ locale }) => {
  const isVi = locale === 'vi';

  const items = isVi
    ? [
        {
          icon: '/images/home/why-vn/icon-1.png',
          title: 'Tiềm năng bản địa',
          body: 'Việt Nam mạnh về IVF, Nha khoa và Phẫu thuật thẩm mỹ với chi phí cạnh tranh.',
        },
        {
          icon: '/images/home/why-vn/icon-2.png',
          title: 'Hạ tầng',
          body: 'Hệ thống resort wellness sẵn sàng là "trạm phục hồi" tốt nhất thế giới.',
        },
        {
          icon: '/images/home/why-vn/icon-3.png',
          title: 'Vai trò VMTA',
          body: 'Thực thể uy tín đứng ra thẩm định, kết nối và cam kết chất lượng cho cả hành trình phức tạp.',
        },
      ]
    : [
        {
          icon: '/images/home/why-vn/icon-1.png',
          title: 'Local Strengths',
          body: 'Vietnam has strong capabilities in IVF, dentistry, and aesthetic surgery, with competitive costs.',
        },
        {
          icon: '/images/home/why-vn/icon-2.png',
          title: 'Infrastructure',
          body: 'A wellness resort system ready to serve as one of the world’s best recovery hubs.',
        },
        {
          icon: '/images/home/why-vn/icon-3.png',
          title: 'VMTA’s Role',
          body: 'A trusted entity that evaluates, connects, and ensures quality across the entire complex journey.',
        },
      ];

  return (
    <section className="vmta-bg-filter-15 relative py-16 md:py-24 bg-white overflow-hidden" data-home-section="why_vn">
      {/* Background texture */}
      <div className="absolute inset-0 opacity-10">
        <img
          src="/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png"
          className="w-full h-full object-cover scale-125"
          alt=""
          aria-hidden="true"
        />
      </div>

      <div className="relative max-w-7xl mx-auto px-4">
        <h2 className="text-3xl md:text-4xl font-bold uppercase text-center text-[#0b7f7c]">
          {isVi ? 'TẠI SAO CHỌN VIỆT NAM & VMTA?' : 'WHY VIETNAM & WHY VMTA'}
        </h2>

        <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
          {items.map((item, i) => (
            <div key={i} className="text-center px-4">
              <img
                src={item.icon}
                alt={item.title}
                className="mx-auto h-20 w-20 object-contain mb-4"
              />
              <h3 className="text-lg font-bold uppercase text-[#d31e45] text-center mb-3">
                {item.title}
              </h3>
              <p className="text-sm text-[#4a4a4a] leading-relaxed text-justify">
                {item.body}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
