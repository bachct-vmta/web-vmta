'use client';

import React, { useState, useEffect } from 'react';

export const MediaManager: React.FC = () => {
  const [assets, setAssets] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [copiedId, setCopiedId] = useState<number | null>(null);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const fetchAssets = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/media');
      if (res.ok) {
        const data = await res.json();
        setAssets(data.assets || []);
      }
    } catch (err) {
      console.error('Error fetching media:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAssets();
  }, []);

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setUploading(true);
    setMessage(null);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch('/api/admin/upload', {
        method: 'POST',
        body: formData,
      });

      if (res.ok) {
        setMessage({ type: 'success', text: 'Tải ảnh thành công và đã đồng bộ lên Thư viện Media Cloud!' });
        fetchAssets();
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi tải ảnh.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối khi tải ảnh.' });
    } finally {
      setUploading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa tập tin media này khỏi thư viện?')) return;
    try {
      const res = await fetch(`/api/admin/media?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa tập tin media thành công!' });
        fetchAssets();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa tập tin media.' });
    }
  };

  const handleCopyUrl = (url: string, id: number) => {
    navigator.clipboard.writeText(url);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const filteredAssets = assets.filter(
    (a) =>
      a.filename.toLowerCase().includes(searchQuery.toLowerCase()) ||
      a.url.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            📁 QUẢN LÝ THƯ VIỆN MEDIA & HÌNH ẢNH CLOUD CDN
          </h2>
          <p className="text-xs text-slate-500">
            Tải ảnh mới tự động đồng bộ Cloud Storage, quản lý tập trung và sao chép liên kết CDN 1-Click
          </p>
        </div>

        <label className="px-4 py-2.5 bg-[#0b7f7c] text-white font-bold text-xs rounded-xl hover:bg-[#086a67] transition shadow-md cursor-pointer inline-flex items-center gap-2">
          <span>{uploading ? '⏳ Đang đẩy lên Cloud Storage...' : '☁️ TẢI ẢNH MỚI LÊN CLOUD'}</span>
          <input
            type="file"
            accept="image/*"
            onChange={handleFileUpload}
            disabled={uploading}
            className="hidden"
          />
        </label>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Search Bar */}
      <div className="flex items-center justify-between gap-4">
        <input
          type="text"
          placeholder="🔍 Tìm kiếm ảnh trong thư viện theo tên file hoặc URL..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="w-full max-w-md rounded-xl border border-slate-300 px-4 py-2 text-xs font-medium bg-white"
        />
        <span className="text-xs font-bold text-slate-500 shrink-0">
          Tổng cộng: {filteredAssets.length} hình ảnh
        </span>
      </div>

      {/* Assets Grid */}
      {loading ? (
        <div className="text-center py-12 text-xs font-bold text-slate-400">⏳ Đang nạp thư viện media...</div>
      ) : filteredAssets.length === 0 ? (
        <div className="text-center py-12 text-xs font-bold text-slate-400">Chưa có tập tin media nào khớp với tìm kiếm.</div>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          {filteredAssets.map((asset) => {
            const isCloud = asset.url?.startsWith('http://') || asset.url?.startsWith('https://');
            return (
              <div
                key={asset.id}
                className="bg-slate-50 rounded-2xl border border-slate-200 p-3 flex flex-col justify-between space-y-2 group hover:shadow-md transition"
              >
                <div className="relative overflow-hidden rounded-xl border border-slate-200 aspect-square bg-white">
                  <img
                    src={asset.url}
                    alt=""
                    className="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                  />
                  {isCloud && (
                    <span className="absolute top-1 right-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-sky-600 text-white shadow-xs">
                      ☁️ Cloud
                    </span>
                  )}
                </div>

                <div className="space-y-1">
                  <p className="text-[11px] font-bold text-[#0b7f7c] truncate" title={asset.filename}>
                    {asset.filename}
                  </p>
                  <p className="text-[9px] text-slate-400 font-mono truncate" title={asset.url}>
                    {asset.url}
                  </p>
                </div>

                <div className="pt-2 border-t border-slate-200 flex items-center justify-between gap-1">
                  <button
                    type="button"
                    onClick={() => handleCopyUrl(asset.url, asset.id)}
                    className="px-2 py-1 bg-teal-50 text-[#0b7f7c] border border-teal-200 rounded text-[10px] font-bold hover:bg-teal-100 flex-1"
                  >
                    {copiedId === asset.id ? '✓ Đã Copy' : '📋 Copy URL'}
                  </button>
                  <button
                    type="button"
                    onClick={() => handleDelete(asset.id)}
                    className="px-2 py-1 bg-rose-100 text-rose-700 rounded text-[10px] font-bold hover:bg-rose-200"
                    title="Xóa khỏi thư viện"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};
