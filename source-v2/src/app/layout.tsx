import React from 'react';
import '@/app/globals.css';
import { Be_Vietnam_Pro } from 'next/font/google';

const beVietnamPro = Be_Vietnam_Pro({
  weight: ['300', '400', '500', '600', '700', '800'],
  subsets: ['vietnamese', 'latin'],
  display: 'swap',
  variable: '--font-be-vietnam',
});

export const metadata = {
  title: 'VMTA - Liên Minh Du Lịch Y Tế Việt Nam',
  description: 'Cầu nối nâng tầm du lịch y tế Việt Nam, mang dịch vụ chăm sóc sức khỏe chất lượng quốc tế đến du khách trong và ngoài nước.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi" className={`${beVietnamPro.variable}`}>
      <body className="min-h-screen flex flex-col bg-white text-slate-800 antialiased font-sans">
        {children}
      </body>
    </html>
  );
}
