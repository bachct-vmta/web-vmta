import React from 'react';
import { VideoPlayer } from '@/components/common/VideoPlayer';
import { getSectionContent } from '@/lib/cms';

interface VisionMissionSectionProps {
  locale: string;
}

export const VisionMissionSection: React.FC<VisionMissionSectionProps> = async ({ locale }) => {
  const isVi = locale === 'vi';

  const defaultTitle = isVi ? 'TẦM NHÌN' : 'VISION';
  const defaultSubtitle = isVi ? 'SỨ MỆNH' : 'MISSION';
  const defaultBody = isVi
    ? 'Trở thành biểu tượng bảo chứng cho chất lượng Du lịch Y tế tại Việt Nam, đưa Việt Nam trở thành điểm đến ưu tiên trên bản đồ y khoa toàn cầu.'
    : 'To become the gold standard for Medical Tourism in Vietnam — establishing the country as a priority destination on the global medical map.';
  const defaultVideoUrl = 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8';

  const defaultAudienceItems = isVi
    ? [
        {
          title: 'VỚI KHÁCH HÀNG',
          body: 'Mang đến hành trình chăm sóc sức khỏe an toàn, cá nhân hóa và không rào cản địa lý.',
        },
        {
          title: 'VỚI ĐỐI TÁC',
          body: 'Xây dựng hệ điều hành kết nối thông minh, giúp nâng cao hiệu quả vận hành và giá trị thương hiệu.',
        },
        {
          title: 'VỚI NGÀNH',
          body: 'Định hình một hệ sinh thái Du lịch Y tế minh bạch, chuyên nghiệp và bền vững.',
        },
      ]
    : [
        {
          title: 'FOR CLIENTS',
          body: 'Delivering a safe, premium healthcare journey with end-to-end care and zero geographical barriers.',
        },
        {
          title: 'FOR PARTNERS',
          body: 'Building an intelligent coordination platform that connects hospitals, resorts and technology.',
        },
        {
          title: 'FOR THE INDUSTRY',
          body: 'Shaping a transparent, professional, and sustainable Medical Tourism ecosystem.',
        },
      ];

  const cmsData = await getSectionContent('home', 'vision_mission', locale, {
    title: defaultTitle,
    subtitle: defaultSubtitle,
    body: defaultBody,
    video_url: defaultVideoUrl,
    items: defaultAudienceItems,
  });

  const audienceItems = (cmsData.items && cmsData.items.length > 0) ? cmsData.items : defaultAudienceItems;

  return (
    <section className="py-16 md:py-24 bg-white overflow-hidden" data-home-section="vision_mission">
      <div className="max-w-7xl mx-auto px-4 space-y-16">
        {/* ROW 1: TẦM NHÌN */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
          <div className="md:col-span-5">
            <h2 className="text-3xl md:text-5xl font-bold uppercase text-[#0b7f7c] leading-tight">
              {cmsData.title || defaultTitle}
            </h2>
          </div>
          <div className="md:col-span-7">
            <div className="p-8 md:p-12 bg-[url('/images/home/vision-mission/asset-6-bg.jpg')] bg-right bg-contain bg-no-repeat rounded-2xl bg-teal-50/40 border border-teal-100">
              <div className="text-[#d31e45] text-base md:text-lg leading-relaxed italic font-semibold relative pl-6">
                <span className="text-4xl text-[#d31e45] font-serif absolute -left-2 top-0 leading-none">“</span>
                {cmsData.body || defaultBody}
              </div>
            </div>
          </div>
        </div>

        {/* ROW 2: SỨ MỆNH */}
        <div>
          <div className="grid grid-cols-1 md:grid-cols-12 items-center gap-8 mb-10">
            <div className="md:col-span-8 hidden lg:block">
              <div className="p-8 bg-[url('/images/home/vision-mission/asset-7-bg.jpg')] bg-left bg-contain bg-no-repeat min-h-[120px]"></div>
            </div>
            <div className="md:col-span-4 lg:col-span-4">
              <h2 className="text-3xl md:text-5xl font-bold uppercase text-[#0b7f7c] text-left lg:text-right leading-tight">
                {cmsData.subtitle || defaultSubtitle}
              </h2>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {audienceItems.map((item: any, idx: number) => (
              <div key={idx} className="bg-slate-50 p-6 rounded-2xl border border-slate-100 text-center">
                <h4 className="text-base font-bold uppercase text-[#d31e45] mb-2 tracking-wide">
                  {item.title || item.audience}
                </h4>
                <p className="text-sm text-slate-600 leading-relaxed text-justify">
                  {item.body}
                </p>
              </div>
            ))}
          </div>
        </div>

        {/* ROW 3: Dynamic Video Player from Admin */}
        <div className="rounded-2xl overflow-hidden shadow-2xl border-4 border-slate-100 relative">
          <VideoPlayer
            src={cmsData.video_url || defaultVideoUrl}
            poster="/images/home/vision-mission/video-poster.webp"
          />
        </div>
      </div>
    </section>
  );
};
