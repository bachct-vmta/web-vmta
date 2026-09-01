import React from 'react';
import { LayoutWrapper } from '@/components/layout/LayoutWrapper';

export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const currentLocale = locale === 'en' ? 'en' : 'vi';

  return (
    <LayoutWrapper locale={currentLocale}>
      {children}
    </LayoutWrapper>
  );
}
