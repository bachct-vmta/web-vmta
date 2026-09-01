'use client';

import React from 'react';

export const AdminDashboard: React.FC = () => {
  const stats = [
    { label: 'TỔNG LƯỢT TRUY CẬP HÔM NAY', value: '1,420', change: '+12.5%', color: 'border-teal-500 text-[#0b7f7c]' },
    { label: 'YÊU CẦU THAM GIA HỆ SINH THÁI', value: '28', change: '+4 mới', color: 'border-red-500 text-red-600' },
    { label: 'ĐOẠN NỘI DUNG CMS ĐÃ ĐỒNG BỘ', value: '31', change: '100% Active', color: 'border-emerald-500 text-emerald-600' },
    { label: 'ĐĂNG KÝ NHẬN TIN NEWSLETTER', value: '154', change: '+18 tuần này', color: 'border-amber-500 text-amber-600' },
  ];

  const recentInquiries = [
    { name: 'Bệnh viện Đa khoa Hồng Ngọc', type: 'Cơ sở Y tế', email: 'contact@hongngochospital.vn', phone: '024 3927 5555', status: 'Chờ xử lý', date: '01/09/2026' },
    { name: 'Sun World Ba Na Hills Resort', type: 'Khu Nghỉ dưỡng', email: 'info@banahills.sunworld.vn', phone: '0905 766 777', status: 'Đã liên hệ', date: '31/08/2026' },
    { name: 'Vietravel Medical Tour', type: 'Doanh nghiệp Lữ hành', email: 'medical@vietravel.com', phone: '028 3822 8898', status: 'Đã phê duyệt', date: '30/08/2026' },
  ];

  return (
    <div className="space-y-8 font-utm-helve">
      {/* Overview Stat Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((item, idx) => (
          <div key={idx} className={`bg-white p-6 rounded-2xl border-l-4 ${item.color} shadow-sm border border-slate-200 space-y-2`}>
            <p className="text-[11px] font-bold uppercase text-slate-500 tracking-wider">{item.label}</p>
            <div className="flex items-baseline justify-between">
              <span className="text-3xl font-extrabold text-slate-800">{item.value}</span>
              <span className="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{item.change}</span>
            </div>
          </div>
        ))}
      </div>

      {/* Recent Ecosystem Join Requests Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div className="flex items-center justify-between border-b border-slate-200 pb-4">
          <h3 className="font-bold text-base text-[#0b7f7c] uppercase flex items-center gap-2">
            <span>📩</span> DANH SÁCH YÊU CẦU THAM GIA HỆ SINH THÁI GẦN ĐÂY
          </h3>
          <span className="text-xs font-bold text-slate-500">Hiển thị 3/28 bản ghi</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-700">
            <thead className="bg-slate-50 uppercase font-bold text-slate-500 border-b border-slate-200">
              <tr>
                <th className="px-4 py-3">Doanh nghiệp / Đơn vị</th>
                <th className="px-4 py-3">Loại hình</th>
                <th className="px-4 py-3">Email liên hệ</th>
                <th className="px-4 py-3">Số điện thoại</th>
                <th className="px-4 py-3">Trạng thái</th>
                <th className="px-4 py-3">Ngày gửi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {recentInquiries.map((req, idx) => (
                <tr key={idx} className="hover:bg-slate-50 transition">
                  <td className="px-4 py-3 font-bold text-slate-900">{req.name}</td>
                  <td className="px-4 py-3">{req.type}</td>
                  <td className="px-4 py-3 font-mono text-slate-600">{req.email}</td>
                  <td className="px-4 py-3 font-mono">{req.phone}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2.5 py-1 rounded-full font-bold text-[10px] ${
                      req.status === 'Chờ xử lý' ? 'bg-amber-100 text-amber-800' :
                      req.status === 'Đã liên hệ' ? 'bg-blue-100 text-blue-800' :
                      'bg-emerald-100 text-emerald-800'
                    }`}>
                      {req.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-slate-400 font-mono">{req.date}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
