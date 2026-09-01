import React from 'react';
import Link from 'next/link';
import { getSectionContent, getOrderedPageSections } from '@/lib/cms';

export default async function AboutUsPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  const orderedKeys = await getOrderedPageSections('about');
  const defaultKeys = ['hero', 'architect', 'how_it_works', 'difference'];
  const keysToRender = orderedKeys.length > 0 ? orderedKeys : defaultKeys;

  const heroCms = await getSectionContent('about', 'hero', locale, {
    title: isVi
      ? 'KIẾN TRÚC SƯ TRƯỞNG CHO HỆ SINH THÁI DU LỊCH Y TẾ VIỆT NAM'
      : 'PIONEERING VIETNAM MEDICAL TOURISM ECOSYSTEM',
    subtitle: isVi ? 'GIỚI THIỆU VỀ VMTA' : 'ABOUT VMTA',
    body: isVi
      ? 'Liên Minh Du Lịch Y Tế Việt Nam (VMTA) hình thành nhằm định hình một chuẩn mực mới cho ngành Du lịch Y tế tại Việt Nam — nơi chất lượng y khoa, trải nghiệm nghỉ dưỡng và công nghệ vận hành được tích hợp toàn diện.'
      : 'Vietnam Medical Tourism Alliance shapes new standards integrating clinical excellence, luxury recovery, and technology.',
    image_url: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
  });

  const defaultWhoAreItems = isVi
    ? [
        {
          title: 'Thực thể Điều phối Độc lập',
          body: 'Xây dựng tiêu chuẩn, vận hành hệ thống kết nối thông minh và quản trị chất lượng toàn bộ hành trình Du lịch Y tế.',
          icon: '/images/about/Asset-1@4x-2.png',
        },
        {
          title: 'Chuẩn hóa Tiêu chuẩn Vận hành (SLA)',
          body: 'Đưa các tiêu chuẩn quốc tế về y khoa, an toàn người bệnh và chất lượng dịch vụ vào quy trình vận hành đồng bộ giữa các thành viên.',
          icon: '/images/about/Asset-2@4x-4.png',
        },
        {
          title: 'Bảo chứng Uy tín & Nâng tầm Ngành',
          body: 'Tạo dựng niềm tin cho du khách quốc tế và khẳng định vị thế của Việt Nam trên bản đồ Du lịch Y tế toàn cầu.',
          icon: '/images/about/Asset-3@4x-3.png',
        },
      ]
    : [
        {
          title: 'Independent Coordinating Entity',
          body: 'Establishing standards, running intelligent connection systems, and managing medical tourism quality.',
          icon: '/images/about/Asset-1@4x-2.png',
        },
        {
          title: 'Standardized Operations (SLA)',
          body: 'Applying international medical and safety standards across all ecosystem members.',
          icon: '/images/about/Asset-2@4x-4.png',
        },
        {
          title: 'Credibility Assurance',
          body: 'Building trust for international tourists and elevating Vietnam on the global medical tourism map.',
          icon: '/images/about/Asset-3@4x-3.png',
        },
      ];

  const architectCms = await getSectionContent('about', 'architect', locale, {
    title: isVi ? 'VMTA LÀ AI ?' : 'WHO IS VMTA ?',
    subtitle: isVi ? 'ĐƠN VỊ ĐIỀU PHỐI ĐỘC LẬP' : 'INDEPENDENT COORDINATOR',
    body: isVi
      ? 'VMTA không phải là một bệnh viện, phòng khám hay công ty lữ hành đơn lẻ. Chúng tôi là thực thể điều phối trung tâm — đơn vị định hình tiêu chuẩn, vận hành hệ thống kết nối và bảo chứng chất lượng cho toàn bộ hệ sinh thái Du lịch Y tế tại Việt Nam.'
      : 'We are the central orchestrating body — defining standards, operating intelligent connection systems, and certifying quality for the entire Medical Tourism ecosystem in Vietnam.',
    image_url: '/images/about/Asset-7-100.jpg',
    items: defaultWhoAreItems,
  });

  const defaultHowWorksItems = isVi
    ? [
        {
          title: 'Thẩm định hồ sơ từ xa',
          body: 'Đánh giá tình trạng và đề xuất phương án điều trị phù hợp',
          icon: '/images/about/Asset-4@4x-2-300x300.png',
        },
        {
          title: 'Thiết kế hành trình cá nhân hóa',
          body: 'Xây dựng lộ trình điều trị và phục hồi dựa trên nhu cầu cụ thể',
          icon: '/images/about/Asset-2@4x-3-300x300.png',
        },
        {
          title: 'Điều phối điều trị',
          body: 'Kết nối và vận hành giữa các đơn vị trong hệ sinh thái',
          icon: '/images/about/Asset-3@4x-2-300x300.png',
        },
        {
          title: 'Theo dõi và chăm sóc hậu điều trị',
          body: 'Đảm bảo quá trình phục hồi được kiểm soát và tối ưu',
          icon: '/images/about/Asset-4@4x-2-300x300.png',
        },
      ]
    : [
        {
          title: 'Remote Case Evaluation',
          body: 'Assess medical conditions and propose appropriate treatment plans',
          icon: '/images/about/Asset-4@4x-2-300x300.png',
        },
        {
          title: 'Personalized Journey Design',
          body: 'Develop treatment and recovery plans tailored to individual needs',
          icon: '/images/about/Asset-2@4x-3-300x300.png',
        },
        {
          title: 'Treatment Coordination',
          body: 'Connect and manage operations among units within the ecosystem',
          icon: '/images/about/Asset-3@4x-2-300x300.png',
        },
        {
          title: 'Post-Treatment Monitoring',
          body: 'Ensure the recovery process is closely monitored and optimized',
          icon: '/images/about/Asset-4@4x-2-300x300.png',
        },
      ];

  const howWorksCms = await getSectionContent('about', 'how_it_works', locale, {
    title: isVi ? 'CÁCH VMTA HOẠT ĐỘNG' : 'HOW VMTA WORKS',
    items: defaultHowWorksItems,
  });

  const defaultDifferences = isVi
    ? [
        'Thẩm định nghiêm ngặt trước khi đưa vào hệ sinh thái',
        'Quản trị toàn bộ hành trình thay vì cung cấp dịch vụ rời rạc',
        'Ứng dụng công nghệ để theo dõi và tối ưu trải nghiệm',
        'Đồng hành cùng khách hàng và đối tác trong dài hạn',
      ]
    : [
        'Strict appraisal before being included in the ecosystem',
        'Managing the entire journey instead of providing fragmented services',
        'Applying technology to monitor and optimize the experience',
        'Long-term companionship with customers and partners',
      ];

  const differenceCms = await getSectionContent('about', 'difference', locale, {
    title: isVi ? 'KHÁC BIỆT CỦA VMTA' : 'WHAT MAKES VMTA DIFFERENT',
    bullets: defaultDifferences,
  });

  const whoAreItems = (architectCms.items && architectCms.items.length > 0) ? architectCms.items : defaultWhoAreItems;
  const howWorksItems = (howWorksCms.items && howWorksCms.items.length > 0) ? howWorksCms.items : defaultHowWorksItems;
  const differences = (differenceCms.bullets && differenceCms.bullets.length > 0) ? differenceCms.bullets : defaultDifferences;

  return (
    <div id="content" className="content-area scroll-smooth bg-white space-y-0">
      {keysToRender.map((key) => {
        if (key === 'hero') {
          return (
            <section key="hero" id="section-hero" className="vmta-banner-hero relative min-h-[500px] flex items-center justify-center overflow-hidden bg-white py-20">
              <div className="absolute inset-0">
                <img
                  src={heroCms.image_url || '/images/about/hero.jpg'}
                  className="w-full h-full object-cover opacity-20"
                  alt=""
                />
              </div>
              <div className="relative max-w-7xl mx-auto px-4 z-10 text-center">
                <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold uppercase text-[#0b7f7c] leading-tight max-w-5xl mx-auto">
                  {heroCms.title}
                </h1>
                <p className="mt-6 text-base md:text-xl font-bold uppercase text-[#0b7f7c] max-w-3xl mx-auto leading-relaxed">
                  {heroCms.body}
                </p>

                <div className="pt-8 flex flex-wrap justify-center gap-4">
                  <a
                    href="#section-who-are"
                    className="inline-block rounded-md border border-[#0b7f7c] bg-white px-6 py-3 font-bold text-[#0b7f7c] uppercase text-sm hover:bg-[#0b7f7c] hover:text-white transition"
                  >
                    {isVi ? 'KHÁM PHÁ HÀNH TRÌNH' : 'DISCOVER JOURNEY'}
                  </a>
                  <Link
                    href={`/${locale}/lien-minh-du-lich-y-te`}
                    className="inline-block rounded-md bg-[#d31e45] px-6 py-3 font-bold text-white uppercase text-sm hover:bg-[#b01838] transition shadow-md"
                  >
                    {isVi ? 'THAM GIA HỆ SINH THÁI' : 'JOIN ECOSYSTEM'}
                  </Link>
                </div>
              </div>
            </section>
          );
        }

        if (key === 'architect') {
          return (
            <section key="architect" id="section-who-are" className="py-16 md:py-24 bg-white">
              <div className="relative max-w-7xl mx-auto px-4">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                  <div>
                    <h2 className="text-3xl md:text-5xl font-extrabold uppercase leading-tight text-[#0b7f7c] mb-4">
                      {architectCms.title}
                    </h2>
                    <div className="text-sm md:text-base text-slate-700 leading-relaxed text-justify space-y-3 mb-8">
                      <p>{architectCms.body}</p>
                    </div>

                    <div className="space-y-4">
                      {whoAreItems.map((item: any, i: number) => (
                        <div key={i} className="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                          <div className="w-10 h-10 rounded-xl bg-teal-50 text-[#0b7f7c] flex items-center justify-center font-bold text-base shrink-0">
                            0{i + 1}
                          </div>
                          <div>
                            <p className="font-bold text-[#0b7f7c] text-sm mb-1">{item.title}</p>
                            <p className="text-xs text-slate-600 leading-relaxed text-justify">{item.body}</p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div>
                    <img
                      src={architectCms.image_url || '/images/about/architect.jpg'}
                      alt="VMTA Architecture"
                      className="w-full h-auto rounded-3xl shadow-xl border border-slate-100 object-cover"
                    />
                  </div>
                </div>
              </div>
            </section>
          );
        }

        if (key === 'how_it_works') {
          return (
            <section key="how_it_works" className="py-16 md:py-24 bg-slate-50 border-t border-slate-200/60">
              <div className="max-w-7xl mx-auto px-4">
                <div className="text-center mb-12">
                  <h2 className="text-3xl md:text-5xl font-extrabold uppercase text-[#0b7f7c]">
                    {howWorksCms.title}
                  </h2>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                  {howWorksItems.map((step: any, i: number) => (
                    <div key={i} className="text-center p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                      <div className="w-12 h-12 rounded-2xl bg-teal-50 text-[#0b7f7c] font-extrabold text-lg flex items-center justify-center mx-auto mb-4">
                        0{i + 1}
                      </div>
                      <p className="font-bold text-sm text-[#d31e45] uppercase mb-2">{step.title}</p>
                      <p className="text-xs text-slate-600 leading-relaxed text-justify">{step.body}</p>
                    </div>
                  ))}
                </div>
              </div>
            </section>
          );
        }

        if (key === 'difference') {
          return (
            <section key="difference" className="relative min-h-[400px] flex items-center overflow-hidden py-16 bg-white border-t border-slate-100">
              <div className="relative max-w-7xl w-full mx-auto px-4 z-10">
                <div className="max-w-3xl">
                  <h2 className="text-3xl md:text-5xl font-extrabold uppercase text-[#0b7f7c] mb-6">
                    {differenceCms.title}
                  </h2>
                  <div className="space-y-4 font-medium text-slate-800 text-sm md:text-base">
                    {differences.map((diff: string, i: number) => (
                      <div key={i} className="flex items-center gap-3 bg-white/80 p-3.5 rounded-xl border border-teal-100 shadow-sm">
                        <span className="w-6 h-6 rounded-full bg-[#0b7f7c] text-white font-bold text-xs flex items-center justify-center shrink-0">
                          ✓
                        </span>
                        <span>{diff}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </section>
          );
        }

        return null;
      })}
    </div>
  );
}
