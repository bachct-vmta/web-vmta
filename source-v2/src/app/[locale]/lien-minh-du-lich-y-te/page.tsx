import React from 'react';
import Link from 'next/link';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export default async function AlliancePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  let members: any[] = [];
  try {
    members = await prisma.$queryRawUnsafe(`SELECT * FROM AllianceMember WHERE is_active = 1 ORDER BY group_id ASC, id DESC;`);
  } catch (err) {
    console.error('Error fetching alliance members for web:', err);
  }

  const MEMBER_GROUPS = [
    { id: 1, name_vi: 'Bệnh viện & Cơ sở Y tế Đạt Chuẩn', name_en: 'Accredited Hospitals & Clinics', icon: '🏥' },
    { id: 2, name_vi: 'Lữ hành & Du lịch', name_en: 'Travel & Tourism Operators', icon: '✈️' },
    { id: 3, name_vi: 'Tài chính & Bảo hiểm', name_en: 'Finance & Medical Travel Insurance', icon: '🛡️' },
    { id: 4, name_vi: 'Các đơn vị khác', name_en: 'Other Supporting Partners', icon: '🌐' },
  ];

  return (
    <div id="content" className="content-area scroll-smooth bg-white font-utm-helve space-y-0 pb-20">
      {/* Hero Header */}
      <section id="section-alliance-hero" className="relative min-h-[360px] flex items-center justify-center overflow-hidden bg-white py-16">
        <div className="absolute inset-0">
          <img
            src="/images/alliance/hero.png"
            className="w-full h-full object-cover opacity-20"
            alt=""
          />
        </div>
        <div className="relative z-10 w-full max-w-7xl mx-auto px-4 py-8 text-center text-[#0b7f7c]">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold uppercase leading-tight max-w-5xl mx-auto">
            {isVi ? 'MẠNG LƯỚI LIÊN MINH DU LỊCH Y TẾ VIỆT NAM (VMTA)' : 'VIETNAM MEDICAL TOURISM ALLIANCE MEMBERS DIRECTORY'}
          </h1>
          <p className="mt-4 text-base md:text-xl font-bold uppercase max-w-3xl mx-auto">
            {isVi ? 'DANH BẠ CHÍNH THỨC CÁC ĐƠN VỊ THÀNH VIÊN ĐẠT CHUẨN THẨM ĐỊNH' : 'OFFICIAL DIRECTORY OF VETTES & ACCREDITED ALLIANCE MEMBERS'}
          </p>
          <div className="mt-6">
            <Link
              href={`/${locale}/lien-he`}
              className="inline-block rounded-xl bg-[#d31e45] px-8 py-3.5 font-bold uppercase text-white hover:bg-[#b01838] transition shadow-lg text-xs"
            >
              {isVi ? 'ĐĂNG KÝ GIA NHẬP LIÊN MINH' : 'APPLY TO JOIN ALLIANCE'}
            </Link>
          </div>
        </div>
      </section>

      {/* 4 Groups Sections */}
      <section className="max-w-7xl mx-auto px-4 py-12 space-y-16">
        {MEMBER_GROUPS.map((group) => {
          const groupMembers = members.filter((m) => Number(m.group_id) === Number(group.id));
          return (
            <div key={group.id} className="space-y-6">
              <div className="border-b-2 border-[#0b7f7c] pb-3 flex items-center justify-between">
                <h2 className="text-xl md:text-2xl font-extrabold uppercase text-[#0b7f7c] flex items-center gap-2">
                  <span>{group.icon}</span>
                  <span>Nhóm {group.id}: {isVi ? group.name_vi : group.name_en}</span>
                </h2>
                <span className="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                  {groupMembers.length} {isVi ? 'thành viên' : 'members'}
                </span>
              </div>

              {groupMembers.length === 0 ? (
                <p className="text-xs text-slate-400 italic">Đang cập nhật danh sách hội viên cho nhóm này...</p>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {groupMembers.map((member) => (
                    <div
                      key={member.id}
                      className="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 shadow-sm hover:shadow-xl transition hover:-translate-y-1 flex flex-col justify-between"
                    >
                      <div className="space-y-3">
                        <div className="flex justify-between items-center">
                          <span className="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300">
                            🏅 Hội viên {member.badge}
                          </span>
                          <span className="text-[10px] text-teal-700 font-bold uppercase bg-teal-50 px-2 py-0.5 rounded">
                            VMTA ACCREDITED
                          </span>
                        </div>

                        <div className="flex items-center gap-3 pt-2">
                          {member.logo_url && (
                            <img
                              src={member.logo_url}
                              alt=""
                              className="h-12 w-14 object-contain bg-white rounded-xl border border-slate-200 p-1 shrink-0 shadow-xs"
                            />
                          )}
                          <h3 className="font-extrabold text-sm text-[#0b7f7c] leading-snug">
                            {isVi ? member.name_vi : member.name_en}
                          </h3>
                        </div>

                        <p className="text-xs text-slate-600 leading-relaxed">
                          {isVi ? member.description_vi : member.description_en}
                        </p>
                      </div>

                      <div className="pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                        <p>📍 <strong>{isVi ? 'Địa chỉ:' : 'Address:'}</strong> {member.address || 'Chưa cập nhật'}</p>
                        {member.email && <p>✉️ <strong>Email:</strong> {member.email}</p>}
                        {member.website && (
                          <p>🌐 <a href={member.website} target="_blank" rel="noreferrer" className="text-[#0b7f7c] font-bold hover:underline">
                            Website chính thức ➔
                          </a></p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          );
        })}
      </section>
    </div>
  );
}
