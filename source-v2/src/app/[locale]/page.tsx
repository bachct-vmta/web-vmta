import React from 'react';
import { HeroSection } from '@/components/home/HeroSection';
import { ValuesAndAboutSection } from '@/components/home/ValuesAndAboutSection';
import { SolutionsSection } from '@/components/home/SolutionsSection';
import { VisionMissionSection } from '@/components/home/VisionMissionSection';
import { BenefitsSection } from '@/components/home/BenefitsSection';
import { TechnologySection } from '@/components/home/TechnologySection';
import { WhyVNSection } from '@/components/home/WhyVNSection';
import { NewsTeaserSection } from '@/components/home/NewsTeaserSection';
import { getOrderedPageSections } from '@/lib/cms';

export default async function HomePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const orderedKeys = await getOrderedPageSections('home');

  const defaultKeys = ['hero', 'values', 'about', 'solutions', 'vision_mission', 'benefits', 'technology', 'why_vn', 'news'];
  const keysToRender = orderedKeys.length > 0 ? orderedKeys : defaultKeys;

  const renderedSet = new Set<string>();

  return (
    <div className="space-y-0 pb-0 bg-white">
      {keysToRender.map((key) => {
        if (renderedSet.has(key)) return null;

        if (key === 'hero') {
          renderedSet.add('hero');
          return <HeroSection key="hero" locale={locale} />;
        }
        if (key === 'values' || key === 'about') {
          renderedSet.add('values');
          renderedSet.add('about');
          return <ValuesAndAboutSection key="values_about" locale={locale} />;
        }
        if (key === 'solutions') {
          renderedSet.add('solutions');
          return <SolutionsSection key="solutions" locale={locale} />;
        }
        if (key === 'vision_mission') {
          renderedSet.add('vision_mission');
          return <VisionMissionSection key="vision_mission" locale={locale} />;
        }
        if (key === 'benefits') {
          renderedSet.add('benefits');
          return <BenefitsSection key="benefits" locale={locale} />;
        }
        if (key === 'technology') {
          renderedSet.add('technology');
          return <TechnologySection key="technology" locale={locale} />;
        }
        if (key === 'why_vn') {
          renderedSet.add('why_vn');
          return <WhyVNSection key="why_vn" locale={locale} />;
        }
        if (key === 'news') {
          renderedSet.add('news');
          return <NewsTeaserSection key="news" locale={locale} />;
        }
        return null;
      })}
    </div>
  );
}
