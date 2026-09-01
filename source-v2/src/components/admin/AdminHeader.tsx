'use client';

import React from 'react';

interface AdminHeaderProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  onLogout: () => void;
}

export const AdminHeader: React.FC<AdminHeaderProps> = ({
  activeTab,
  setActiveTab,
  onLogout,
}) => {
  const navTabs = [
    { id: 'cms', label: '📝 THIẾT KẾ LANDING PAGE', desc: 'Sắp xếp khối ở Sidebar' },
    { id: 'specialties', label: '🩺 Y TẾ & DU LỊCH', desc: 'Quản lý Chuyên khoa & Gói dịch vụ' },
    { id: 'alliance_members', label: '🤝 MẠNG LƯỚI LIÊN MINH', desc: 'Quản lý 4 nhóm Hội viên VMTA' },
    { id: 'news', label: '📰 QUẢN LÝ TIN TỨC', desc: 'Đăng bài viết & sự kiện' },
    { id: 'media', label: '📁 QUẢN LÝ MEDIA', desc: 'Thư viện ảnh Cloud CDN' },
    { id: 'inquiries', label: '📩 YÊU CẦU & NEWSLETTER', desc: 'Tiếp nhận Leads & Email' },
    { id: 'chatbot', label: '🤖 CHATBOT & CSKH 1-1', desc: 'Kịch bản, Thống kê & Email CSKH' },
    { id: 'dashboard', label: '📊 DASHBOARD', desc: 'Đánh giá hiệu quả trang web' },
  ];

  return (
    <header className="bg-slate-900 text-white border-b border-slate-800 shadow-md font-utm-helve sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-4">
        {/* Brand Logo */}
        <div className="flex items-center gap-3">
          <div className="bg-[#0b7f7c] p-2 rounded-xl text-white font-black text-lg tracking-wider">
            VMTA
          </div>
          <div>
            <h1 className="text-sm font-bold uppercase text-white tracking-wide">
              HỆ THỐNG QUẢN TRỊ VMTA ADMIN CMS
            </h1>
            <p className="text-[11px] text-teal-400 font-medium">
              VIETNAM MEDICAL TOURISM ALLIANCE v2.5.0
            </p>
          </div>
        </div>

        {/* User Account & Logout */}
        <div className="flex items-center gap-3">
          <div className="text-right hidden sm:block">
            <p className="text-xs font-bold text-slate-200">Quản trị viên (Admin)</p>
            <p className="text-[10px] text-emerald-400 font-mono">🟢 Đã đăng nhập</p>
          </div>

          <button
            onClick={onLogout}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-red-600/90 text-white text-xs font-bold hover:bg-red-700 transition shadow-sm"
          >
            <span>🚪 ĐĂNG XUẤT</span>
          </button>
        </div>
      </div>

      {/* Feature Navigation Bar */}
      <div className="bg-slate-800/80 border-t border-slate-700/60 overflow-x-auto">
        <div className="max-w-7xl mx-auto px-4 flex items-center gap-2 py-2">
          {navTabs.map((tab) => {
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex flex-col items-start ${
                  isActive
                    ? 'bg-[#0b7f7c] text-white shadow-md'
                    : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'
                }`}
              >
                <span>{tab.label}</span>
                <span className={`text-[10px] ${isActive ? 'text-teal-200' : 'text-slate-400'}`}>
                  {tab.desc}
                </span>
              </button>
            );
          })}
        </div>
      </div>
    </header>
  );
};
