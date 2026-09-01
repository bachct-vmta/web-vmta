'use client';

import React, { useState, useEffect } from 'react';

interface MediaPickerProps {
  label: string;
  value: string;
  onChange: (url: string) => void;
}

export const MediaPicker: React.FC<MediaPickerProps> = ({ label, value, onChange }) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [activeTab, setActiveTab] = useState<'library' | 'upload' | 'url'>('library');

  const [assets, setAssets] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [manualUrl, setManualUrl] = useState(value || '');

  const isCloud = value?.startsWith('http://') || value?.startsWith('https://');

  const fetchAssets = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/media');
      if (res.ok) {
        const data = await res.json();
        setAssets(data.assets || []);
      }
    } catch (err) {
      console.error('Error fetching media assets:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = () => {
    setIsModalOpen(true);
    fetchAssets();
  };

  const handleSelectAsset = (url: string) => {
    onChange(url);
    setIsModalOpen(false);
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setUploading(true);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch('/api/admin/upload', {
        method: 'POST',
        body: formData,
      });

      if (res.ok) {
        const data = await res.json();
        if (data.url) {
          onChange(data.url);
          setIsModalOpen(false);
        }
      }
    } catch (err) {
      console.error('Error uploading image:', err);
    } finally {
      setUploading(false);
    }
  };

  const filteredAssets = assets.filter(
    (a) =>
      a.filename.toLowerCase().includes(searchQuery.toLowerCase()) ||
      a.url.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="space-y-1.5 font-utm-helve">
      <div className="flex items-center justify-between">
        <label className="block text-xs font-bold text-slate-700">{label}</label>
        {isCloud && (
          <span className="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full">
            ☁️ Cloud Storage CDN
          </span>
        )}
      </div>

      <div className="flex flex-wrap items-center gap-3">
        {value && (
          <div className="relative group shrink-0">
            <img
              src={value}
              alt=""
              className="h-16 w-20 object-cover rounded-xl border border-slate-300 shadow-xs bg-white"
            />
            <button
              type="button"
              onClick={() => onChange('')}
              className="absolute -top-1.5 -right-1.5 bg-rose-600 text-white rounded-full w-5 h-5 text-[10px] font-bold flex items-center justify-center shadow-md hover:bg-rose-700"
            >
              ✕
            </button>
          </div>
        )}

        <div className="flex-1 space-y-2 min-w-[200px]">
          <input
            type="text"
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            placeholder="Dán URL hình ảnh..."
            className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono bg-white"
          />

          <button
            type="button"
            onClick={handleOpenModal}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 border border-teal-200 text-[#0b7f7c] rounded-xl text-xs font-bold hover:bg-teal-100 cursor-pointer transition"
          >
            <span>📁 CHỌN TỪ THƯ VIỆN MEDIA HOẶC UPLOAD MÓI</span>
          </button>
        </div>
      </div>

      {/* Cloud Media Library Picker Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
          <div className="w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 space-y-4 max-h-[88vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <div className="flex items-center gap-3">
                <h3 className="font-bold text-sm text-[#0b7f7c] uppercase">
                  📁 THƯ VIỆN HÌNH ẢNH CLOUD CDN
                </h3>
                <div className="flex bg-slate-100 p-1 rounded-xl">
                  <button
                    type="button"
                    onClick={() => setActiveTab('library')}
                    className={`px-3 py-1 text-xs font-bold rounded-lg transition ${activeTab === 'library' ? 'bg-[#0b7f7c] text-white' : 'text-slate-600'}`}
                  >
                    🗃️ Chọn từ Thư viện
                  </button>
                  <button
                    type="button"
                    onClick={() => setActiveTab('upload')}
                    className={`px-3 py-1 text-xs font-bold rounded-lg transition ${activeTab === 'upload' ? 'bg-[#0b7f7c] text-white' : 'text-slate-600'}`}
                  >
                    ☁️ Tải Ảnh Mới Lên Cloud
                  </button>
                  <button
                    type="button"
                    onClick={() => setActiveTab('url')}
                    className={`px-3 py-1 text-xs font-bold rounded-lg transition ${activeTab === 'url' ? 'bg-[#0b7f7c] text-white' : 'text-slate-600'}`}
                  >
                    🔗 Dán Link URL
                  </button>
                </div>
              </div>

              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 font-bold text-lg"
              >
                ✕
              </button>
            </div>

            {/* TAB 1: LIBRARY SELECTOR */}
            {activeTab === 'library' && (
              <div className="space-y-4">
                <input
                  type="text"
                  placeholder="🔍 Tìm kiếm ảnh trong thư viện..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-2 text-xs"
                />

                {loading ? (
                  <div className="text-center py-12 text-xs font-bold text-slate-400">⏳ Đang tải thư viện ảnh...</div>
                ) : filteredAssets.length === 0 ? (
                  <div className="text-center py-12 text-xs font-bold text-slate-400">Chưa có ảnh nào trong thư viện. Chuyển sang Tab "Tải Ảnh Mới" để upload.</div>
                ) : (
                  <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 max-h-[50vh] overflow-y-auto p-1">
                    {filteredAssets.map((asset) => {
                      const isSelected = value === asset.url;
                      return (
                        <div
                          key={asset.id}
                          onClick={() => handleSelectAsset(asset.url)}
                          className={`relative cursor-pointer rounded-xl border p-2 flex flex-col items-center space-y-1.5 transition ${
                            isSelected
                              ? 'border-[#0b7f7c] bg-teal-50 ring-2 ring-[#0b7f7c]'
                              : 'border-slate-200 hover:border-teal-400 hover:bg-slate-50'
                          }`}
                        >
                          <img
                            src={asset.url}
                            alt=""
                            className="aspect-square w-full object-cover rounded-lg bg-white"
                          />
                          <span className="text-[10px] font-bold text-slate-700 truncate w-full text-center">
                            {asset.filename}
                          </span>
                          {isSelected && (
                            <span className="absolute top-1 right-1 bg-[#0b7f7c] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                              ✓ Chọn
                            </span>
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            )}

            {/* TAB 2: NEW UPLOAD */}
            {activeTab === 'upload' && (
              <div className="py-8 text-center space-y-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <span className="text-4xl">☁️</span>
                <div>
                  <h4 className="font-bold text-sm text-[#0b7f7c]">TẢI ẢNH MỚI TỪ MÁY TÍNH LÊN CLOUD STORAGE</h4>
                  <p className="text-xs text-slate-500">Tự động nén WebP & đồng bộ tức thì vào Thư viện Media Cloud</p>
                </div>

                <label className="inline-flex items-center gap-2 px-6 py-3 bg-[#0b7f7c] text-white text-xs font-bold uppercase rounded-xl hover:bg-[#086a67] cursor-pointer shadow-md transition">
                  <span>{uploading ? '⏳ Đang tải lên Cloud...' : '📁 BẤM ĐỂ CHỌN FILE TỪ MÁY TÍNH'}</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleFileUpload}
                    disabled={uploading}
                    className="hidden"
                  />
                </label>
              </div>
            )}

            {/* TAB 3: PASTE URL */}
            {activeTab === 'url' && (
              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Đường dẫn URL hình ảnh</label>
                  <input
                    type="text"
                    value={manualUrl}
                    onChange={(e) => setManualUrl(e.target.value)}
                    placeholder="https://domain.com/image.jpg"
                    className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-mono"
                  />
                </div>

                {manualUrl && (
                  <div className="flex items-center gap-3">
                    <span className="text-xs text-slate-500 font-bold">Xem trước:</span>
                    <img src={manualUrl} alt="" className="h-16 w-24 object-cover rounded-xl border border-slate-300" />
                  </div>
                )}

                <div className="pt-2 flex justify-end">
                  <button
                    type="button"
                    onClick={() => {
                      onChange(manualUrl);
                      setIsModalOpen(false);
                    }}
                    className="px-5 py-2 bg-[#0b7f7c] text-white text-xs font-bold rounded-xl"
                  >
                    XÁC NHẬN CHỌN URL NÀY
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
