import React from 'react';
import Link from 'next/link';

export default async function DentalSpecialtyPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  const strengths = isVi
    ? [
        {
          title: 'Implant (Trồng răng)',
          image: '/images/specialties/nha-khoa/implant.jpg',
          bullets: [
            'Giải pháp phục hồi răng mất bền vững',
            'Đảm bảo chức năng ăn nhai và thẩm mỹ',
            'Ứng dụng công nghệ định vị chính xác 3D Safe-Tech',
          ],
        },
        {
          title: 'Veneer (Dán sứ thẩm mỹ)',
          image: '/images/specialties/nha-khoa/veneer.jpg',
          bullets: [
            'Thiết kế nụ cười hài hòa, tự nhiên chuẩn tỷ lệ vàng',
            'Ít xâm lấn, không mài nhỏ răng, thời gian nhanh',
            'Sứ thủy tinh cao cấp bảo hành 15-20 năm',
          ],
        },
        {
          title: 'Chỉnh nha (Niềng răng)',
          image: '/images/specialties/nha-khoa/orthodontics.jpg',
          bullets: [
            'Điều chỉnh sai lệch khớp cắn hiệu quả',
            'Lựa chọn linh hoạt (Mắc cài kim loại, sứ, Invisalign)',
            'Lộ trình rõ ràng với mô phỏng 3D trước điều trị',
          ],
        },
      ]
    : [
        {
          title: 'Dental Implant',
          image: '/images/specialties/nha-khoa/implant.jpg',
          bullets: [
            'Durable solution for missing teeth',
            'Restores full chewing function and aesthetics',
            'Computer-guided 3D precision placement',
          ],
        },
        {
          title: 'Veneer Porcelain',
          image: '/images/specialties/nha-khoa/veneer.jpg',
          bullets: [
            'Harmonious natural smile design',
            'Minimally invasive with fast turnaround',
            'High-grade porcelain backed by 15-year warranty',
          ],
        },
        {
          title: 'Orthodontics & Invisalign',
          image: '/images/specialties/nha-khoa/orthodontics.jpg',
          bullets: [
            'Corrects bite misalignment and crowding',
            'Flexible options (braces, Invisalign clear aligners)',
            '3D simulated treatment timeline',
          ],
        },
      ];

  const hospitals = isVi
    ? [
        {
          name: 'Bệnh viện Răng Hàm Mặt Trung ương TP.HCM',
          bullets: [
            'Đơn vị tuyến trung ương đầu ngành về Nha khoa tại Việt Nam',
            'Đội ngũ giáo sư, tiến sĩ, bác sĩ chuyên sâu tu nghiệp Châu Âu',
            'Trang thiết bị phòng mổ vô khuẩn đạt tiêu chuẩn quốc tế',
          ],
        },
        {
          name: 'Bệnh viện Răng Hàm Mặt Trung ương Hà Nội',
          bullets: [
            'Trung tâm tiếp nhận và điều trị các ca lâm sàng nha khoa phức tạp',
            'Ứng dụng công nghệ quét hàm 3D Scan & CAD/CAM tại chỗ',
            'Quy trình kiểm soát nhiễm khuẩn nghiêm ngặt theo JCI',
          ],
        },
      ]
    : [
        {
          name: 'Central Hospital of Odonto-Stomatology Ho Chi Minh City',
          bullets: [
            'Premier national dental institution in Vietnam',
            'Specialist medical team trained in Europe & US',
            'International standard sterile operating rooms',
          ],
        },
        {
          name: 'Central Hospital of Odonto-Stomatology Hanoi',
          bullets: [
            'National referral center for complex dental reconstructions',
            'In-house 3D Scan & CAD/CAM digital dental lab',
            'Strict JCI infection control protocols',
          ],
        },
      ];

  return (
    <article className="vmta-specialty-detail bg-white font-utm-helve text-[#4a4a4a] space-y-0 pb-16">
      {/* 1. Hero */}
      <section
        className="relative min-h-[300px] bg-white bg-cover bg-center pt-28 pb-12 overflow-hidden"
        style={{ backgroundImage: "url('/images/specialties/nha-khoa/hero-bg.png')" }}
      >
        <div className="absolute inset-0 bg-white/70"></div>
        <div className="relative max-w-7xl mx-auto px-4 z-10">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold uppercase text-[#0b7f7c]">
            {isVi ? 'NHA KHOA THẨM MỸ & IMPLANT' : 'AESTHETIC DENTISTRY & IMPLANT'}
          </h1>
          <nav className="mt-3 text-sm text-[#0b7f7c] font-semibold" aria-label="breadcrumb">
            <Link href={`/${locale}`} className="hover:underline">
              {isVi ? 'Trang chủ' : 'Home'}
            </Link>
            <span className="mx-2">/</span>
            <Link href={`/${locale}/chuyen-khoa`} className="hover:underline">
              {isVi ? 'Chuyên khoa' : 'Specialties'}
            </Link>
            <span className="mx-2">/</span>
            <span>{isVi ? 'Nha khoa' : 'Dentistry'}</span>
          </nav>
        </div>
      </section>

      {/* 2. Intro */}
      <section
        className="py-16 md:py-24 bg-cover bg-center border-b border-slate-100"
        style={{ backgroundImage: "url('/images/specialties/nha-khoa/intro-bg.jpg')" }}
      >
        <div className="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          <div className="lg:col-span-7 space-y-4">
            <h2 className="text-2xl md:text-4xl font-extrabold uppercase text-[#0b7f7c] leading-tight">
              {isVi
                ? 'VIỆT NAM – ĐIỂM ĐẾN MỚI CỦA NHA KHOA CHẤT LƯỢNG CAO'
                : 'VIETNAM – A NEW DESTINATION FOR HIGH-QUALITY DENTISTRY'}
            </h2>
            <p className="text-base font-bold text-[#d31e45] uppercase">
              {isVi
                ? 'Nâng tầm nụ cười – Tối ưu sức khỏe răng miệng'
                : 'Elevating smiles – Optimizing oral health'}
            </p>
            <div className="text-sm md:text-base text-slate-700 leading-relaxed text-justify space-y-3">
              <p>
                {isVi
                  ? 'Trong những năm gần đây, Việt Nam đang nổi lên như một trung tâm nha khoa tại châu Á, nơi hội tụ giữa chuyên môn y khoa, công nghệ hiện đại và chi phí hợp lý.'
                  : 'Vietnam is emerging as a dental hub in Asia, combining medical expertise, modern technology, and competitive treatment costs.'}
              </p>
              <ul className="list-disc pl-5 space-y-1.5 font-medium">
                <li>{isVi ? 'Đội ngũ bác sĩ được đào tạo quốc tế' : 'Internationally trained dental teams'}</li>
                <li>{isVi ? 'Công nghệ điều trị tiên tiến (3D scan, CAD/CAM)' : 'Advanced treatment technology (3D scan, CAD/CAM)'}</li>
                <li>{isVi ? 'Vật liệu nha khoa cao cấp đạt chuẩn toàn cầu' : 'Premium dental materials meeting global standards'}</li>
              </ul>
              <p>
                {isVi
                  ? 'Không chỉ dừng lại ở điều trị, Việt Nam còn mang đến trải nghiệm kết hợp nghỉ dưỡng – giúp khách hàng phục hồi nhanh hơn và thoải mái hơn.'
                  : 'Beyond treatment, Vietnam offers a care journey combined with restorative travel for faster, more comfortable recovery.'}
              </p>
            </div>
          </div>

          <div className="lg:col-span-5">
            <img
              src="/images/specialties/nha-khoa/intro-dental.jpg"
              alt="Nha khoa VMTA"
              className="w-full rounded-3xl shadow-xl object-cover aspect-[4/3]"
            />
          </div>
        </div>
      </section>

      {/* 3. Strengths */}
      <section className="py-16 md:py-24 bg-white border-b border-slate-100">
        <div className="max-w-7xl mx-auto px-4">
          <h2 className="text-3xl md:text-4xl font-extrabold uppercase text-center text-[#0b7f7c] mb-12">
            {isVi ? 'THẾ MẠNH NHA KHOA TẠI VIỆT NAM' : 'DENTAL STRENGTHS IN VIETNAM'}
          </h2>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {strengths.map((item, i) => (
              <div key={i} className="bg-slate-50 p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                  <img
                    src={item.image}
                    alt={item.title}
                    className="w-full rounded-2xl object-cover aspect-[4/3] mb-5 shadow-md"
                  />
                  <h3 className="text-lg font-bold text-[#d31e45] uppercase mb-3">{item.title}</h3>
                  <ul className="space-y-2 text-xs text-slate-700 list-disc pl-4 font-medium">
                    {item.bullets.map((bullet, bIdx) => (
                      <li key={bIdx}>{bullet}</li>
                    ))}
                  </ul>
                </div>

                <div className="pt-6">
                  <Link
                    href={`/${locale}/lien-he`}
                    className="inline-block w-full text-center rounded-xl bg-[#0b7f7c] py-2.5 text-xs font-bold uppercase text-white hover:opacity-90 transition"
                  >
                    {isVi ? 'NHẬN BÁO GIÁ TƯ VẤN' : 'GET QUOTATION'}
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 4. Hospitals */}
      <section className="py-16 md:py-24 bg-white">
        <div className="max-w-7xl mx-auto px-4">
          <h2 className="text-3xl md:text-4xl font-extrabold uppercase text-center text-[#0b7f7c] mb-12">
            {isVi ? 'CÁC BỆNH VIỆN NHA KHOA HÀNG ĐẦU VIỆT NAM' : 'LEADING DENTAL HOSPITALS IN VIETNAM'}
          </h2>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {hospitals.map((h, i) => (
              <div key={i} className="bg-slate-50 p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <h3 className="text-xl font-bold uppercase text-[#0b7f7c]">{h.name}</h3>
                <ul className="space-y-2 text-sm text-slate-700 list-disc pl-5 font-medium">
                  {h.bullets.map((bullet, bIdx) => (
                    <li key={bIdx}>{bullet}</li>
                  ))}
                </ul>
                <div className="pt-4 flex gap-3">
                  <Link
                    href={`/${locale}/lien-he`}
                    className="rounded-xl bg-[#d31e45] px-5 py-2.5 text-xs font-bold uppercase text-white hover:bg-[#b01838] transition"
                  >
                    {isVi ? 'Đặt lịch ngay' : 'Book appointment'}
                  </Link>
                  <Link
                    href={`/${locale}/lien-he`}
                    className="rounded-xl border border-[#0b7f7c] bg-white px-5 py-2.5 text-xs font-bold uppercase text-[#0b7f7c] hover:bg-[#0b7f7c] hover:text-white transition"
                  >
                    {isVi ? 'Tìm hiểu thêm' : 'Learn more'}
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </article>
  );
}
