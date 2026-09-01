'use client';

import React, { useState, useEffect } from 'react';

export const ChatbotManager: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'scripts' | 'logs'>('scripts');
  const [scripts, setScripts] = useState<any[]>([]);
  const [logs, setLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const [editingScript, setEditingScript] = useState<any | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const fetchData = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/chatbot');
      if (res.ok) {
        const data = await res.json();
        setScripts(data.scripts || []);
        setLogs(data.logs || []);
      }
    } catch (err) {
      console.error('Error fetching chatbot data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleOpenAdd = () => {
    setEditingScript({
      category: 'Quy trình',
      question_vi: '',
      question_en: '',
      answer_vi: '',
      answer_en: '',
    });
    setIsModalOpen(true);
  };

  const handleOpenEdit = (s: any) => {
    setEditingScript({ ...s });
    setIsModalOpen(true);
  };

  const handleDeleteScript = async (id: number) => {
    if (!confirm('Xóa kịch bản Chatbot này?')) return;
    try {
      const res = await fetch(`/api/admin/chatbot?id=${id}&type=script`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa kịch bản thành công!' });
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa.' });
    }
  };

  const handleDeleteLog = async (id: number) => {
    if (!confirm('Xóa nhật ký chat này?')) return;
    try {
      const res = await fetch(`/api/admin/chatbot?id=${id}&type=log`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa nhật ký thành công!' });
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa.' });
    }
  };

  const handleSaveScript = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingScript?.question_vi || !editingScript?.answer_vi) return;

    try {
      const res = await fetch('/api/admin/chatbot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save_script', ...editingScript }),
      });

      if (res.ok) {
        setMessage({ type: 'success', text: 'Lưu kịch bản Chatbot thành công!' });
        setIsModalOpen(false);
        fetchData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi lưu kịch bản.' });
    }
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            🤖 QUẢN LÝ KỊCH BẢN CHATBOT & YÊU CẦU CHĂM SÓC 1-1 VIA EMAIL
          </h2>
          <p className="text-xs text-slate-500">
            Tạo kịch bản hỏi đáp tự động, xem báo cáo thắc mắc phổ biến và tiếp nhận email tư vấn riêng
          </p>
        </div>

        <div className="flex items-center gap-2">
          <div className="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
            <button
              onClick={() => setActiveTab('scripts')}
              className={`px-4 py-2 rounded-lg text-xs font-bold transition ${activeTab === 'scripts' ? 'bg-[#0b7f7c] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
            >
              📜 Kịch bản Tự động ({scripts.length})
            </button>
            <button
              onClick={() => setActiveTab('logs')}
              className={`px-4 py-2 rounded-lg text-xs font-bold transition ${activeTab === 'logs' ? 'bg-[#0b7f7c] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
            >
              📩 Email Tư vấn 1-1 ({logs.length})
            </button>
          </div>

          {activeTab === 'scripts' && (
            <button
              onClick={handleOpenAdd}
              className="px-4 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition shadow-md"
            >
              ➕ THÊM KỊCH BẢN MỚI
            </button>
          )}
        </div>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Analytics Summary */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 bg-teal-50/70 p-4 rounded-2xl border border-teal-200">
        <div className="flex items-center gap-3">
          <span className="text-2xl">📊</span>
          <div>
            <p className="text-[11px] font-bold text-slate-500 uppercase">Tổng Kịch Bản Tự Động</p>
            <p className="text-lg font-extrabold text-[#0b7f7c]">{scripts.length} Kịch bản</p>
          </div>
        </div>
        <div className="flex items-center gap-3">
          <span className="text-2xl">✉️</span>
          <div>
            <p className="text-[11px] font-bold text-slate-500 uppercase">Email CSKH 1-1 Tiếp Nhận</p>
            <p className="text-lg font-extrabold text-amber-700">{logs.length} Yêu cầu</p>
          </div>
        </div>
        <div className="flex items-center gap-3">
          <span className="text-2xl">💡</span>
          <div>
            <p className="text-[11px] font-bold text-slate-500 uppercase">Luồng Tư Vấn Chatbot</p>
            <p className="text-xs font-bold text-emerald-700">Tự động trả lời + Bắn Email CSKH</p>
          </div>
        </div>
      </div>

      {activeTab === 'scripts' ? (
        <div className="space-y-3">
          {loading ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải kịch bản Chatbot...</div>
          ) : scripts.length === 0 ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có kịch bản Chatbot nào.</div>
          ) : (
            scripts.map((s, idx) => (
              <div key={s.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-2 flex flex-wrap items-center justify-between gap-4">
                <div className="space-y-1 max-w-2xl">
                  <div className="flex items-center gap-2">
                    <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                      {s.category}
                    </span>
                    <span className="text-xs font-bold text-[#0b7f7c]">#{idx + 1}. {s.question_vi}</span>
                  </div>
                  <p className="text-xs text-slate-700 italic bg-white p-2.5 rounded-xl border border-slate-200">
                    💡 <strong>Trả lời:</strong> {s.answer_vi}
                  </p>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handleOpenEdit(s)}
                    className="px-3 py-1.5 bg-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-300"
                  >
                    ✏️ Sửa
                  </button>
                  <button
                    onClick={() => handleDeleteScript(s.id)}
                    className="px-3 py-1.5 bg-rose-500 text-white font-bold text-xs rounded-lg hover:bg-rose-600"
                  >
                    🗑️ Xóa
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      ) : (
        <div className="space-y-3">
          {loading ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải nhật ký email...</div>
          ) : logs.length === 0 ? (
            <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có email tư vấn 1-1 nào gửi từ Chatbot.</div>
          ) : (
            logs.map((log) => (
              <div key={log.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-2 flex flex-wrap items-center justify-between gap-4">
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <span className="text-xs font-bold text-[#0b7f7c] font-mono">✉️ {log.user_email}</span>
                    {log.user_phone && <span className="text-xs text-slate-500">📞 {log.user_phone}</span>}
                    <span className="text-[10px] text-slate-400">📅 {log.created_at}</span>
                  </div>
                  <p className="text-xs text-slate-800 font-bold">💬 Câu hỏi: "{log.user_message}"</p>
                </div>

                <button
                  onClick={() => handleDeleteLog(log.id)}
                  className="px-3 py-1.5 bg-rose-500 text-white font-bold text-xs rounded-lg hover:bg-rose-600"
                >
                  🗑️ Xóa
                </button>
              </div>
            ))
          )}
        </div>
      )}

      {/* Add / Edit Script Modal */}
      {isModalOpen && editingScript && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <h3 className="font-bold text-sm text-[#0b7f7c] uppercase">
                {editingScript.id ? '✏️ SỬA KỊCH BẢN CHATBOT' : '➕ THÊM KỊCH BẢN MỚI'}
              </h3>
              <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
            </div>

            <form onSubmit={handleSaveScript} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Chủ đề kịch bản</label>
                <input
                  type="text"
                  value={editingScript.category || 'Quy trình'}
                  onChange={(e) => setEditingScript({ ...editingScript, category: e.target.value })}
                  placeholder="VD: Quy trình, Chi phí, Bảo hiểm..."
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Câu hỏi gợi ý (Tiếng Việt)</label>
                <input
                  type="text"
                  required
                  value={editingScript.question_vi || ''}
                  onChange={(e) => setEditingScript({ ...editingScript, question_vi: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-[#0b7f7c]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Câu hỏi gợi ý (Tiếng Anh)</label>
                <input
                  type="text"
                  value={editingScript.question_en || ''}
                  onChange={(e) => setEditingScript({ ...editingScript, question_en: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Câu trả lời tự động (Tiếng Việt)</label>
                <textarea
                  rows={3}
                  required
                  value={editingScript.answer_vi || ''}
                  onChange={(e) => setEditingScript({ ...editingScript, answer_vi: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs resize-none"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Câu trả lời tự động (Tiếng Anh)</label>
                <textarea
                  rows={3}
                  value={editingScript.answer_en || ''}
                  onChange={(e) => setEditingScript({ ...editingScript, answer_en: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs resize-none"
                />
              </div>

              <div className="pt-3 flex justify-end gap-2 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200"
                >
                  Hủy Bỏ
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-[#0b7f7c] text-white rounded-xl text-xs font-bold hover:bg-[#086a67]"
                >
                  💾 Lưu Kịch Bản
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
