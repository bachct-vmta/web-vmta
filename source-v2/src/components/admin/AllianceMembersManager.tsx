'use client';

import React, { useState, useEffect } from 'react';
import { MediaPicker } from '@/components/admin/MediaPicker';

export const AllianceMembersManager: React.FC = () => {
  const [members, setMembers] = useState<any[]>([]);
  const [selectedGroupId, setSelectedGroupId] = useState<number>(0); // 0: All

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const [editingMember, setEditingMember] = useState<any | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const MEMBER_GROUPS = [
    { id: 1, name: 'Bệnh viện & Cơ sở Y tế Đạt Chuẩn', icon: '🏥' },
    { id: 2, name: 'Lữ hành & Du lịch', icon: '✈️' },
    { id: 3, name: 'Tài chính & Bảo hiểm', icon: '🛡️' },
    { id: 4, name: 'Các đơn vị khác', icon: '🌐' },
  ];

  const fetchMembers = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/alliance-members');
      if (res.ok) {
        const data = await res.json();
        setMembers(data.members || []);
      }
    } catch (err) {
      console.error('Error fetching alliance members:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMembers();
  }, []);

  const handleOpenAdd = () => {
    setEditingMember({
      group_id: 1,
      name_vi: '',
      name_en: '',
      badge: 'Bạch Kim',
      logo_url: '/images/home/header/logo-vmta.png',
      address: '',
      phone: '',
      email: '',
      website: '',
      description_vi: '',
      description_en: '',
    });
    setIsModalOpen(true);
  };

  const handleOpenEdit = (m: any) => {
    setEditingMember({ ...m });
    setIsModalOpen(true);
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa hội viên này khỏi mạng lưới VMTA?')) return;
    try {
      const res = await fetch(`/api/admin/alliance-members?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa hội viên thành công!' });
        fetchMembers();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa hội viên.' });
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingMember?.name_vi) return;
    setSaving(true);
    setMessage(null);

    try {
      const res = await fetch('/api/admin/alliance-members', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(editingMember),
      });

      if (res.ok) {
        setMessage({ type: 'success', text: 'Lưu hội viên thành công!' });
        setIsModalOpen(false);
        fetchMembers();
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi lưu hội viên.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối lưu hội viên.' });
    } finally {
      setSaving(false);
    }
  };

  const filteredMembers = selectedGroupId === 0
    ? members
    : members.filter((m) => Number(m.group_id) === Number(selectedGroupId));

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            🤝 QUẢN LÝ DANH SÁCH HỘI VIÊN MẠNG LƯỚI LIÊN MINH VMTA
          </h2>
          <p className="text-xs text-slate-500">
            Quản lý và cấp chứng nhận đối tác thành viên theo 4 phân nhóm chuyên biệt
          </p>
        </div>
        <button
          onClick={handleOpenAdd}
          className="px-4 py-2.5 bg-[#0b7f7c] text-white font-bold text-xs rounded-xl hover:bg-[#086a67] transition shadow-md"
        >
          ➕ THÊM HỘI VIÊN MỚI
        </button>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Group Filter Tabs */}
      <div className="flex flex-wrap items-center gap-2">
        <button
          onClick={() => setSelectedGroupId(0)}
          className={`px-3 py-1.5 rounded-lg text-xs font-bold transition ${selectedGroupId === 0 ? 'bg-[#0b7f7c] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'}`}
        >
          Tất cả hội viên ({members.length})
        </button>

        {MEMBER_GROUPS.map((g) => (
          <button
            key={g.id}
            onClick={() => setSelectedGroupId(g.id)}
            className={`px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 ${selectedGroupId === g.id ? 'bg-[#0b7f7c] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'}`}
          >
            <span>{g.icon}</span>
            <span>Nhóm {g.id}: {g.name}</span>
          </button>
        ))}
      </div>

      {/* Members Grid */}
      {loading ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải danh sách hội viên...</div>
      ) : filteredMembers.length === 0 ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có hội viên nào trong nhóm này.</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredMembers.map((m) => {
            const groupObj = MEMBER_GROUPS.find((g) => g.id === Number(m.group_id));
            return (
              <div key={m.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-3 flex flex-col justify-between">
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                      {groupObj?.icon} {groupObj?.name}
                    </span>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
                      🏅 {m.badge}
                    </span>
                  </div>

                  <div className="flex items-center gap-3 pt-1">
                    {m.logo_url && (
                      <img src={m.logo_url} alt="" className="h-10 w-12 object-contain bg-white rounded border border-slate-200 p-1 shrink-0" />
                    )}
                    <h3 className="font-bold text-xs text-[#0b7f7c] line-clamp-2">{m.name_vi}</h3>
                  </div>

                  <p className="text-[11px] text-slate-600 line-clamp-2">{m.description_vi}</p>
                  <p className="text-[10px] text-slate-400">📍 {m.address || 'Chưa cập nhật địa chỉ'}</p>
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-slate-200">
                  <button
                    onClick={() => handleOpenEdit(m)}
                    className="px-3 py-1.5 bg-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-300"
                  >
                    ✏️ Sửa
                  </button>
                  <button
                    onClick={() => handleDelete(m.id)}
                    className="px-3 py-1.5 bg-rose-500 text-white font-bold text-xs rounded-lg hover:bg-rose-600"
                  >
                    🗑️ Xóa
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Add / Edit Member Modal */}
      {isModalOpen && editingMember && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <h3 className="font-bold text-sm text-[#0b7f7c] uppercase">
                {editingMember.id ? '✏️ SỬA THÔNG TIN HỘI VIÊN' : '➕ THÊM HỘI VIÊN MỚI'}
              </h3>
              <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Phân nhóm hội viên</label>
                <select
                  value={editingMember.group_id}
                  onChange={(e) => setEditingMember({ ...editingMember, group_id: Number(e.target.value) })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold bg-white"
                >
                  {MEMBER_GROUPS.map((g) => (
                    <option key={g.id} value={g.id}>
                      Nhóm {g.id}: {g.icon} {g.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Tên hội viên (Tiếng Việt)</label>
                  <input
                    type="text"
                    required
                    value={editingMember.name_vi || ''}
                    onChange={(e) => setEditingMember({ ...editingMember, name_vi: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-[#0b7f7c]"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Tên hội viên (Tiếng Anh)</label>
                  <input
                    type="text"
                    value={editingMember.name_en || ''}
                    onChange={(e) => setEditingMember({ ...editingMember, name_en: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Hạng chứng nhận hội viên</label>
                  <select
                    value={editingMember.badge || 'Bạch Kim'}
                    onChange={(e) => setEditingMember({ ...editingMember, badge: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold bg-white"
                  >
                    <option value="Bạch Kim">🏅 Bạch Kim (Platinum)</option>
                    <option value="Vàng">🥇 Vàng (Gold)</option>
                    <option value="Chuẩn">🎖️ Chuẩn VMTA (Standard)</option>
                  </select>
                </div>
              <MediaPicker
                label="Logo / Hình ảnh đại diện Đơn vị (Cloud CDN Option 2)"
                value={editingMember.logo_url || ''}
                onChange={(url) => setEditingMember({ ...editingMember, logo_url: url })}
              />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Địa chỉ trụ sở</label>
                <input
                  type="text"
                  value={editingMember.address || ''}
                  onChange={(e) => setEditingMember({ ...editingMember, address: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
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
                  disabled={saving}
                  className="px-6 py-2 bg-[#0b7f7c] text-white rounded-xl text-xs font-bold hover:bg-[#086a67]"
                >
                  {saving ? '⏳ Đang lưu...' : '💾 Lưu Hội Viên'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
