import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

const defaultSections = [
  // ==================== 1. TRANG CHỦ (home) ====================
  {
    page_slug: 'home',
    section_key: 'hero',
    order: 1,
    vi: {
      title: 'HỘI ĐỒNG Y KHOA & DU LỊCH Y TẾ VIỆT NAM',
      subtitle: 'VIETNAM MEDICAL TOURISM ALLIANCE',
      body: 'Nơi hội tụ các bệnh viện, trung tâm y tế hàng đầu và doanh nghiệp lữ hành uy tín, mang đến giải pháp chăm sóc sức khỏe toàn diện kết hợp nghỉ dưỡng cao cấp tại Việt Nam.',
      extra_json: {
        cta_primary_label: 'KHÁM PHÁ NGAY',
        cta_primary_url: '#section-about',
        cta_secondary_label: 'LIÊN HỆ TƯ VẤN',
        cta_secondary_url: '/vi/lien-he',
        image_url: '/images/home/hero/banner-bg.png',
        image_url_vi: '/images/home/hero/banner-bg.png',
        image_url_en: '/images/home/hero/banner-bg.png',
        items: [
          { title: 'BỆNH VIỆN ĐẠT CHUẨN', body: 'JCI / ISO INTERNATIONAL' },
          { title: 'ĐỘI NGŨ BÁC SĨ', body: 'CHUYÊN GIA ĐẦU NGÀNH' },
          { title: 'CHI PHÍ TIẾT KIỆM', body: 'LÊN ĐẾN 60%' },
          { title: 'HỖ TRỢ 24/7', body: 'ĐIỀU PHỐI Y TẾ TOÀN DIỆN' },
        ],
      },
    },
    en: {
      title: 'VIETNAM MEDICAL TOURISM ALLIANCE',
      subtitle: 'INTERNATIONAL HEALTHCARE & RESORT RECOVERY',
      body: 'Uniting top accredited medical centers and premier travel operators to deliver all-inclusive healthcare and resort recovery in Vietnam.',
      extra_json: {
        cta_primary_label: 'DISCOVER NOW',
        cta_primary_url: '#section-about',
        cta_secondary_label: 'CONTACT CONSULTANT',
        cta_secondary_url: '/en/lien-he',
        image_url: '/images/home/hero/banner-bg.png',
        image_url_vi: '/images/home/hero/banner-bg.png',
        image_url_en: '/images/home/hero/banner-bg.png',
        items: [
          { title: 'ACCREDITED HOSPITALS', body: 'JCI / ISO CERTIFIED' },
          { title: 'MEDICAL TEAM', body: 'TOP SPECIALISTS' },
          { title: 'COST SAVINGS', body: 'UP TO 60%' },
          { title: '24/7 SUPPORT', body: 'MEDICAL COORDINATION' },
        ],
      },
    },
  },
  {
    page_slug: 'home',
    section_key: 'values',
    order: 2,
    vi: {
      title: 'TẦM NHÌN, SỨ MỆNH & GIÁ TRỊ CỐT LÕI',
      subtitle: 'TỰ HÀO LÀ CẦU NỐI Y TẾ VÀ DU LỊCH NGHỈ DƯỠNG UY TÍN',
      body: 'Mọi cơ sở y tế và đội ngũ chuyên gia trong liên minh đều trải qua quy trình thẩm định khắt khe trước khi tiếp nhận du khách.',
      extra_json: {
        image_url: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        image_url_vi: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        image_url_en: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        items: [
          { title: 'Niềm tin từ chuyên môn', body: 'Mọi cơ sở y tế và đội ngũ chuyên gia trong liên minh đều trải qua quy trình thẩm định khắt khe trước khi tiếp nhận du khách.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'Sự minh bạch tuyệt đối', body: 'Minh bạch về chi phí, quy trình điều trị, tiêu chuẩn dịch vụ và quyền lợi của khách hàng trong suốt hành trình.', icon_url: '/images/home/values/icon-2.png' },
          { title: 'Sự kết nối chuẩn hóa', body: 'Xây dựng một ngôn ngữ chung và quy trình vận hành đồng bộ giữa ngành Y tế và ngành Du lịch – Nghỉ dưỡng.', icon_url: '/images/home/values/icon-3.png' },
          { title: 'Lấy trải nghiệm khách hàng làm trung tâm', body: 'Mỗi hành trình được thiết kế cá nhân hóa, tối ưu sự an tâm, riêng tư và sự phục hồi toàn diện cho người bệnh.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'Khát vọng vươn tầm quốc tế', body: 'Định hình vị thế mới cho Du lịch Y tế Việt Nam bằng chất lượng dịch vụ chuẩn SLA và tư duy vận hành hiện đại.', icon_url: '/images/home/values/icon-2.png' },
        ],
      },
    },
    en: {
      title: 'VISION, MISSION & CORE VALUES',
      subtitle: 'PROUD TO BE VIETNAM’S PREMIER MEDICAL TOURISM ALLIANCE',
      body: 'Every medical center and expert team undergoes strict vetting before accepting patients.',
      extra_json: {
        image_url: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        image_url_vi: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        image_url_en: '/images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png',
        items: [
          { title: 'TRUST FROM EXPERTISE', body: 'Every medical center and expert team undergoes strict vetting before accepting patients.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'ABSOLUTE TRANSPARENCY', body: 'Full transparency in treatment costs, procedures, and patient rights throughout the journey.', icon_url: '/images/home/values/icon-2.png' },
          { title: 'STANDARDIZED INTEGRATION', body: 'Unified operating standards bridging Healthcare and Tourism sectors.', icon_url: '/images/home/values/icon-3.png' },
          { title: 'PATIENT-CENTRIC EXPERIENCE', body: 'Every itinerary is personalized to ensure peace of mind, privacy, and full recovery.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'GLOBAL AMBITION', body: 'Elevating Vietnam Medical Tourism with SLA-grade service standards.', icon_url: '/images/home/values/icon-2.png' },
        ],
      },
    },
  },
  {
    page_slug: 'home',
    section_key: 'about',
    order: 3,
    vi: {
      title: 'VMTA – KIẾN TRÚC SƯ TRƯỞNG CHO HỆ SINH THÁI DU LỊCH Y TẾ VIỆT NAM',
      subtitle: 'CẦU NỐI Y TẾ VÀ DU LỊCH NGHỈ DƯỠNG',
      body: 'Liên Minh Du Lịch Y Tế Việt Nam (VMTA) ra đời với vai trò là đơn vị điều phối độc lập, kết nối và chuẩn hóa các nguồn lực từ Y tế, Du lịch đến Công nghệ.',
      extra_json: {
        cta_label: 'KHÁM PHÁ THÊM',
        cta_url: '/vi/gioi-thieu',
        image_url: '/images/about/Asset-7-100.jpg',
        image_url_vi: '/images/about/Asset-7-100.jpg',
        image_url_en: '/images/about/Asset-7-100.jpg',
        bullets: [
          'Hệ thống tiếp nhận và điều phối hồ sơ bệnh án thông minh.',
          'Minh bạch quy trình và bảo chứng chất lượng dịch vụ tại mọi điểm chạm.',
          'Đồng hành 24/7 từ khi tư vấn trước khởi hành đến chăm sóc sau điều trị.',
        ],
      },
    },
    en: {
      title: 'VMTA – PIONEERING VIETNAM MEDICAL TOURISM ECOSYSTEM',
      subtitle: 'HEALTHCARE & RESORT BRIDGING',
      body: 'Vietnam Medical Tourism Alliance (VMTA) acts as an independent coordinator unifying Healthcare, Tourism, and Technology resources.',
      extra_json: {
        cta_label: 'DISCOVER MORE',
        cta_url: '/en/gioi-thieu',
        image_url: '/images/about/Asset-7-100.jpg',
        image_url_vi: '/images/about/Asset-7-100.jpg',
        image_url_en: '/images/about/Asset-7-100.jpg',
        bullets: [
          'Intelligent medical record intake and coordination system.',
          '100% transparent procedures and quality assurance at every touchpoint.',
          '24/7 dedicated support from pre-departure consultation to post-op care.',
        ],
      },
    },
  },
  {
    page_slug: 'home',
    section_key: 'solutions',
    order: 4,
    vi: {
      title: 'GIẢI PHÁP ĐIỀU PHỐI DÀNH CHO CÁC ĐƠN VỊ',
      subtitle: 'KẾT NỐI SỨC MẠNH HỆ SINH THÁI GIỮA Y TẾ - NGHỈ DƯỠNG - DU LỊCH',
      body: 'Hệ thống giải pháp khép kín hỗ trợ bệnh viện, phòng khám, resort nghỉ dưỡng và doanh nghiệp lữ hành tối ưu hiệu quả vận hành.',
      extra_json: {
        items: [
          { title: 'Đối với Bệnh viện & Cơ sở Y tế', body: 'Mở rộng tệp khách hàng quốc tế và nội địa cao cấp, tối ưu hóa quy trình tiếp nhận – hỗ trợ người bệnh theo chuẩn SLA.', icon_url: '/images/home/solutions/icon-1.png' },
          { title: 'Đối với Khách sạn & Resort', body: 'Phát triển dòng sản phẩm Du lịch Y tế / Wellness cao cấp, gia tăng tỷ lệ lấp đầy phòng hậu điều trị, tạo lợi thế cạnh tranh.', icon_url: '/images/home/solutions/icon-2.png' },
          { title: 'Đối với Doanh nghiệp Lữ hành', body: 'Đa dạng hóa sản phẩm tour du lịch y tế cao cấp, gia tăng doanh thu và trải nghiệm khách hàng với các dịch vụ bảo chứng uy tín.', icon_url: '/images/home/solutions/icon-3.png' },
        ],
      },
    },
    en: {
      title: 'COORDINATION SOLUTIONS FOR PARTNERS',
      subtitle: 'INTELLIGENT CONNECTING SYSTEM FOR HEALTHCARE & TOURISM',
      body: 'End-to-end solution suite supporting accredited hospitals, clinics, luxury resorts, and travel operators.',
      extra_json: {
        items: [
          { title: 'For Hospitals & Clinics', body: 'Expand high-end international patient reach, standardize intake SLA processes, and elevate brand standing.', icon_url: '/images/home/solutions/icon-1.png' },
          { title: 'For Hotels & Resorts', body: 'Develop premium Wellness & Medical Tourism products, increasing post-treatment resort occupancy.', icon_url: '/images/home/solutions/icon-2.png' },
          { title: 'For Travel Agencies', body: 'Diversify premium medical tourism packages, increasing revenue and customer satisfaction.', icon_url: '/images/home/solutions/icon-3.png' },
        ],
      },
    },
  },
  {
    page_slug: 'home',
    section_key: 'vision_mission',
    order: 5,
    vi: {
      title: 'TẦM NHÌN',
      subtitle: 'SỨ MỆNH',
      body: 'Trở thành biểu tượng bảo chứng cho chất lượng Du lịch Y tế tại Việt Nam, đưa Việt Nam trở thành điểm đến ưu tiên trên bản đồ y khoa toàn cầu.',
      extra_json: {
        video_url: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
        image_url: '/images/home/vision-mission/video-poster.webp',
        image_url_vi: '/images/home/vision-mission/video-poster.webp',
        image_url_en: '/images/home/vision-mission/video-poster.webp',
        items: [
          { title: 'VỚI KHÁCH HÀNG', body: 'Mang đến hành trình chăm sóc sức khỏe an toàn, cá nhân hóa và không rào cản địa lý.' },
          { title: 'VỚI ĐỐI TÁC', body: 'Xây dựng hệ điều hành kết nối thông minh, giúp nâng cao hiệu quả vận hành và giá trị thương hiệu.' },
          { title: 'VỚI NGÀNH', body: 'Định hình một hệ sinh thái Du lịch Y tế minh bạch, chuyên nghiệp và bền vững.' },
        ],
      },
    },
    en: {
      title: 'VISION',
      subtitle: 'MISSION',
      body: 'To become the gold standard for Medical Tourism in Vietnam — establishing the country as a priority destination on the global medical map.',
      extra_json: {
        video_url: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
        image_url: '/images/home/vision-mission/video-poster.webp',
        image_url_vi: '/images/home/vision-mission/video-poster.webp',
        image_url_en: '/images/home/vision-mission/video-poster.webp',
        items: [
          { title: 'FOR CLIENTS', body: 'Delivering a safe, premium healthcare journey with end-to-end care and zero geographical barriers.' },
          { title: 'FOR PARTNERS', body: 'Building an intelligent coordination platform that connects hospitals, resorts and technology.' },
          { title: 'FOR THE INDUSTRY', body: 'Shaping a transparent, professional, and sustainable Medical Tourism ecosystem.' },
        ],
      },
    },
  },
  {
    page_slug: 'home',
    section_key: 'benefits',
    order: 6,
    vi: {
      title: 'QUYỀN LỢI THÀNH VIÊN LIÊN MINH',
      subtitle: 'GIA TĂNG GIÁ TRỊ VÀ VƯƠN TẦM THƯƠNG HIỆU',
      body: 'Bảo chứng uy tín thương hiệu, tiếp cận nguồn khách hàng quốc tế dồi dào, tối ưu hóa quy trình điều phối.',
      extra_json: {
        items: [
          { title: 'Bảo Chứng Uy Tín Thương Hiệu', body: 'Gia nhập mạng lưới đạt chuẩn thẩm định độc lập VMTA, nâng cao niềm tin đối với du khách.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'Tiếp Cận Nguồn Khách Hàng Quốc Tế', body: 'Kết nối mạng lưới kênh phân phối lữ hành toàn cầu và hệ thống tiếp nhận bệnh nhân tập trung.', icon_url: '/images/home/values/icon-2.png' },
          { title: 'Ứng Dụng Công Nghệ Vận Hành', body: 'Sử dụng hệ điều hành điều phối hồ sơ bệnh án và lịch trình nghỉ dưỡng chuyên nghiệp.', icon_url: '/images/home/values/icon-3.png' },
          { title: 'Đồng Hành Phát Triển Bền Vững', body: 'Tham gia chuỗi sự kiện xúc tiến thương mại và hội thảo chuyển giao công nghệ y khoa.', icon_url: '/images/home/values/icon-1.png' },
        ],
      },
    },
    en: {
      title: 'ALLIANCE MEMBER BENEFITS',
      subtitle: 'MAXIMIZING VALUE & ELEVATING BRAND',
      body: 'Brand endorsement, access to international patient pool, and optimized coordination workflows.',
      extra_json: {
        items: [
          { title: 'Brand Credibility Endorsement', body: 'Join the independently vetted VMTA network to boost international patient trust.', icon_url: '/images/home/values/icon-1.png' },
          { title: 'Access International Patient Pool', body: 'Connect with global travel channels and centralized medical intake systems.', icon_url: '/images/home/values/icon-2.png' },
          { title: 'Operational Tech Integration', body: 'Utilize professional medical record and resort recovery coordination operating software.', icon_url: '/images/home/values/icon-3.png' },
          { title: 'Sustainable Growth Partnership', body: 'Participate in trade promotion events and international medical technology seminars.', icon_url: '/images/home/values/icon-1.png' },
        ],
      },
    },
  },

  // ==================== 2. TRANG GIỚI THIỆU (about) ====================
  {
    page_slug: 'about',
    section_key: 'hero',
    order: 1,
    vi: {
      title: 'KIẾN TRÚC SƯ TRƯỞNG CHO HỆ SINH THÁI DU LỊCH Y TẾ VIỆT NAM',
      subtitle: 'GIỚI THIỆU VỀ VMTA',
      body: 'VMTA là đơn vị điều phối độc lập, kết nối và chuẩn hóa các nguồn lực y tế, du lịch và công nghệ.',
      extra_json: {
        image_url: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
        image_url_vi: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
        image_url_en: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
      },
    },
    en: {
      title: 'PIONEERING VIETNAM MEDICAL TOURISM ECOSYSTEM',
      subtitle: 'ABOUT VMTA',
      body: 'VMTA acts as an independent coordinator unifying Healthcare, Tourism, and Technology resources.',
      extra_json: {
        image_url: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
        image_url_vi: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
        image_url_en: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png',
      },
    },
  },
  {
    page_slug: 'about',
    section_key: 'architect',
    order: 2,
    vi: {
      title: 'VMTA LÀ AI ?',
      subtitle: 'ĐƠN VỊ ĐIỀU PHỐI ĐỘC LẬP',
      body: 'VMTA không phải là một bệnh viện, phòng khám hay công ty lữ hành đơn lẻ. Chúng tôi là thực thể điều phối trung tâm — đơn vị định hình tiêu chuẩn, vận hành hệ thống kết nối và bảo chứng chất lượng cho toàn bộ hệ sinh thái Du lịch Y tế tại Việt Nam.',
      extra_json: {
        image_url: '/images/about/Asset-7-100.jpg',
        image_url_vi: '/images/about/Asset-7-100.jpg',
        image_url_en: '/images/about/Asset-7-100.jpg',
        items: [
          { title: 'Thực thể Điều phối Độc lập', body: 'Xây dựng tiêu chuẩn, vận hành hệ thống kết nối thông minh và quản trị chất lượng toàn bộ hành trình Du lịch Y tế.', icon_url: '/images/about/Asset-1@4x-2.png' },
          { title: 'Chuẩn hóa Tiêu chuẩn Vận hành (SLA)', body: 'Đưa các tiêu chuẩn quốc tế về y khoa, an toàn người bệnh và chất lượng dịch vụ vào quy trình vận hành đồng bộ.', icon_url: '/images/about/Asset-2@4x-4.png' },
          { title: 'Bảo chứng Uy tín & Nâng tầm Ngành', body: 'Tạo dựng niềm tin cho du khách quốc tế và khẳng định vị thế của Việt Nam trên bản đồ Du lịch Y tế toàn cầu.', icon_url: '/images/about/Asset-3@4x-3.png' },
        ],
      },
    },
    en: {
      title: 'WHO IS VMTA ?',
      subtitle: 'INDEPENDENT COORDINATOR',
      body: 'We are the central orchestrating body — defining standards, operating intelligent connection systems, and certifying quality for the entire Medical Tourism ecosystem in Vietnam.',
      extra_json: {
        image_url: '/images/about/Asset-7-100.jpg',
        image_url_vi: '/images/about/Asset-7-100.jpg',
        image_url_en: '/images/about/Asset-7-100.jpg',
        items: [
          { title: 'Independent Coordinating Entity', body: 'Establishing standards, running intelligent connection systems, and managing medical tourism quality.', icon_url: '/images/about/Asset-1@4x-2.png' },
          { title: 'Standardized Operations (SLA)', body: 'Applying international medical and safety standards across all ecosystem members.', icon_url: '/images/about/Asset-2@4x-4.png' },
          { title: 'Credibility Assurance', body: 'Building trust for international tourists and elevating Vietnam on the global medical tourism map.', icon_url: '/images/about/Asset-3@4x-3.png' },
        ],
      },
    },
  },
  {
    page_slug: 'about',
    section_key: 'how_it_works',
    order: 3,
    vi: {
      title: 'CÁCH THỨC VMTA HOẠT ĐỘNG',
      subtitle: 'QUY TRÌNH ĐIỀU PHỐI CHUYÊN NGHIỆP 4 BƯỚC',
      body: 'Quy trình tiếp nhận và vận hành liền mạch giữa Y tế và Du lịch.',
      extra_json: {
        items: [
          { title: 'Thẩm định hồ sơ từ xa', body: 'Đánh giá tình trạng y khoa và đề xuất phác đồ điều trị phù hợp trước khi khởi hành.', icon_url: '/images/about/Asset-4@4x-2-300x300.png' },
          { title: 'Thiết kế hành trình cá nhân hóa', body: 'Xây dựng lộ trình điều trị kết hợp nghỉ dưỡng phục hồi theo nhu cầu cụ thể.', icon_url: '/images/about/Asset-2@4x-3-300x300.png' },
          { title: 'Điều phối điều trị & Tiếp đón', body: 'Kết nối và vận hành dịch vụ đưa đón, khám chữa bệnh và lưu trú nghỉ dưỡng.', icon_url: '/images/about/Asset-3@4x-2-300x300.png' },
          { title: 'Theo dõi & Chăm sóc hậu điều trị', body: 'Đảm bảo quá trình phục hồi được kiểm soát và tối ưu sau khi du khách trở về.', icon_url: '/images/about/Asset-4@4x-2-300x300.png' },
        ],
      },
    },
    en: {
      title: 'HOW VMTA WORKS',
      subtitle: '4-STEP PROFESSIONAL COORDINATION PROCESS',
      body: 'Seamless intake and coordination workflow connecting Healthcare and Tourism.',
      extra_json: {
        items: [
          { title: 'Remote Case Evaluation', body: 'Assess medical conditions and propose appropriate treatment plans prior to departure.', icon_url: '/images/about/Asset-4@4x-2-300x300.png' },
          { title: 'Personalized Journey Design', body: 'Develop customized treatment and resort recovery itineraries based on patient needs.', icon_url: '/images/about/Asset-2@4x-3-300x300.png' },
          { title: 'Treatment & Reception Coordination', body: 'Orchestrate transportation, hospital admissions, and resort stay arrangements.', icon_url: '/images/about/Asset-3@4x-2-300x300.png' },
          { title: 'Post-Treatment Monitoring & Care', body: 'Ensure recovery is closely monitored and optimized after patients return home.', icon_url: '/images/about/Asset-4@4x-2-300x300.png' },
        ],
      },
    },
  },
  {
    page_slug: 'about',
    section_key: 'difference',
    order: 4,
    vi: {
      title: 'KHÁC BIỆT CỦA VMTA',
      subtitle: 'ĐỒNG BỘ NĂNG LỰC Y TẾ VÀ DU LỊCH',
      body: 'Những giá trị độc bản tạo nên vị thế hàng đầu cho VMTA.',
      extra_json: {
        bullets: [
          'Thẩm định nghiêm ngặt trước khi đưa vào hệ sinh thái',
          'Quản trị toàn bộ hành trình thay vì cung cấp dịch vụ rời rạc',
          'Ứng dụng công nghệ để theo dõi và tối ưu trải nghiệm',
          'Đồng hành cùng khách hàng và đối tác trong dài hạn',
        ],
      },
    },
    en: {
      title: 'WHAT MAKES VMTA DIFFERENT',
      subtitle: 'INTEGRATING MEDICAL & TRAVEL CAPABILITIES',
      body: 'Unique value propositions establishing VMTA’s leading position.',
      extra_json: {
        bullets: [
          'Strict appraisal before being included in the ecosystem',
          'Managing the entire journey instead of providing fragmented services',
          'Applying technology to monitor and optimize the experience',
          'Long-term companionship with customers and partners',
        ],
      },
    },
  },
];

async function main() {
  console.log('Seeding 100% COMPLETE default CMS sections with VALID IMAGES into dev.db...');

  for (const item of defaultSections) {
    await prisma.$executeRawUnsafe(
      `INSERT OR IGNORE INTO CmsSection (page_slug, section_key, "order") VALUES (?, ?, ?);`,
      item.page_slug,
      item.section_key,
      item.order
    );

    const rows: any = await prisma.$queryRawUnsafe(
      `SELECT id FROM CmsSection WHERE page_slug = ? AND section_key = ? LIMIT 1;`,
      item.page_slug,
      item.section_key
    );

    if (rows && rows.length > 0) {
      const sectionId = rows[0].id;
      const extraVi = (item.vi as any).extra_json ? JSON.stringify((item.vi as any).extra_json) : null;
      const extraEn = (item.en as any).extra_json ? JSON.stringify((item.en as any).extra_json) : null;

      await prisma.$executeRawUnsafe(
        `INSERT INTO CmsSectionTranslation (section_id, locale, title, subtitle, body, extra_json)
         VALUES (?, 'vi', ?, ?, ?, ?)
         ON CONFLICT(section_id, locale) DO UPDATE SET title=?, subtitle=?, body=?, extra_json=?;`,
        sectionId, item.vi.title, item.vi.subtitle, item.vi.body, extraVi,
        item.vi.title, item.vi.subtitle, item.vi.body, extraVi
      );

      await prisma.$executeRawUnsafe(
        `INSERT INTO CmsSectionTranslation (section_id, locale, title, subtitle, body, extra_json)
         VALUES (?, 'en', ?, ?, ?, ?)
         ON CONFLICT(section_id, locale) DO UPDATE SET title=?, subtitle=?, body=?, extra_json=?;`,
        sectionId, item.en.title, item.en.subtitle, item.en.body, extraEn,
        item.en.title, item.en.subtitle, item.en.body, extraEn
      );

      console.log(`Seeded ${item.page_slug}/${item.section_key}`);
    }
  }

  console.log('SUCCESSFULLY RE-SEEDED ALL SECTIONS WITH VALID IMAGES!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
