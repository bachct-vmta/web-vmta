'use client';

import React, { useState } from 'react';

interface InquiryFormProps {
  locale: string;
  defaultService?: string;
}

export const InquiryForm: React.FC<InquiryFormProps> = ({ locale, defaultService }) => {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [service, setService] = useState(defaultService || '');
  const [message, setMessage] = useState('');
  const [honeypot, setHoneypot] = useState('');
  const [status, setStatus] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !phone) return;

    setLoading(true);
    setStatus(null);

    try {
      const res = await fetch('/api/inquiries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, phone, email, service, message, honeypot }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setStatus({ type: 'success', msg: data.message });
        setName('');
        setPhone('');
        setEmail('');
        setMessage('');
      } else {
        setStatus({ type: 'error', msg: data.error || 'Đã có lỗi xảy ra' });
      }
    } catch {
      setStatus({ type: 'error', msg: 'Không thể kết nối đến máy chủ' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div id="dang-ky-tuvan" className="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-100">
      <h3 className="text-2xl font-bold text-[#0b7f7c] mb-2">
        {locale === 'vi' ? 'ĐĂNG KÝ TƯ VẤN Y TẾ' : 'CONSULTATION REQUEST'}
      </h3>
      <p className="text-sm text-slate-600 mb-6">
        {locale === 'vi'
          ? 'Để lại thông tin để được đội ngũ chuyên gia VMTA tư vấn chi tiết về lịch trình và dịch vụ.'
          : 'Fill in your contact details for personalized medical tourism advice.'}
      </p>

      {status && (
        <div
          className={`p-4 rounded-xl text-sm mb-6 ${
            status.type === 'success'
              ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
              : 'bg-rose-50 text-rose-800 border border-rose-200'
          }`}
        >
          {status.msg}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-4">
        {/* Honeypot field */}
        <input
          type="text"
          name="website_url"
          value={honeypot}
          onChange={(e) => setHoneypot(e.target.value)}
          className="hidden"
          tabIndex={-1}
          autoComplete="off"
        />

        <div>
          <label className="block text-xs font-semibold uppercase text-slate-600 mb-1">
            {locale === 'vi' ? 'Họ và tên *' : 'Full Name *'}
          </label>
          <input
            type="text"
            required
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder={locale === 'vi' ? 'Nhập họ tên...' : 'Your name...'}
            className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]"
          />
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-600 mb-1">
              {locale === 'vi' ? 'Số điện thoại *' : 'Phone Number *'}
            </label>
            <input
              type="tel"
              required
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="0912 xxx xxx"
              className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-600 mb-1">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="example@domain.com"
              className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]"
            />
          </div>
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase text-slate-600 mb-1">
            {locale === 'vi' ? 'Dịch vụ quan tâm' : 'Service of Interest'}
          </label>
          <select
            value={service}
            onChange={(e) => setService(e.target.value)}
            className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b7f7c] bg-white"
          >
            <option value="">{locale === 'vi' ? '-- Chọn dịch vụ --' : '-- Select Service --'}</option>
            <option value="Nha khoa thẩm mỹ & Implant">Nha khoa thẩm mỹ & Implant</option>
            <option value="Ghép tạng & Phẫu thuật chuyên sâu">Ghép tạng & Phẫu thuật chuyên sâu</option>
            <option value="Tầm soát sức khỏe tổng quát">Tầm soát sức khỏe tổng quát</option>
            <option value="Tour du lịch kết hợp nghỉ dưỡng">Tour du lịch kết hợp nghỉ dưỡng</option>
          </select>
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase text-slate-600 mb-1">
            {locale === 'vi' ? 'Ghi chú / Yêu cầu thêm' : 'Notes / Additional Requests'}
          </label>
          <textarea
            rows={3}
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            placeholder={locale === 'vi' ? 'Nội dung chi tiết...' : 'Details...'}
            className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full rounded-xl bg-[#0b7f7c] text-white py-3.5 px-6 font-bold uppercase text-sm hover:opacity-90 transition shadow-lg shadow-[#0b7f7c]/20 disabled:opacity-50"
        >
          {loading
            ? locale === 'vi'
              ? 'Đang gửi...'
              : 'Sending...'
            : locale === 'vi'
            ? 'GỬI YÊU CẦU TƯ VẤN'
            : 'SUBMIT CONSULTATION REQUEST'}
        </button>
      </form>
    </div>
  );
};
