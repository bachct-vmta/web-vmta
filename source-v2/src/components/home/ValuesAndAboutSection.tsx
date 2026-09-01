import React from 'react';
import { getSectionContent } from '@/lib/cms';

interface ValuesAndAboutSectionProps {
  locale: string;
}

export const ValuesAndAboutSection: React.FC<ValuesAndAboutSectionProps> = async ({ locale }) => {
  const isVi = locale === 'vi';

  const defaultValuesItems = isVi
    ? [
        { icon: '/images/home/values/icon-1.png', title: 'Niềm tin từ chuyên môn', body: 'Mọi cơ sở y tế và đội ngũ chuyên gia trong liên minh đều trải qua quy trình thẩm định khắt khe trước khi tiếp nhận du khách.' },
        { icon: '/images/home/values/icon-2.png', title: 'Sự minh bạch tuyệt đối', body: 'Minh bạch về chi phí, quy trình điều trị, tiêu chuẩn dịch vụ và quyền lợi của khách hàng trong suốt hành trình.' },
        { icon: '/images/home/values/icon-3.png', title: 'Sự kết nối chuẩn hóa', body: 'Xây dựng một ngôn ngữ chung và quy trình vận hành đồng bộ giữa ngành Y tế và ngành Du lịch – Nghỉ dưỡng.' },
        { icon: '/images/home/values/icon-1.png', title: 'Lấy trải nghiệm khách hàng làm trung tâm', body: 'Mỗi hành trình được thiết kế cá nhân hóa, tối ưu sự an tâm, riêng tư và sự phục hồi toàn diện cho người bệnh.' },
        { icon: '/images/home/values/icon-2.png', title: 'Khát vọng vươn tầm quốc tế', body: 'Định hình vị thế mới cho Du lịch Y tế Việt Nam bằng chất lượng dịch vụ chuẩn SLA và tư duy vận hành hiện đại.' },
      ]
    : [
        { icon: '/images/home/values/icon-1.png', title: 'TRUST FROM EXPERTISE', body: 'Every medical center and expert team undergoes strict vetting before accepting patients.' },
        { icon: '/images/home/values/icon-2.png', title: 'ABSOLUTE TRANSPARENCY', body: 'Full transparency in treatment costs, procedures, and patient rights throughout the journey.' },
        { icon: '/images/home/values/icon-3.png', title: 'STANDARDIZED INTEGRATION', body: 'Unified operating standards bridging Healthcare and Tourism sectors.' },
        { icon: '/images/home/values/icon-1.png', title: 'PATIENT-CENTRIC EXPERIENCE', body: 'Every itinerary is personalized to ensure peace of mind, privacy, and full recovery.' },
        { icon: '/images/home/values/icon-2.png', title: 'GLOBAL AMBITION', body: 'Elevating Vietnam Medical Tourism with SLA-grade service standards.' },
      ];

  const defaultAboutBullets = isVi
    ? [
        'Hệ thống tiếp nhận và điều phối hồ sơ bệnh án thông minh.',
        'Minh bạch quy trình và bảo chứng chất lượng dịch vụ tại mọi điểm chạm.',
        'Đồng hành 24/7 từ khi tư vấn trước khởi hành đến chăm sóc sau điều trị.',
      ]
    : [
        'Intelligent medical record intake and coordination system.',
        '100% transparent procedures and quality assurance at every touchpoint.',
        '24/7 dedicated support from pre-departure consultation to post-op care.',
      ];

  const cmsDataValues = await getSectionContent('home', 'values', locale, {
    title: isVi ? 'TẦM NHÌN, SỨ MỆNH & GIÁ TRỊ CỐT LÕI' : 'VISION, MISSION & CORE VALUES',
    subtitle: isVi ? 'TỰ HÀO LÀ CẦU NỐI Y TẾ VÀ DU LỊCH NGHỈ DƯỠNG UY TÍN' : 'PROUD TO BE VIETNAM’S PREMIER MEDICAL TOURISM ALLIANCE',
    image_url: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
    items: defaultValuesItems,
  });

  const cmsDataAbout = await getSectionContent('home', 'about', locale, {
    title: isVi
      ? 'VMTA – KIẾN TRÚC SƯ TRƯỞNG CHO HỆ SINH THÁI DU LỊCH Y TẾ VIỆT NAM'
      : 'VMTA – PIONEERING VIETNAM MEDICAL TOURISM ECOSYSTEM',
    subtitle: isVi ? 'CẦU NỐI Y TẾ VÀ DU LỊCH NGHỈ DƯỠNG' : 'HEALTHCARE & RESORT BRIDGING',
    body: isVi
      ? 'Liên Minh Du Lịch Y Tế Việt Nam (VMTA) ra đời với vai trò là đơn vị điều phối độc lập, kết nối và chuẩn hóa các nguồn lực từ Y tế, Du lịch đến Công nghệ.'
      : 'Vietnam Medical Tourism Alliance (VMTA) acts as an independent coordinator unifying Healthcare, Tourism, and Technology resources.',
    image_url: '/images/home/about/image-right.jpg',
    bullets: defaultAboutBullets,
  });

  const valuesItems = (cmsDataValues.items && cmsDataValues.items.length > 0) ? cmsDataValues.items : defaultValuesItems;
  const aboutBullets = (cmsDataAbout.bullets && cmsDataAbout.bullets.length > 0) ? cmsDataAbout.bullets : defaultAboutBullets;

  return (
    <section className="vmta-bg-filter-20 relative py-16 md:py-24 bg-white overflow-hidden" data-home-section="values">
      {/* Background texture */}
      <div className="absolute inset-0 opacity-10">
        <img
          src={cmsDataValues.image_url || '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png'}
          className="w-full h-full object-cover scale-125"
          alt=""
          aria-hidden="true"
        />
      </div>

      <div className="relative max-w-7xl mx-auto px-4 space-y-16">
        {/* ROW 1: GIÁ TRỊ CỐT LÕI */}
        <div>
          <h2 className="text-3xl md:text-4xl font-bold uppercase text-center text-[#0b7f7c]">
            {cmsDataValues.title}
          </h2>
          <p className="mt-3 text-sm md:text-base font-bold uppercase text-center text-[#0b7f7c] max-w-3xl mx-auto">
            {cmsDataValues.subtitle}
          </p>

          <div className="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            {valuesItems.map((item: any, index: number) => (
              <div key={index} className="text-center p-6 bg-slate-50/80 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <img
                  src={item.icon || `/images/home/values/icon-${(index % 3) + 1}.png`}
                  alt={item.title}
                  className="mx-auto h-16 w-16 object-contain mb-4"
                />
                <h3 className="text-base font-bold uppercase text-[#d31e45] text-center mb-3">
                  {item.title}
                </h3>
                <p className="text-xs md:text-sm text-[#4a4a4a] leading-relaxed text-justify">
                  {item.body}
                </p>
              </div>
            ))}
          </div>
        </div>

        {/* ROW 2: VỀ VMTA */}
        <div data-home-section="about" className="pt-8 border-t border-slate-100 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
          <div className="lg:col-span-7 space-y-4">
            <span className="text-xs uppercase tracking-widest text-[#0b7f7c] font-bold">
              {cmsDataAbout.subtitle}
            </span>
            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold uppercase text-[#0b7f7c] leading-tight">
              {cmsDataAbout.title}
            </h2>
            <div className="text-sm md:text-base text-slate-700 leading-relaxed text-justify space-y-3">
              <p>{cmsDataAbout.body}</p>
            </div>

            <ul className="space-y-3 pt-2">
              {aboutBullets.map((bullet: string, idx: number) => (
                <li key={idx} className="flex items-start gap-3">
                  <span className="mt-1.5 h-2.5 w-2.5 rounded-full bg-[#d31e45] shrink-0"></span>
                  <span className="text-sm font-semibold text-slate-700">{bullet}</span>
                </li>
              ))}
            </ul>

            <div className="pt-4">
              <a
                href={`/${locale}/gioi-thieu`}
                className="inline-block rounded-md bg-[#d31e45] text-white px-7 py-3 font-bold uppercase text-sm hover:bg-[#b01838] transition shadow-md"
              >
                {isVi ? 'KHÁM PHÁ THÊM' : 'DISCOVER MORE'}
              </a>
            </div>
          </div>

          <div className="lg:col-span-5">
            <img
              src={cmsDataAbout.image_url || '/images/home/about/image-right.jpg'}
              alt="VMTA About"
              className="w-full rounded-[35px] shadow-2xl object-cover aspect-[4/5]"
            />
          </div>
        </div>
      </div>
    </section>
  );
};
