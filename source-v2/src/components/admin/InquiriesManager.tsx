'use client';

import React, { useState, useEffect } from 'react';

export const InquiriesManager: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'inquiries' | 'subscribers'>('inquiries');
  const [inquiries, setInquiries] = useState<any[]>([]);
  const [subscribers, setSubscribers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [inqRes, subRes] = await Promise.all([
        fetch('/api/admin/inquiries'),
        fetch('/api/admin/newsletter'),
      ]);

      if (inqRes.ok) {
        const d = await inqRes.json();
        setInquiries(d.inquiries || []);
      }
      if (subRes.ok) {
        const d = await subRes.json();
        setSubscribers(d.subscribers || []);
      }
    } catch (err) {
      console.error('Error fetching inquiries/subscribers:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleUpdateStatus = async (id: number, status: string) => {
    try {
      const res = await fetch('/api/admin/inquiries', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status }),
      });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã cập nhật trạng thái xử lý!' });
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi cập nhật trạng thái.' });
    }
  };

  const handleDeleteInquiry = async (id: number) => {
    if (!confirm('Xóa yêu cầu tư vấn này?')) return;
    try {
      const res = await fetch(`/api/admin/inquiries?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa thành công!' });
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa.' });
    }
  };

  const handleDeleteSubscriber = async (id: number) => {
    if (!confirm('Xóa subscriber này khỏi danh sách nhận tin?')) return;
    try {
      const res = await fetch(`/api/admin/newsletter?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa thành công!' });
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa.' });
    }
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            📩 QUẢN LÝ YÊU CẦU TƯ VẤN & ĐĂNG KÝ NEWSLETTER
          </h2>
          <p className="text-xs text-slate-500">
            Tiếp nhận Lead từ khách hàng & quản lý danh sách email đăng ký nhận bản tin
          </p>
        </div>

        <div className="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
          <button
            onClick={() => setActiveTab('inquiries')}
            className={`px-4 py-2 rounded-lg text-xs font-bold transition ${activeTab === 'inquiries' ? 'bg-[#0b7f7c] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
          >
            📩 Yêu cầu tư vấn ({inquiries.length})
          </button>
          <button
            onClick={() => setActiveTab('subscribers')}
            className={`px-4 py-2 rounded-lg text-xs font-bold transition ${activeTab === 'subscribers' ? 'bg-[#0b7f7c] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
          >
            📧 Đăng ký Newsletter ({subscribers.length})
          </button>
        </div>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {activeTab === 'inquiries' ? (
        <div className="space-y-4">
          {loading ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải danh sách yêu cầu...</div>
          ) : inquiries.length === 0 ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có yêu cầu tư vấn nào từ khách hàng.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs border-collapse">
                <thead>
                  <tr className="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <th className="p-3">Khách hàng</th>
                    <th className="p-3">Thông tin liên hệ</th>
                    <th className="p-3">Nội dung yêu cầu</th>
                    <th className="p-3">Thời gian</th>
                    <th className="p-3">Trạng thái</th>
                    <th className="p-3 text-right">Thao tác</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {inquiries.map((inq) => (
                    <tr key={inq.id} className="hover:bg-slate-50">
                      <td className="p-3 font-bold text-[#0b7f7c]">
                        {inq.full_name}
                        {inq.organization && <p className="text-[10px] text-slate-400 font-normal">{inq.organization}</p>}
                      </td>
                      <td className="p-3 space-y-0.5">
                        <p className="font-mono text-slate-800">{inq.email}</p>
                        <p className="text-slate-500">{inq.phone || 'Không có SĐT'}</p>
                      </td>
                      <td className="p-3 max-w-xs text-slate-700 line-clamp-2">{inq.content || 'Yêu cầu tư vấn'}</td>
                      <td className="p-3 text-slate-400 text-[11px]">{inq.created_at}</td>
                      <td className="p-3">
                        <select
                          value={inq.status || 'new'}
                          onChange={(e) => handleUpdateStatus(inq.id, e.target.value)}
                          className={`px-2 py-1 rounded-md text-[11px] font-bold outline-none border ${
                            inq.status === 'done'
                              ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                              : inq.status === 'processing'
                              ? 'bg-amber-50 text-amber-900 border-amber-300'
                              : 'bg-red-50 text-red-800 border-red-300'
                          }`}
                        >
                          <option value="new">🔴 Mới tiếp nhận</option>
                          <option value="processing">🟡 Đang xử lý</option>
                          <option value="done">🟢 Đã hoàn thành</option>
                        </select>
                      </td>
                      <td className="p-3 text-right">
                        <button
                          onClick={() => handleDeleteInquiry(inq.id)}
                          className="px-2.5 py-1 bg-rose-500 text-white rounded text-[11px] font-bold hover:bg-rose-600"
                        >
                          Xóa
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      ) : (
        <div className="space-y-4">
          {loading ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải danh sách subscriber...</div>
          ) : subscribers.length === 0 ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có email nào đăng ký nhận bản tin.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs border-collapse">
                <thead>
                  <tr className="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <th className="p-3">STT</th>
                    <th className="p-3">Email Đăng Ký</th>
                    <th className="p-3">Thời gian</th>
                    <th className="p-3 text-right">Thao tác</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {subscribers.map((sub, idx) => (
                    <tr key={sub.id} className="hover:bg-slate-50">
                      <td className="p-3 font-bold text-slate-400">#{idx + 1}</td>
                      <td className="p-3 font-bold text-[#0b7f7c] font-mono">{sub.email}</td>
                      <td className="p-3 text-slate-400 text-[11px]">{sub.created_at}</td>
                      <td className="p-3 text-right">
                        <button
                          onClick={() => handleDeleteSubscriber(sub.id)}
                          className="px-2.5 py-1 bg-rose-500 text-white rounded text-[11px] font-bold hover:bg-rose-600"
                        >
                          Xóa
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
    </div>
  );
};
