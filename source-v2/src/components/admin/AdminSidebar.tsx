'use client';

import React from 'react';

interface SidebarProps {
  activePage?: string;
  onSelectPage?: (page: string) => void;
  activeTab?: string;
  setActiveTab?: (tab: string) => void;
}

export const AdminSidebar: React.FC<SidebarProps> = ({
  activePage,
  onSelectPage,
  activeTab,
  setActiveTab,
}) => {
  const currentActive = activePage || activeTab || 'home';
  const handleSelect = onSelectPage || setActiveTab || (() => {});

  const tabs = [
    { id: 'home', label: 'Trang Chủ (Homepage)', icon: '🏠' },
    { id: 'about', label: 'Trang Giới Thiệu (About Us)', icon: 'ℹ️' },
    { id: 'products', label: 'Trang Thành Tựu Y Khoa', icon: '🏆' },
    { id: 'alliance', label: 'Mạng Lưới Liên Minh', icon: '🌐' },
    { id: 'contact', label: 'Trang Liên Hệ (Contact)', icon: '📞' },
  ];

  return (
    <aside className="w-full bg-slate-900 text-white rounded-2xl p-5 shadow-sm font-utm-helve border border-slate-800 space-y-6">
      <div>
        <div className="flex items-center gap-3 pb-4 mb-4 border-b border-slate-800">
          <div className="w-9 h-9 rounded-xl bg-[#0b7f7c] flex items-center justify-center font-bold text-white text-base">
            🏥
          </div>
          <div>
            <h1 className="font-bold text-xs text-white uppercase tracking-wider">DANH MỤC TRANG CON</h1>
            <p className="text-[10px] text-slate-400">Chọn trang để chỉnh sửa nội dung</p>
          </div>
        </div>

        <nav className="space-y-1.5">
          {tabs.map((tab) => {
            const isActive = currentActive === tab.id;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => handleSelect(tab.id)}
                className={`w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-bold transition text-left ${
                  isActive
                    ? 'bg-[#0b7f7c] text-white shadow-md'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                }`}
              >
                <span className="text-base">{tab.icon}</span>
                <span>{tab.label}</span>
              </button>
            );
          })}
        </nav>
      </div>

      <div className="pt-4 border-t border-slate-800 text-[11px] text-slate-400 space-y-2">
        <a
          href="/vi"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-1.5 text-amber-400 hover:underline font-bold"
        >
          <span>🔗 Xem Website Công khai ↗</span>
        </a>
      </div>
    </aside>
  );
};
