import React from 'react';
import Link from 'next/link';

export default async function MedicalAchievementsPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  const stats = isVi
    ? [
        { icon: '/images/medical-achievements/stat-heart.png', value: '100+', label: 'Ca ghép tạng thành công' },
        { icon: '/images/medical-achievements/stat-hospital.png', value: '50+', label: 'Bệnh viện & Trung tâm JCI' },
        { icon: '/images/medical-achievements/stat-people.png', value: '99.8%', label: 'Tỷ lệ thành công' },
      ]
    : [
        { icon: '/images/medical-achievements/stat-heart.png', value: '100+', label: 'Successful Organ Transplants' },
        { icon: '/images/medical-achievements/stat-hospital.png', value: '50+', label: 'JCI Accredited Centers' },
        { icon: '/images/medical-achievements/stat-people.png', value: '99.8%', label: 'Patient Success Rate' },
      ];

  const capabilities = isVi
    ? [
        {
          title: 'ĐỘI NGŨ BÁC SĨ CHUYÊN GIA',
          body: 'Các giáo sư, bác sĩ đầu ngành tu nghiệp quốc tế, làm chủ các kỹ thuật y khoa phức tạp nhất.',
          icon: '/images/medical-achievements/icon-doctors.png',
        },
        {
          title: 'KỸ THUẬT Y KHOA TIÊN TIẾN',
          body: 'Ứng dụng phẫu thuật Robot, can thiệp mạch máu và liệu pháp tế bào gốc trong điều trị.',
          icon: '/images/medical-achievements/icon-technique.png',
        },
        {
          title: 'CÔNG NGHỆ CHẨN ĐOÁN HIỆN ĐẠI',
          body: 'Trang bị hệ thống MRI 3.0T, PET/CT và phòng mổ vô khuẩn tiêu chuẩn quốc tế.',
          icon: '/images/medical-achievements/icon-modern-tech.png',
        },
        {
          title: 'ĐỊA ĐỂM CHỮA LÀNH TỰ NHIÊN',
          body: 'Kết hợp điều trị với hệ thống resort nghỉ dưỡng thiên nhiên giúp phục hồi toàn diện.',
          icon: '/images/medical-achievements/icon-trust-destination.png',
        },
      ]
    : [
        {
          title: 'EXPERT MEDICAL TEAM',
          body: 'Leading professors and doctors trained internationally, mastering complex procedures.',
          icon: '/images/medical-achievements/icon-doctors.png',
        },
        {
          title: 'ADVANCED TECHNIQUES',
          body: 'Applying Robotic surgery, vascular intervention, and stem cell therapy.',
          icon: '/images/medical-achievements/icon-technique.png',
        },
        {
          title: 'MODERN DIAGNOSTICS',
          body: 'Equipped with 3.0T MRI, PET/CT scanners and international cleanroom operating suites.',
          icon: '/images/medical-achievements/icon-modern-tech.png',
        },
        {
          title: 'NATURAL HEALING DESTINATION',
          body: 'Integrating clinical care with luxury resort recovery environments.',
          icon: '/images/medical-achievements/icon-trust-destination.png',
        },
      ];

  const assurances = isVi
    ? [
        {
          title: 'LẬP KẾ HOẠCH CHI TIẾT',
          body: 'Thẩm định hồ sơ y khoa từ xa và tư vấn phác đồ trước khi khởi hành.',
          icon: '/images/medical-achievements/icon-plan.png',
        },
        {
          title: 'LỰA CHỌN BỆNH VIỆN CHUẨN JCI',
          body: 'Kết nối đến các trung tâm y khoa hàng đầu đạt chứng nhận an toàn quốc tế.',
          icon: '/images/medical-achievements/icon-select.png',
        },
        {
          title: 'ĐIỀU PHỐI HÀNH TRÌNH 24/7',
          body: 'Đón tiễn sân bay, xe riêng và trợ lý y tế song ngữ đồng hành.',
          icon: '/images/medical-achievements/icon-coordinate.png',
        },
        {
          title: 'ĐẢM BẢO CHẤT LƯỢNG SỨC KHỎE',
          body: 'Tái khám và theo dõi chỉ số phục hồi theo chuẩn SLA nghiêm ngặt.',
          icon: '/images/medical-achievements/icon-quality.png',
        },
      ]
    : [
        {
          title: 'DETAILED PLANNING',
          body: 'Remote case evaluation and consultation before departure.',
          icon: '/images/medical-achievements/icon-plan.png',
        },
        {
          title: 'JCI HOSPITAL SELECTION',
          body: 'Direct access to accredited medical centers ensuring global patient safety.',
          icon: '/images/medical-achievements/icon-select.png',
        },
        {
          title: '24/7 ITINERARY COORDINATION',
          body: 'Airport transfer, private transport, and bilingual medical assistant.',
          icon: '/images/medical-achievements/icon-coordinate.png',
        },
        {
          title: 'HEALTH QUALITY ASSURANCE',
          body: 'Follow-up monitoring and recovery tracking under strict SLA standards.',
          icon: '/images/medical-achievements/icon-quality.png',
        },
      ];

  return (
    <div className="space-y-0 pb-16 bg-white font-utm-helve">
      {/* 1. Hero */}
      <section className="relative overflow-hidden bg-white min-h-[500px] flex items-center py-16">
        <div className="absolute inset-0">
          <img
            src="/images/medical-achievements/hero-operating-room.jpg"
            className="w-full h-full object-cover opacity-20"
            alt=""
          />
        </div>
        <div className="relative max-w-7xl mx-auto px-4 z-10 w-full">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div className="lg:col-span-7 space-y-4">
              <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold uppercase text-[#0b7f7c] leading-tight">
                {isVi ? 'THÀNH TỰU Y KHOA TIÊU BIỂU TẠI VIỆT NAM' : 'SIGNIFICANT MEDICAL ACHIEVEMENTS IN VIETNAM'}
              </h1>
              <p className="text-sm md:text-base font-bold uppercase text-[#d31e45]">
                {isVi ? 'VIỆT NAM – ĐIỂM ĐẾN MỚI CỦA Y HỌC THẾ GIỚI' : 'VIETNAM — NEW GLOBAL MEDICAL DESTINATION'}
              </p>
              <p className="text-sm md:text-base text-slate-700 leading-relaxed text-justify">
                {isVi
                  ? 'Việt Nam đang từng bước khẳng định vị thế trên bản đồ y khoa thế giới với nhiều ca ghép tạng phức tạp, can thiệp tim mạch bẩm sinh và cấy ghép nha khoa chuẩn xác hàng đầu.'
                  : 'Vietnam is establishing its position on the global medical map with complex organ transplants and precision procedures.'}
              </p>
            </div>

            <div className="lg:col-span-5 bg-white p-6 rounded-3xl shadow-2xl border border-teal-100 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-6">
              {stats.map((stat, i) => (
                <div key={i} className="flex items-center gap-4">
                  <img src={stat.icon} alt="" className="w-14 h-14 object-contain shrink-0" />
                  <div>
                    <span className="block font-bold text-2xl text-[#d31e45]">{stat.value}</span>
                    <span className="text-xs text-slate-600 font-semibold">{stat.label}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* 2. Medical Case Highlight */}
      <section className="py-16 bg-slate-50 border-t border-slate-200/60">
        <div className="max-w-7xl mx-auto px-4">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div className="space-y-4">
              <span className="text-xs font-bold uppercase tracking-widest text-[#d31e45]">
                {isVi ? 'CA LÂM SÀNG ĐẶC BIỆT' : 'FEATURED CLINICAL CASE'}
              </span>
              <h2 className="text-2xl md:text-4xl font-extrabold uppercase text-[#0b7f7c]">
                {isVi ? 'GHÉP ĐỒNG THỜI TIM - PHỔI' : 'SIMULTANEOUS HEART-LUNG TRANSPLANT'}
              </h2>
              <p className="text-sm font-bold uppercase text-slate-600">
                {isVi ? 'KỸ THUẬT NGHẸT THỞ ĐẦU TIÊN TẠI VIỆT NAM' : 'FIRST BREAKTHROUGH PROCEDURE IN VIETNAM'}
              </p>
              <p className="text-sm text-slate-700 leading-relaxed text-justify">
                {isVi
                  ? 'Kíp mổ quy tụ hơn 50 chuyên gia hàng đầu từ các Bệnh viện Tuyến trung ương đã thực hiện thành công ca phẫu thuật ghép đồng thời cả tim và phổi cho bệnh nhân suy tạng giai đoạn cuối.'
                  : 'A surgical team of over 50 leading specialists successfully performed simultaneous heart and lung transplant.'}
              </p>

              <div className="pt-4 flex flex-wrap gap-4">
                <Link
                  href={`/${locale}/lien-he`}
                  className="rounded-md border border-[#0b7f7c] bg-white px-6 py-2.5 text-xs font-bold uppercase text-[#0b7f7c] hover:bg-[#0b7f7c] hover:text-white transition"
                >
                  {isVi ? 'XEM CHI TIẾT' : 'VIEW DETAILS'}
                </Link>
                <Link
                  href={`/${locale}/lien-he`}
                  className="rounded-md bg-[#d31e45] px-6 py-2.5 text-xs font-bold uppercase text-white hover:bg-[#b01838] transition shadow-md"
                >
                  {isVi ? 'NHẬN TƯ VẤN MIỄN PHÍ' : 'FREE CONSULTATION'}
                </Link>
              </div>
            </div>

            <div>
              <img
                src="/images/medical-achievements/heart-lung-transplant.jpg"
                alt="Heart Lung Transplant"
                className="w-full rounded-3xl shadow-xl object-cover aspect-video"
              />
            </div>
          </div>
        </div>
      </section>

      {/* 3. Capabilities */}
      <section className="py-16 md:py-24 bg-white">
        <div className="max-w-7xl mx-auto px-4">
          <h2 className="text-3xl md:text-4xl font-extrabold uppercase text-center text-[#0b7f7c] mb-12">
            {isVi ? 'VIỆT NAM – NĂNG LỰC Y HỌC ĐANG VƯƠN TẦM QUỐC TẾ' : 'VIETNAM — MEDICAL CAPABILITY RISING GLOBALLY'}
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {capabilities.map((item, i) => (
              <div key={i} className="text-center p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-md transition">
                <img src={item.icon} alt="" className="w-20 h-20 mx-auto mb-4 object-contain" />
                <h3 className="font-bold text-sm text-[#d31e45] uppercase mb-2">{item.title}</h3>
                <p className="text-xs text-slate-600 leading-relaxed text-justify">{item.body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 4. Assurance */}
      <section className="py-16 md:py-24 bg-[#0b7f7c] text-white relative overflow-hidden">
        <div className="max-w-7xl mx-auto px-4 relative z-10">
          <h2 className="text-3xl md:text-4xl font-extrabold uppercase mb-6 text-white">
            {isVi ? 'VMTA – BẢO CHỨNG CHO HÀNH TRÌNH Y TẾ AN TOÀN' : 'VMTA — ASSURANCE FOR A SAFE MEDICAL JOURNEY'}
          </h2>
          <p className="text-sm md:text-base text-white/90 italic mb-10 max-w-3xl">
            {isVi
              ? 'Lần đầu tiên tại Việt Nam, bệnh nhi 12 tuổi được thay khớp háng toàn phần bằng công nghệ in 3D và thiết bị định vị PSI thiết kế riêng biệt.'
              : 'For the first time in Vietnam, a 12-year-old patient underwent total hip replacement using 3D printing technology with custom PSI navigation.'}
          </p>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {assurances.map((item, i) => (
              <div key={i} className="flex items-start gap-4 p-5 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20">
                <img src={item.icon} alt="" className="w-12 h-12 object-contain shrink-0" />
                <div>
                  <h3 className="font-bold text-xs uppercase text-amber-300 mb-1">{item.title}</h3>
                  <p className="text-xs text-white/90 leading-relaxed text-justify">{item.body}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
