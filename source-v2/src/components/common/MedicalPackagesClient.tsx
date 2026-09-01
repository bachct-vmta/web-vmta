'use client';

import React, { useState } from 'react';

interface MedicalPackagesClientProps {
  categories: any[];
  packages: any[];
  allianceMembers: any[];
  locale: string;
}

export const MedicalPackagesClient: React.FC<MedicalPackagesClientProps> = ({
  categories,
  packages,
  allianceMembers,
  locale,
}) => {
  const isVi = locale === 'vi';
  const [selectedPkg, setSelectedPkg] = useState<any | null>(null);

  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [note, setNote] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const medicalCats = categories.filter((c) => c.type === 'medical');
  const tourismCats = categories.filter((c) => c.type === 'tourism');

  const handleOpenBooking = (pkg: any) => {
    setSelectedPkg(pkg);
    setNote(isVi ? `Tôi muốn nhận tư vấn chi tiết & báo giá cho gói: "${pkg.title_vi}"` : `I would like consultation & quote for package: "${pkg.title_en}"`);
    setMessage(null);
  };

  const handleBookingSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!fullName || !email || !selectedPkg) return;
    setSubmitting(true);
    setMessage(null);

    try {
      const res = await fetch('/api/admin/inquiries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          full_name: fullName,
          phone,
          email,
          organization: `Đăng ký gói: ${selectedPkg.title_vi}`,
          content: `${note}\n[Cơ sở: ${selectedPkg.facility_name || 'Hệ thống VMTA'}]`,
        }),
      });

      if (res.ok) {
        setMessage({
          type: 'success',
          text: isVi
            ? 'Đăng ký thành công! Chuyên viên tư vấn VMTA sẽ liên hệ với bạn trong 24h.'
            : 'Registration successful! A VMTA consultant will contact you within 24h.',
        });
        setTimeout(() => {
          setSelectedPkg(null);
          setFullName('');
          setPhone('');
          setEmail('');
          setNote('');
        }, 2000);
      } else {
        setMessage({
          type: 'error',
          text: isVi ? 'Lỗi đăng ký. Vui lòng thử lại sau.' : 'Registration failed. Please try again.',
        });
      }
    } catch {
      setMessage({
        type: 'error',
        text: isVi ? 'Lỗi kết nối. Vui lòng thử lại.' : 'Connection error.',
      });
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="space-y-12">
      {/* 5 Medical Categories */}
      <section className="max-w-7xl mx-auto px-4 pt-12 pb-6">
        <div className="text-center mb-8">
          <span className="text-xs font-bold uppercase tracking-wider text-[#d31e45] bg-red-50 px-3 py-1 rounded-full border border-red-100">
            🩺 {isVi ? 'DANH MỤC Y KHOA CHUYÊN SÂU' : 'MEDICAL SPECIALTIES'}
          </span>
          <h2 className="text-2xl md:text-3xl font-extrabold uppercase text-[#0b7f7c] mt-2">
            {isVi ? '5 Nhóm Chuyên Khoa Y Tế Đạt Chuẩn Thẩm Định' : '5 Accredited Medical Specialty Groups'}
          </h2>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
          {medicalCats.map((cat) => (
            <div
              key={cat.category_key}
              className="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-center flex flex-col items-center justify-center space-y-3 hover:shadow-md transition hover:-translate-y-1"
            >
              <span className="text-4xl">{cat.icon}</span>
              <h3 className="font-bold text-xs uppercase text-[#0b7f7c]">
                {isVi ? cat.title_vi : cat.title_en}
              </h3>
            </div>
          ))}
        </div>
      </section>

      {/* 5 Tourism Categories */}
      <section className="max-w-7xl mx-auto px-4 py-6">
        <div className="text-center mb-8">
          <span className="text-xs font-bold uppercase tracking-wider text-sky-700 bg-sky-50 px-3 py-1 rounded-full border border-sky-100">
            ✈️ {isVi ? 'DANH MỤC LƯU TRÚ & DU LỊCH' : 'TOURISM & ACCOMMODATION'}
          </span>
          <h2 className="text-2xl md:text-3xl font-extrabold uppercase text-[#0b7f7c] mt-2">
            {isVi ? '5 Nhóm Dịch Vụ Du Lịch & Nghỉ Dưỡng Đi Kèm' : '5 Tourism & Hospitality Service Groups'}
          </h2>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
          {tourismCats.map((cat) => (
            <div
              key={cat.category_key}
              className="bg-sky-50/50 border border-sky-200/80 rounded-2xl p-5 text-center flex flex-col items-center justify-center space-y-3 hover:shadow-md transition hover:-translate-y-1"
            >
              <span className="text-4xl">{cat.icon}</span>
              <h3 className="font-bold text-xs uppercase text-sky-900">
                {isVi ? cat.title_vi : cat.title_en}
              </h3>
            </div>
          ))}
        </div>
      </section>

      {/* Medical Packages Section */}
      <section className="max-w-7xl mx-auto px-4 pt-6">
        <div className="text-center mb-10">
          <span className="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
            💎 {isVi ? 'GÓI NGHỈ DƯỠNG Y TẾ THAM KHẢO' : 'FEATURED MEDICAL PACKAGES'}
          </span>
          <h2 className="text-2xl md:text-4xl font-extrabold uppercase text-[#0b7f7c] mt-2">
            {isVi ? 'Các Gói Trải Nghiệm Khám Chữa Bệnh & Phục Hồi' : 'SLA Accredited Healthcare & Recovery Packages'}
          </h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {packages.map((pkg) => (
            <div key={pkg.id} className="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden flex flex-col justify-between hover:shadow-xl transition">
              {pkg.image_url && (
                <div className="relative h-56 w-full overflow-hidden">
                  <img src={pkg.image_url} alt="" className="w-full h-full object-cover hover:scale-105 transition duration-500" />
                  <div className="absolute top-4 right-4 bg-[#d31e45] text-white font-extrabold text-xs px-3.5 py-1.5 rounded-full shadow-md">
                    {isVi ? pkg.price_vi : pkg.price_en}
                  </div>
                </div>
              )}

              <div className="p-6 space-y-4 flex-1 flex flex-col justify-between">
                <div className="space-y-3">
                  <div className="flex items-center gap-2">
                    <span className="text-[11px] font-bold uppercase px-2.5 py-1 bg-teal-50 text-[#0b7f7c] rounded-md border border-teal-200">
                      ⏱️ {isVi ? pkg.duration_vi : pkg.duration_en}
                    </span>
                  </div>
                  <h3 className="font-extrabold text-lg text-[#0b7f7c] leading-tight">
                    {isVi ? pkg.title_vi : pkg.title_en}
                  </h3>
                  <p className="text-xs text-slate-600 italic">
                    {isVi ? pkg.subtitle_vi : pkg.subtitle_en}
                  </p>

                  <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs">
                    <p className="font-bold text-[#0b7f7c]">
                      🏥 {isVi ? 'Cơ sở y tế & Lưu trú:' : 'Medical & Resort Facility:'} {pkg.facility_name}
                    </p>
                    <p className="text-slate-600">
                      📋 <strong>{isVi ? 'Lịch trình tham khảo:' : 'Itinerary overview:'}</strong> {isVi ? pkg.itinerary_vi : pkg.itinerary_en}
                    </p>
                  </div>
                </div>

                <div className="pt-4 border-t border-slate-100 flex justify-end">
                  <button
                    onClick={() => handleOpenBooking(pkg)}
                    className="inline-flex items-center gap-2 px-6 py-3 bg-[#0b7f7c] text-white text-xs font-bold uppercase rounded-xl hover:bg-[#086a67] transition shadow-md cursor-pointer"
                  >
                    <span>{isVi ? 'ĐĂNG KÝ TƯ VẤN GÓI NÀY' : 'BOOK CONSULTATION'}</span>
                    <span>➔</span>
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Package Booking Modal */}
      {selectedPkg && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 font-utm-helve">
          <div className="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <div>
                <span className="text-[10px] font-bold uppercase px-2 py-0.5 bg-red-50 text-[#d31e45] rounded">
                  {isVi ? 'Đăng Ký Tư Vấn Gói' : 'Package Booking'}
                </span>
                <h3 className="font-extrabold text-sm text-[#0b7f7c] mt-1 leading-tight">
                  {isVi ? selectedPkg.title_vi : selectedPkg.title_en}
                </h3>
              </div>
              <button
                onClick={() => setSelectedPkg(null)}
                className="text-slate-400 hover:text-slate-600 font-bold text-lg p-1"
              >
                ✕
              </button>
            </div>

            {message && (
              <div
                className={`p-4 rounded-xl text-xs font-bold ${
                  message.type === 'success'
                    ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                    : 'bg-red-50 text-red-800 border border-red-200'
                }`}
              >
                {message.text}
              </div>
            )}

            <form onSubmit={handleBookingSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">
                  {isVi ? 'Họ và tên *' : 'Full Name *'}
                </label>
                <input
                  type="text"
                  required
                  placeholder={isVi ? 'Nhập họ và tên...' : 'Enter your name...'}
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-bold text-[#0b7f7c]"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">
                    {isVi ? 'Số điện thoại *' : 'Phone Number *'}
                  </label>
                  <input
                    type="tel"
                    required
                    placeholder={isVi ? 'Nhập SĐT...' : 'Enter phone...'}
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">
                    {isVi ? 'Email liên hệ *' : 'Email Address *'}
                  </label>
                  <input
                    type="email"
                    required
                    placeholder="email@domain.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">
                  {isVi ? 'Ghi chú / Yêu cầu riêng' : 'Notes / Special Requests'}
                </label>
                <textarea
                  rows={3}
                  value={note}
                  onChange={(e) => setNote(e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs resize-none"
                />
              </div>

              <div className="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setSelectedPkg(null)}
                  className="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200"
                >
                  {isVi ? 'Hủy Bỏ' : 'Cancel'}
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-6 py-2.5 bg-[#0b7f7c] text-white rounded-xl text-xs font-bold uppercase hover:bg-[#086a67]"
                >
                  {submitting ? (isVi ? '⏳ Đang gửi...' : 'Submitting...') : (isVi ? '🚀 GỬI ĐĂNG KÝ NGAY' : 'SUBMIT BOOKING')}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
