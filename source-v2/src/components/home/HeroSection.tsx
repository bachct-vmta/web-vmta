import React from 'react';
import { getSectionContent } from '@/lib/cms';

interface HeroSectionProps {
  locale: string;
}

export const HeroSection: React.FC<HeroSectionProps> = async ({ locale }) => {
  const isVi = locale === 'vi';

  const defaultTitle = isVi
    ? 'HỘI ĐỒNG Y KHOA & DU LỊCH Y TẾ VIỆT NAM'
    : 'VIETNAM MEDICAL TOURISM ALLIANCE';
  const defaultSubtitle = isVi
    ? 'VIETNAM MEDICAL TOURISM ALLIANCE'
    : 'INTERNATIONAL HEALTHCARE & RESORT RECOVERY';
  const defaultBody = isVi
    ? 'Nơi hội tụ các bệnh viện, trung tâm y tế hàng đầu và doanh nghiệp lữ hành uy tín, mang đến giải pháp chăm sóc sức khỏe toàn diện kết hợp nghỉ dưỡng cao cấp tại Việt Nam.'
    : 'Uniting top accredited medical centers and premier travel operators to deliver all-inclusive healthcare and resort recovery in Vietnam.';
  const defaultImage = '/images/home/hero/banner-bg.png';

  const cmsData = await getSectionContent('home', 'hero', locale, {
    title: defaultTitle,
    subtitle: defaultSubtitle,
    body: defaultBody,
    image_url: defaultImage,
  });

  const marqueeItems = isVi
    ? [
        { label: 'BỆNH VIỆN ĐẠT CHUẨN', value: 'JCI / ISO INTERNATIONAL' },
        { label: 'ĐỘI NGŨ BÁC SĨ', value: 'CHUYÊN GIA ĐẦU NGÀNH' },
        { label: 'CHI PHÍ TIẾT KIỆM', value: 'LÊN ĐẾN 60%' },
        { label: 'HỖ TRỢ 24/7', value: 'ĐIỀU PHỐI Y TẾ TOÀN DIỆN' },
      ]
    : [
        { label: 'ACCREDITED HOSPITALS', value: 'JCI / ISO CERTIFIED' },
        { label: 'MEDICAL TEAM', value: 'TOP SPECIALISTS' },
        { label: 'COST SAVINGS', value: 'UP TO 60%' },
        { label: '24/7 SUPPORT', value: 'MEDICAL COORDINATION' },
      ];

  const duplicatedMarquee = [...marqueeItems, ...marqueeItems];

  return (
    <section className="relative overflow-hidden bg-white" data-home-section="hero">
      {/* Background image loaded dynamically from DB */}
      <div className="absolute inset-0">
        <img
          src={cmsData.image_url || defaultImage}
          className="w-full h-full object-cover opacity-20"
          alt=""
          aria-hidden="true"
        />
      </div>

      <div className="relative max-w-7xl mx-auto px-4 py-20 md:py-32 text-center z-10">
        <h1 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-[#0b7f7c] mx-auto max-w-5xl tracking-tight leading-tight">
          {cmsData.title || defaultTitle}
        </h1>

        <p className="mt-4 text-lg md:text-2xl font-bold uppercase text-[#0b7f7c] max-w-3xl mx-auto tracking-wide">
          {cmsData.subtitle || defaultSubtitle}
        </p>

        <p className="mt-5 text-sm md:text-base font-bold text-slate-800 max-w-3xl mx-auto leading-relaxed text-center">
          {cmsData.body || defaultBody}
        </p>

        <div className="mt-8">
          <a
            href={`/${locale}/lien-he`}
            className="inline-block rounded-md bg-[#d31e45] px-8 py-3.5 text-base font-bold text-white uppercase tracking-wide hover:bg-[#b01838] transition shadow-lg shadow-[#d31e45]/20"
          >
            {isVi ? 'ĐĂNG KÝ NGAY' : 'REGISTER NOW'}
          </a>
        </div>
      </div>

      {/* Marquee Strip */}
      <div className="relative bg-[#0b7f7c] overflow-hidden py-1 border-t border-b border-white">
        <div className="w-full max-w-7xl mx-auto py-3 overflow-hidden">
          <div className="flex gap-16 animate-[marquee_30s_linear_infinite] whitespace-nowrap">
            {duplicatedMarquee.map((item, index) => (
              <div key={index} className="flex items-center gap-2 shrink-0">
                <span className="font-bold text-base md:text-lg text-white">{item.label}:</span>
                <span className="font-bold text-base md:text-lg text-amber-200">{item.value}</span>
                <span className="ml-8 text-white/50">•</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};
