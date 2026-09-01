import React from 'react';
import Link from 'next/link';
import { prisma } from '@/lib/prisma';
import { MedicalPackagesClient } from '@/components/common/MedicalPackagesClient';

export default async function SpecialtiesPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const isVi = locale === 'vi';

  let categories: any[] = [];
  let packages: any[] = [];
  let allianceMembers: any[] = [];

  try {
    categories = await prisma.$queryRawUnsafe(`SELECT * FROM MedicalCategory ORDER BY type ASC, id ASC;`);
    packages = await prisma.$queryRawUnsafe(`SELECT * FROM MedicalPackage WHERE is_active = 1 ORDER BY id DESC;`);
    allianceMembers = await prisma.$queryRawUnsafe(`SELECT * FROM AllianceMember WHERE is_active = 1;`);
  } catch (err) {
    console.error('Error querying medical packages from db:', err);
  }

  return (
    <div className="bg-white font-sans space-y-0 pb-20">
      {/* Hero Header */}
      <section className="relative h-[280px] overflow-hidden bg-white">
        <img
          src="/images/home/hero/banner-bg.png"
          alt=""
          className="absolute inset-0 h-full w-full object-cover opacity-25"
        />
        <div className="absolute inset-0 bg-white/50"></div>
        <div className="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 py-8">
          <h1 className="text-3xl md:text-5xl font-extrabold uppercase text-[#0b7f7c]">
            {isVi ? 'Y TẾ & DU LỊCH' : 'HEALTHCARE & MEDICAL TOURISM'}
          </h1>
          <p className="text-sm font-bold text-[#0b7f7c] mt-1">
            {isVi ? 'CHUYÊN KHOA Y KHOA & CÁC GÓI NGHỈ DƯỠNG PHỤC HỒI CHUẨN VMTA' : 'SPECIALTIES & SLA ACCREDITED WELLNESS RECOVERY PACKAGES'}
          </p>
          <nav className="mt-3 text-xs text-[#0b7f7c] font-semibold" aria-label="breadcrumb">
            <Link href={`/${locale}`} className="hover:underline">
              {isVi ? 'Trang chủ' : 'Home'}
            </Link>
            <span className="mx-2">/</span>
            <span>{isVi ? 'Y tế & Du lịch' : 'Healthcare & Tourism'}</span>
          </nav>
        </div>
      </section>

      {/* Interactive Medical Packages Client with Booking Modal */}
      <MedicalPackagesClient
        categories={categories}
        packages={packages}
        allianceMembers={allianceMembers}
        locale={locale}
      />
    </div>
  );
}
