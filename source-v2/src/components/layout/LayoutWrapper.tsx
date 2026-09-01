'use client';

import React from 'react';
import { usePathname } from 'next/navigation';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { ChatbotWidget } from '@/components/common/ChatbotWidget';

interface LayoutWrapperProps {
  children: React.ReactNode;
  locale: string;
}

export const LayoutWrapper: React.FC<LayoutWrapperProps> = ({ children, locale }) => {
  const pathname = usePathname();
  const isAdminRoute = pathname?.includes('/admin');

  if (isAdminRoute) {
    return <>{children}</>;
  }

  return (
    <>
      <Header locale={locale} />
      <main className="flex-1">{children}</main>
      <Footer locale={locale} />
      <ChatbotWidget locale={locale} />
    </>
  );
};
