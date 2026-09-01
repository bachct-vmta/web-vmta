'use client';

import React, { useState, useEffect } from 'react';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { AdminSidebar } from '@/components/admin/AdminSidebar';
import { SectionEditor } from '@/components/admin/SectionEditor';
import { AdminDashboard } from '@/components/admin/AdminDashboard';
import { MedicalPackagesManager } from '@/components/admin/MedicalPackagesManager';
import { AllianceMembersManager } from '@/components/admin/AllianceMembersManager';
import { PostsManager } from '@/components/admin/PostsManager';
import { MediaManager } from '@/components/admin/MediaManager';
import { InquiriesManager } from '@/components/admin/InquiriesManager';
import { ChatbotManager } from '@/components/admin/ChatbotManager';

export default function AdminPage() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loadingAuth, setLoadingAuth] = useState(true);

  // Login form state
  const [username, setUsername] = useState('admin');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(true);
  const [loginError, setLoginError] = useState('');

  // Active top navigation tab
  const [activeNavTab, setActiveNavTab] = useState('cms');

  // Active page slug in CMS editor tab
  const [activePageSlug, setActivePageSlug] = useState('home');

  // Check saved login session on mount
  useEffect(() => {
    const savedToken = localStorage.getItem('vmta_admin_auth') || sessionStorage.getItem('vmta_admin_auth');
    if (savedToken === 'authenticated') {
      setIsAuthenticated(true);
    }
    setLoadingAuth(false);
  }, []);

  // Handle Login Submit
  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    setLoginError('');

    if ((username === 'admin' || username === 'vmta@vmta.vn') && (password === 'admin' || password === '123456')) {
      setIsAuthenticated(true);
      if (rememberMe) {
        localStorage.setItem('vmta_admin_auth', 'authenticated');
      } else {
        sessionStorage.setItem('vmta_admin_auth', 'authenticated');
      }
    } else {
      setLoginError('Tài khoản hoặc mật khẩu không chính xác. Mẫu: admin / 123456');
    }
  };

  // Handle Logout
  const handleLogout = () => {
    localStorage.removeItem('vmta_admin_auth');
    sessionStorage.removeItem('vmta_admin_auth');
    setIsAuthenticated(false);
  };

  if (loadingAuth) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center text-white font-utm-helve">
        <p className="text-sm font-bold">⏳ Đang tải hệ thống quản trị VMTA...</p>
      </div>
    );
  }

  // --- RENDER LOGIN FORM IF NOT AUTHENTICATED ---
  if (!isAuthenticated) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-900 via-[#064e4b] to-slate-950 flex items-center justify-center p-4 font-utm-helve">
        <div className="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
          <div className="bg-[#0b7f7c] p-8 text-center text-white space-y-2">
            <div className="mx-auto w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-3xl font-black mb-2">
              🏥
            </div>
            <h2 className="text-xl font-bold uppercase tracking-wide">ĐĂNG NHẬP TRANG QUẢN TRỊ</h2>
            <p className="text-xs text-teal-100">Hệ sinh thái Du lịch Y tế Việt Nam (VMTA)</p>
          </div>

          <form onSubmit={handleLogin} className="p-8 space-y-6">
            {loginError && (
              <div className="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold">
                {loginError}
              </div>
            )}

            <div className="space-y-1">
              <label className="block text-xs font-bold text-slate-700 uppercase">Tên đăng nhập / Email</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Nhập 'admin'..."
                className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#0b7f7c] outline-none"
                required
              />
            </div>

            <div className="space-y-1">
              <label className="block text-xs font-bold text-slate-700 uppercase">Mật khẩu</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Nhập '123456'..."
                className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#0b7f7c] outline-none"
                required
              />
            </div>

            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="rounded text-[#0b7f7c] focus:ring-[#0b7f7c] h-4 w-4"
                />
                <span>Ghi nhớ đăng nhập</span>
              </label>

              <span className="text-[11px] text-slate-400 font-mono">Mặc định: admin / 123456</span>
            </div>

            <button
              type="submit"
              className="w-full py-3.5 bg-[#0b7f7c] text-white font-bold uppercase text-xs rounded-xl shadow-lg hover:bg-[#086a67] transition"
            >
              🔐 ĐĂNG NHẬP HỆ THỐNG
            </button>
          </form>

          <div className="bg-slate-50 border-t border-slate-100 p-4 text-center text-[11px] text-slate-500">
            Hỗ trợ kỹ thuật: <a href="mailto:vmta@vmta.vn" className="text-[#0b7f7c] font-bold">vmta@vmta.vn</a>
          </div>
        </div>
      </div>
    );
  }

  // --- RENDER AUTHENTICATED DASHBOARD & HEADER MODULES ---
  return (
    <div className="min-h-screen bg-slate-100 font-utm-helve flex flex-col justify-between">
      <div>
        {/* Admin Header Navigation */}
        <AdminHeader
          activeTab={activeNavTab}
          setActiveTab={setActiveNavTab}
          onLogout={handleLogout}
        />

        <main className="max-w-7xl mx-auto px-4 py-8">
          {/* TAB 1: LANDING PAGE BUILDER & SECTION EDITOR */}
          {activeNavTab === 'cms' && (
            <div className="grid grid-cols-1 md:grid-cols-12 gap-8">
              <div className="md:col-span-3">
                <AdminSidebar
                  activePage={activePageSlug}
                  onSelectPage={setActivePageSlug}
                />
              </div>

              <div className="md:col-span-9">
                <SectionEditor pageSlug={activePageSlug} />
              </div>
            </div>
          )}

          {/* TAB 2: MEDICAL & TOURISM PACKAGES */}
          {activeNavTab === 'specialties' && <MedicalPackagesManager />}

          {/* TAB 3: ALLIANCE MEMBERS DIRECTORY (4 GROUPS) */}
          {activeNavTab === 'alliance_members' && <AllianceMembersManager />}

          {/* TAB 4: POSTS & NEWS */}
          {activeNavTab === 'news' && <PostsManager />}

          {/* TAB 5: MEDIA MANAGER & CLOUD LIBRARY */}
          {activeNavTab === 'media' && <MediaManager />}

          {/* TAB 6: INQUIRIES & NEWSLETTER */}
          {activeNavTab === 'inquiries' && <InquiriesManager />}

          {/* TAB 7: CHATBOT SCRIPTS & EMAIL ESCALATION */}
          {activeNavTab === 'chatbot' && <ChatbotManager />}

          {/* TAB 8: DASHBOARD STATS */}
          {activeNavTab === 'dashboard' && <AdminDashboard />}
        </main>
      </div>

      {/* Admin Footer */}
      <AdminFooter />
    </div>
  );
}
