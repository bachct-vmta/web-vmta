'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';

interface HeaderProps {
  locale: string;
}

export const Header: React.FC<HeaderProps> = ({ locale }) => {
  const [mobileOpen, setMobileOpen] = useState(false);
  const pathname = usePathname();

  const switchLocale = locale === 'vi' ? 'en' : 'vi';
  const switchLocaleLabel = locale === 'vi' ? 'EN' : 'VI';

  const menuItems = [
    {
      label: locale === 'vi' ? 'GIỚI THIỆU' : 'ABOUT',
      href: `/${locale}/gioi-thieu`,
    },
    {
      label: locale === 'vi' ? 'Y TẾ - TRỊ LIỆU' : 'MEDICAL – THERAPY',
      href: `/${locale}/chuyen-khoa`,
    },
    {
      label: locale === 'vi' ? 'SẢN PHẨM' : 'PRODUCTS',
      href: `/${locale}/thanh-tuu-y-khoa`,
    },
    {
      label: locale === 'vi' ? 'MẠNG LƯỚI LIÊN MINH' : 'ALLIANCE NETWORK',
      href: `/${locale}/lien-minh-du-lich-y-te`,
    },
    {
      label: locale === 'vi' ? 'TIN TỨC' : 'NEWS',
      href: `/${locale}/tin-tuc`,
    },
    {
      label: locale === 'vi' ? 'LIÊN HỆ' : 'CONTACT',
      href: `/${locale}/lien-he`,
    },
  ];

  return (
    <header className="bg-white sticky top-0 z-50 border-b border-slate-200/80 shadow-sm py-2">
      <div className="relative mx-auto flex items-center max-w-7xl justify-between px-4">
        {/* Mobile Hamburger Button */}
        <button
          type="button"
          className="lg:hidden text-[#0b7f7c] hover:text-[#d31e45] p-2"
          onClick={() => setMobileOpen(!mobileOpen)}
          aria-label="Toggle Menu"
        >
          <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        {/* Logo */}
        <Link href={`/${locale}`} className="flex-shrink-0 flex items-center">
          <img src="/images/home/header/logo-vmta.png" alt="VMTA" className="h-14 sm:h-16 w-auto object-contain" />
        </Link>

        {/* Desktop Navigation */}
        <nav className="hidden lg:flex items-center gap-6 text-[0.875rem] font-bold uppercase tracking-wide">
          {menuItems.map((item) => {
            const isActive = pathname === item.href || (item.href !== `/${locale}` && pathname.startsWith(item.href));
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`transition-colors py-2 border-b-2 font-bold ${
                  isActive
                    ? 'text-[#d31e45] border-[#d31e45]'
                    : 'text-[#0b7f7c] border-transparent hover:text-[#d31e45]'
                }`}
              >
                {item.label}
              </Link>
            );
          })}
        </nav>

        {/* Desktop Lang Switcher */}
        <div className="hidden lg:flex items-center">
          <Link
            href={`/${switchLocale}`}
            className="text-xs font-bold px-2.5 py-1 rounded border border-[#0b7f7c] text-[#0b7f7c] hover:bg-[#0b7f7c] hover:text-white transition"
          >
            {switchLocaleLabel}
          </Link>
        </div>
      </div>

      {/* Mobile Drawer Backdrop */}
      {mobileOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/50 lg:hidden"
          onClick={() => setMobileOpen(false)}
        />
      )}

      {/* Mobile Sidebar */}
      {mobileOpen && (
        <div className="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white shadow-2xl lg:hidden">
          <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <Link href={`/${locale}`} onClick={() => setMobileOpen(false)}>
              <img src="/images/home/header/logo-vmta.png" alt="VMTA" className="h-10 w-auto" />
            </Link>
            <button
              type="button"
              onClick={() => setMobileOpen(false)}
              className="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 transition"
            >
              <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <nav className="flex-1 overflow-y-auto py-2">
            {menuItems.map((item) => {
              const isActive = pathname === item.href || (item.href !== `/${locale}` && pathname.startsWith(item.href));
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setMobileOpen(false)}
                  className={`block px-5 py-3 border-b border-slate-100 text-xs font-bold uppercase ${
                    isActive ? 'text-[#d31e45] bg-red-50/50' : 'text-[#0b7f7c]'
                  }`}
                >
                  {item.label}
                </Link>
              );
            })}
          </nav>

          <div className="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
            <Link
              href={`/${switchLocale}`}
              className="text-xs font-bold px-3 py-1.5 rounded border border-[#0b7f7c] text-[#0b7f7c]"
            >
              {switchLocaleLabel}
            </Link>
          </div>
        </div>
      )}
    </header>
  );
};
