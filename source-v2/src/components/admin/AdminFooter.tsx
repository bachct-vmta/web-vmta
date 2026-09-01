'use client';

import React, { useState } from 'react';

export const AdminFooter: React.FC = () => {
  const [showChangelog, setShowChangelog] = useState(false);

  return (
    <footer className="bg-slate-900 text-slate-400 border-t border-slate-800 text-xs font-utm-helve mt-12 py-8">
      <div className="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        {/* Column 1: Tech Support */}
        <div className="md:col-span-5 space-y-2">
          <h4 className="text-xs font-bold uppercase text-teal-400 flex items-center gap-2">
            👨‍💻 THÔNG TIN HỖ TRỢ KỸ THUẬT VIÊN
          </h4>
          <p className="text-slate-300 font-medium">
            Đội ngũ Kỹ thuật VMTA System Admin & Developer
          </p>
          <ul className="space-y-1 text-[11px] text-slate-400">
            <li>📧 Email Hỗ Trợ Kỹ Thuật: <a href="mailto:vmta@vmta.vn" className="text-teal-300 font-bold hover:underline">vmta@vmta.vn</a></li>
            <li>📞 Hotline Kỹ Thuật 24/7: <span className="text-amber-300 font-mono font-bold">0988 123 456</span></li>
            <li>🏢 Trụ sở Kỹ thuật: 193 Trích Sài, Phường Tây Hồ, Hà Nội</li>
          </ul>
        </div>

        {/* Column 2: System Version & Changelog Toggle */}
        <div className="md:col-span-7 space-y-3 md:text-right">
          <div className="flex flex-wrap items-center md:justify-end gap-3">
            <span className="bg-teal-900/80 text-teal-200 border border-teal-700/80 px-3 py-1 rounded-lg text-[11px] font-mono font-bold">
              VMTA CMS v2.4.0 (Release 2026)
            </span>
            <button
              onClick={() => setShowChangelog(!showChangelog)}
              className="text-xs text-amber-400 font-bold hover:underline bg-slate-800 px-3 py-1 rounded-lg border border-slate-700"
            >
              {showChangelog ? '▲ Ẩn Nhật Ký Thay Đổi' : '📋 Xem Nhật Ký Thay Đổi (Changelog)'}
            </button>
          </div>

          <p className="text-[11px] text-slate-500">
            © 2026 Vietnam Medical Tourism Alliance (VMTA). Bản quyền hệ thống CMS đã được bảo hộ.
          </p>
        </div>
      </div>

      {/* Expanded Changelog Modal / Accordion */}
      {showChangelog && (
        <div className="max-w-7xl mx-auto px-4 mt-6 pt-6 border-t border-slate-800">
          <div className="bg-slate-800/90 p-5 rounded-2xl border border-slate-700 space-y-3">
            <h5 className="text-xs font-bold uppercase text-amber-300">
              📜 NHẬT KÝ CẬP NHẬT PHIÊN BẢN (SYSTEM CHANGELOG)
            </h5>
            <div className="space-y-2 text-[11px] text-slate-300">
              <div className="border-b border-slate-700 pb-2">
                <span className="font-bold text-teal-400 font-mono">v2.4.0 (Hiện tại)</span>: 
                Tích hợp tính năng Upload Ảnh từ máy tính & Quản lý Link Video YouTube/HLS trực tiếp vào CSDL SQLite dev.db. Đồng bộ toàn bộ nội dung hiển thị 6 trang con.
              </div>
              <div className="border-b border-slate-700 pb-2">
                <span className="font-bold text-teal-400 font-mono">v2.3.0</span>: 
                Tích hợp dịch tự động Tiếng Việt sang Tiếng Anh (Google Cloud Translation API) trực tiếp trên trình biên soạn CMS Section.
              </div>
              <div>
                <span className="font-bold text-teal-400 font-mono">v2.2.0</span>: 
                Chuẩn hóa cấu trúc 4 trụ cột Mạng lưới Liên minh (Y tế, Nghỉ dưỡng, Lữ hành, Bảo hiểm) và thông tin trụ sở 193 Trích Sài, Tây Hồ, Hà Nội.
              </div>
            </div>
          </div>
        </div>
      )}
    </footer>
  );
};
