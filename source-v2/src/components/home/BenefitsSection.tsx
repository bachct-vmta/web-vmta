import React from 'react';
import { getSectionContent } from '@/lib/cms';

interface BenefitsSectionProps {
  locale: string;
}

export const BenefitsSection: React.FC<BenefitsSectionProps> = async ({ locale }) => {
  const isVi = locale === 'vi';

  const defaultTitle = isVi ? 'QUYỀN LỢI THÀNH VIÊN LIÊN MINH' : 'ALLIANCE MEMBER BENEFITS';
  const defaultItems = isVi
    ? [
        { title: 'Bảo Chứng Uy Tín Thương Hiệu', body: 'Gia nhập mạng lưới đạt chuẩn thẩm định độc lập VMTA, nâng cao niềm tin đối với du khách.' },
        { title: 'Tiếp Cận Nguồn Khách Hàng Quốc Tế', body: 'Kết nối mạng lưới kênh phân phối lữ hành toàn cầu và hệ thống tiếp nhận bệnh nhân tập trung.' },
        { title: 'Ứng Dụng Công Nghệ Vận Hành', body: 'Sử dụng hệ điều hành điều phối hồ sơ bệnh án và lịch trình nghỉ dưỡng chuyên nghiệp.' },
        { title: 'Đồng Hành Phát Triển Bền Vững', body: 'Tham gia chuỗi sự kiện xúc tiến thương mại và hội thảo chuyển giao công nghệ y khoa.' },
      ]
    : [
        { title: 'Brand Credibility Endorsement', body: 'Join the independently vetted VMTA network to boost international patient trust.' },
        { title: 'Access International Patient Pool', body: 'Connect with global travel channels and centralized medical intake systems.' },
        { title: 'Operational Tech Integration', body: 'Utilize professional medical record and resort recovery coordination operating software.' },
        { title: 'Sustainable Growth Partnership', body: 'Participate in trade promotion events and international medical technology seminars.' },
      ];

  const cmsData = await getSectionContent('home', 'benefits', locale, {
    title: defaultTitle,
    items: defaultItems,
  });

  const items = (cmsData.items && cmsData.items.length > 0) ? cmsData.items : defaultItems;

  return (
    <section className="py-16 md:py-24 bg-slate-50 relative overflow-hidden" data-home-section="benefits">
      <div className="max-w-7xl mx-auto px-4 relative z-10 space-y-12">
        <h2 className="text-3xl md:text-4xl font-bold uppercase text-center text-[#0b7f7c]">
          {cmsData.title || defaultTitle}
        </h2>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {items.map((item: any, idx: number) => (
            <div key={idx} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3 hover:shadow-md transition">
              <div className="w-10 h-10 rounded-xl bg-teal-50 text-[#0b7f7c] flex items-center justify-center font-bold text-lg">
                0{idx + 1}
              </div>
              <h3 className="text-base font-bold uppercase text-[#d31e45]">
                {item.title}
              </h3>
              <p className="text-xs text-slate-600 leading-relaxed text-justify">
                {item.body}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
