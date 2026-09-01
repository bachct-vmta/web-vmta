'use client';

import React, { useState, useEffect } from 'react';
import { MediaPicker } from '@/components/admin/MediaPicker';

export const MedicalPackagesManager: React.FC = () => {
  const [categories, setCategories] = useState<any[]>([]);
  const [packages, setPackages] = useState<any[]>([]);
  const [selectedCatKey, setSelectedCatKey] = useState<string>('all');

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  // Form State
  const [editingPkg, setEditingPkg] = useState<any | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const fetchPackagesData = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/medical-packages');
      if (res.ok) {
        const data = await res.json();
        setCategories(data.categories || []);
        setPackages(data.packages || []);
      }
    } catch (err) {
      console.error('Error fetching medical packages:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPackagesData();
  }, []);

  const handleOpenAdd = () => {
    setEditingPkg({
      category_key: categories[0]?.category_key || 'screening',
      title_vi: '',
      title_en: '',
      subtitle_vi: '',
      subtitle_en: '',
      duration_vi: '3 Ngày 2 Đêm',
      duration_en: '3 Days 2 Nights',
      price_vi: '',
      price_en: '',
      facility_name: '',
      image_url: '/images/news/Making-Vietnam-Medical-Tourism.jpg',
      itinerary_vi: '',
      itinerary_en: '',
    });
    setIsModalOpen(true);
  };

  const handleOpenEdit = (pkg: any) => {
    setEditingPkg({ ...pkg });
    setIsModalOpen(true);
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa Gói dịch vụ này?')) return;
    try {
      const res = await fetch(`/api/admin/medical-packages?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa gói dịch vụ thành công!' });
        fetchPackagesData();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa gói dịch vụ.' });
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPkg?.title_vi) return;
    setSaving(true);
    setMessage(null);

    try {
      const res = await fetch('/api/admin/medical-packages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(editingPkg),
      });

      if (res.ok) {
        setMessage({ type: 'success', text: 'Lưu gói dịch vụ thành công!' });
        setIsModalOpen(false);
        fetchPackagesData();
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi lưu gói dịch vụ.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối lưu gói dịch vụ.' });
    } finally {
      setSaving(false);
    }
  };

  const filteredPackages = selectedCatKey === 'all'
    ? packages
    : packages.filter((p) => p.category_key === selectedCatKey);

  const medicalCats = categories.filter((c) => c.type === 'medical');
  const tourismCats = categories.filter((c) => c.type === 'tourism');

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            🩺 QUẢN LÝ CHUYÊN KHOA Y TẾ, DU LỊCH & GÓI DỊCH VỤ THAM KHẢO
          </h2>
          <p className="text-xs text-slate-500">
            Phân nhóm Y tế (5 nhóm) & Du lịch (5 nhóm), quản lý gói trải nghiệm phục hồi nghỉ dưỡng
          </p>
        </div>
        <button
          onClick={handleOpenAdd}
          className="px-4 py-2.5 bg-[#0b7f7c] text-white font-bold text-xs rounded-xl hover:bg-[#086a67] transition shadow-md"
        >
          ➕ THÊM GÓI DỊCH VỤ MỚI
        </button>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Category Filter Tabs */}
      <div className="space-y-3">
        <div className="flex items-center gap-2">
          <span className="text-xs font-bold text-slate-500 uppercase">Lọc theo nhóm:</span>
          <button
            onClick={() => setSelectedCatKey('all')}
            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition ${selectedCatKey === 'all' ? 'bg-[#0b7f7c] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'}`}
          >
            Tất cả gói ({packages.length})
          </button>
        </div>

        {/* Medical Categories */}
        <div className="p-3 bg-teal-50/60 rounded-xl border border-teal-200 space-y-2">
          <label className="text-[11px] font-bold text-[#0b7f7c] uppercase">🩺 5 Nhóm Chuyên Khoa Y Tế:</label>
          <div className="flex flex-wrap gap-2">
            {medicalCats.map((cat) => (
              <button
                key={cat.category_key}
                onClick={() => setSelectedCatKey(cat.category_key)}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5 ${selectedCatKey === cat.category_key ? 'bg-[#0b7f7c] text-white' : 'bg-white text-slate-700 border border-teal-200 hover:bg-teal-100'}`}
              >
                <span>{cat.icon}</span>
                <span>{cat.title_vi}</span>
              </button>
            ))}
          </div>
        </div>

        {/* Tourism Categories */}
        <div className="p-3 bg-sky-50/60 rounded-xl border border-sky-200 space-y-2">
          <label className="text-[11px] font-bold text-sky-800 uppercase">✈️ 5 Nhóm Dịch Vụ Du Lịch & Lưu Trú:</label>
          <div className="flex flex-wrap gap-2">
            {tourismCats.map((cat) => (
              <button
                key={cat.category_key}
                onClick={() => setSelectedCatKey(cat.category_key)}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5 ${selectedCatKey === cat.category_key ? 'bg-sky-700 text-white' : 'bg-white text-slate-700 border border-sky-200 hover:bg-sky-100'}`}
              >
                <span>{cat.icon}</span>
                <span>{cat.title_vi}</span>
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Packages Grid */}
      {loading ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải danh sách gói dịch vụ...</div>
      ) : filteredPackages.length === 0 ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có gói dịch vụ nào trong danh mục này.</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {filteredPackages.map((pkg) => {
            const catName = categories.find((c) => c.category_key === pkg.category_key)?.title_vi || pkg.category_key;
            return (
              <div key={pkg.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-3 flex flex-col justify-between">
                <div className="space-y-2">
                  <div className="flex justify-between items-start">
                    <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                      {catName}
                    </span>
                    <span className="text-xs font-bold text-[#d31e45]">{pkg.price_vi}</span>
                  </div>
                  <h3 className="font-bold text-sm text-[#0b7f7c] line-clamp-2">{pkg.title_vi}</h3>
                  <p className="text-xs text-slate-600 italic">{pkg.subtitle_vi}</p>
                  <div className="text-[11px] text-slate-500 space-y-1 pt-1 border-t border-slate-200">
                    <p><strong>Thời lượng:</strong> {pkg.duration_vi}</p>
                    <p><strong>Cơ sở y tế:</strong> {pkg.facility_name}</p>
                  </div>
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-slate-200">
                  <button
                    onClick={() => handleOpenEdit(pkg)}
                    className="px-3 py-1.5 bg-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-300"
                  >
                    ✏️ Sửa
                  </button>
                  <button
                    onClick={() => handleDelete(pkg.id)}
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

      {/* Add / Edit Package Modal */}
      {isModalOpen && editingPkg && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <h3 className="font-bold text-sm text-[#0b7f7c] uppercase">
                {editingPkg.id ? '✏️ SỬA GÓI DỊCH VỤ THAM KHẢO' : '➕ THÊM GÓI DỊCH VỤ MỚI'}
              </h3>
              <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Danh mục dịch vụ</label>
                <select
                  value={editingPkg.category_key}
                  onChange={(e) => setEditingPkg({ ...editingPkg, category_key: e.target.value })}
                  className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold bg-white"
                >
                  {categories.map((c) => (
                    <option key={c.category_key} value={c.category_key}>
                      [{c.type === 'medical' ? 'Y tế' : 'Du lịch'}] {c.icon} {c.title_vi}
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Tên gói (Tiếng Việt)</label>
                  <input
                    type="text"
                    required
                    value={editingPkg.title_vi || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, title_vi: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-[#0b7f7c]"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Tên gói (Tiếng Anh)</label>
                  <input
                    type="text"
                    value={editingPkg.title_en || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, title_en: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Chi phí tham khảo (VI)</label>
                  <input
                    type="text"
                    value={editingPkg.price_vi || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, price_vi: e.target.value })}
                    placeholder="VD: 15.500.000 VNĐ"
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-[#d31e45]"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Chi phí tham khảo (EN)</label>
                  <input
                    type="text"
                    value={editingPkg.price_en || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, price_en: e.target.value })}
                    placeholder="VD: $650 USD"
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Thời lượng (VI)</label>
                  <input
                    type="text"
                    value={editingPkg.duration_vi || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, duration_vi: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Cơ sở y tế đảm nhận</label>
                  <input
                    type="text"
                    value={editingPkg.facility_name || ''}
                    onChange={(e) => setEditingPkg({ ...editingPkg, facility_name: e.target.value })}
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
                  />
                </div>
              </div>

              <MediaPicker
                label="Hình ảnh gói dịch vụ"
                value={editingPkg.image_url || ''}
                onChange={(url) => setEditingPkg({ ...editingPkg, image_url: url })}
              />

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
                  {saving ? '⏳ Đang lưu...' : '💾 Lưu Gói Dịch Vụ'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
