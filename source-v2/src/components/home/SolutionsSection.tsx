import React from 'react';
import { getSectionContent } from '@/lib/cms';

interface SolutionsSectionProps {
  locale: string;
}

export const SolutionsSection: React.FC<SolutionsSectionProps> = async ({ locale }) => {
  const isVi = locale === 'vi';

  const defaultTitle = isVi ? 'GIẢI PHÁP ĐIỀU PHỐI DÀNH CHO CÁC ĐƠN VỊ' : 'COORDINATION SOLUTIONS FOR PARTNERS';
  const defaultSubtitle = isVi
    ? 'KẾT NỐI SỨC MẠNH HỆ SINH THÁI GIỮA Y TẾ - NGHỈ DƯỠNG - DU LỊCH'
    : 'INTELLIGENT CONNECTING SYSTEM FOR HEALTHCARE & TOURISM';

  const defaultItems = isVi
    ? [
        {
          icon: '/images/home/solutions/icon-1.png',
          title: 'Đối với Bệnh viện & Cơ sở Y tế',
          body: 'Mở rộng tệp khách hàng quốc tế và nội địa cao cấp, tối ưu hóa quy trình tiếp nhận – hỗ trợ người bệnh theo chuẩn SLA, nâng cao vị thế thương hiệu.',
        },
        {
          icon: '/images/home/solutions/icon-2.png',
          title: 'Đối với Khách sạn & Resort',
          body: 'Phát triển dòng sản phẩm Du lịch Y tế / Wellness cao cấp, gia tăng tỷ lệ lấp đầy phòng hậu điều trị, tạo lợi thế cạnh tranh khác biệt.',
        },
        {
          icon: '/images/home/solutions/icon-3.png',
          title: 'Đối với Doanh nghiệp Lữ hành',
          body: 'Đa dạng hóa sản phẩm tour du lịch y tế cao cấp, gia tăng doanh thu và trải nghiệm khách hàng với các dịch vụ bảo chứng uy tín.',
        },
      ]
    : [
        {
          icon: '/images/home/solutions/icon-1.png',
          title: 'For Hospitals & Clinics',
          body: 'Expand high-end international patient reach, standardize intake SLA processes, and elevate brand standing.',
        },
        {
          icon: '/images/home/solutions/icon-2.png',
          title: 'For Hotels & Resorts',
          body: 'Develop premium Wellness & Medical Tourism products, increasing post-treatment resort occupancy.',
        },
        {
          icon: '/images/home/solutions/icon-3.png',
          title: 'For Travel Agencies',
          body: 'Diversify premium medical tourism packages, increasing revenue and customer satisfaction.',
        },
      ];

  const cmsData = await getSectionContent('home', 'solutions', locale, {
    title: defaultTitle,
    subtitle: defaultSubtitle,
    items: defaultItems,
  });

  const items = (cmsData.items && cmsData.items.length > 0) ? cmsData.items : defaultItems;

  return (
    <section className="relative py-16 md:py-24 overflow-hidden bg-white" data-home-section="solutions">
      {/* Background texture */}
      <div className="absolute inset-0 opacity-10">
        <img
          src="/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png"
          className="w-full h-full object-cover"
          alt=""
          aria-hidden="true"
        />
      </div>

      <div className="relative max-w-7xl mx-auto px-4">
        <h2 className="text-3xl md:text-4xl font-bold uppercase text-center text-[#0b7f7c]">
          {cmsData.title || defaultTitle}
        </h2>
        <p className="mt-3 text-sm md:text-base font-bold uppercase text-center text-[#0b7f7c] max-w-3xl mx-auto">
          {cmsData.subtitle || defaultSubtitle}
        </p>

        <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
          {items.map((item: any, i: number) => (
            <div
              key={i}
              className="text-center p-8 md:p-10 rounded-[1.25rem] bg-gradient-to-b from-[#14acab] to-[#0b7f7c] shadow-xl hover:scale-105 transition-transform duration-300"
            >
              <img
                src={item.icon || `/images/home/solutions/icon-${(i % 3) + 1}.png`}
                alt={item.title}
                className="mx-auto h-[60px] w-auto object-contain mb-5"
              />
              <h3 className="text-lg font-bold uppercase text-white mb-3 text-center">
                {item.title}
              </h3>
              <p className="text-sm text-white/90 leading-relaxed text-justify">
                {item.body}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
