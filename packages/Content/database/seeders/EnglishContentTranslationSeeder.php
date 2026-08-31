<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\AboutSection;
use Packages\Content\Src\Models\AchievementSection;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Models\MedicalCase;

/**
 * Updates EN translation rows with proper English content for the major public-facing
 * sections (HomeSection, AboutSection, AchievementSection, MedicalCase). Idempotent:
 * uses translateOrNew('en')->fill() — VI rows are not touched.
 *
 * Why a dedicated seeder: the original seeders carry "TODO: translate to English"
 * placeholders that just clone VI content. This seeder replaces those placeholders
 * with real English text so /en/ pages render properly without manual admin work.
 *
 * Run: php artisan db:seed --class=Packages\\Content\\Database\\Seeders\\EnglishContentTranslationSeeder
 */
class EnglishContentTranslationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedHomeSections();
            $this->seedAboutSections();
            $this->seedAchievementSections();
            $this->seedMedicalCases();
        });
    }

    private function seedHomeSections(): void
    {
        $map = [
            HomeSectionPosition::Hero->value => [
                'title' => 'Vietnam Medical Tourism Alliance',
                'subtitle' => 'Chief Architect of a National-Scale Medical Tourism Ecosystem',
                'body' => 'Vietnam\'s official medical tourism alliance — a closed-loop operating model linking top-tier hospitals, premium resorts and operating technology partners.',
                'cta_label' => 'Join the ecosystem',
                'cta_url' => '/en/contact',
                'items' => [
                    ['label' => 'USA',      'value' => '10 000'],
                    ['label' => 'Cambodia', 'value' => '10 000'],
                    ['label' => 'France',   'value' => '10 000'],
                    ['label' => 'Germany',  'value' => '10 000'],
                ],
            ],
            HomeSectionPosition::Values->value => [
                'title' => 'CORE VALUES',
                'items' => [
                    ['icon' => 'icon-1.png', 'title' => 'International Standards', 'body' => 'An independent Alliance Standards framework ensures every hospital and resort meets rigorous criteria for clinical excellence, service quality and patient safety.'],
                    ['icon' => 'icon-2.png', 'title' => 'Centralized Coordination', 'body' => 'A single operations hub orchestrates the entire patient journey — from consult through treatment to recovery — for a seamless, uninterrupted experience.'],
                    ['icon' => 'icon-3.png', 'title' => 'Humane Medicine', 'body' => 'Deep medical expertise paired with restorative wellness environments — addressing recovery of body, mind and spirit together.'],
                ],
            ],
            HomeSectionPosition::About->value => [
                'title' => 'ABOUT VMTA',
                'body' => "More than a service — VMTA is an Operating System for Medical Tourism. It is Vietnam's pioneering strategic alliance connecting leading hospitals, premium resorts and coordination technology platforms.\n\nWe design and operate personalised healthcare journeys where every detail is calibrated against clinical data and user experience.",
                'cta_label' => 'Learn more',
                'cta_url' => '/en/about',
                'items' => [
                    ['bullet' => 'Connecting medical resources'],
                    ['bullet' => 'Standardising the entire journey'],
                    ['bullet' => 'Optimising treatment and recovery outcomes'],
                ],
            ],
            HomeSectionPosition::Solutions->value => [
                'title' => 'KEY SOLUTIONS',
                'items' => [
                    ['icon' => 'icon-1.png', 'title' => 'Alliance Assessment Process', 'body' => 'A rigorous partner-vetting workflow guarantees consistent standards across the network.'],
                    ['icon' => 'icon-2.png', 'title' => 'Central Coordination Hub', 'body' => 'A central coordination system delivers a seamless journey across providers.'],
                    ['icon' => 'icon-3.png', 'title' => 'Healthcare & Wellness Integration', 'body' => 'Integrated solutions combining clinical protocols with restorative environments.'],
                ],
            ],
            HomeSectionPosition::VisionMission->value => [
                'title' => 'VISION',
                'subtitle' => 'MISSION',
                'body' => 'To become the gold standard for Medical Tourism in Vietnam — establishing the country as a priority destination on the global medical map.',
                'items' => [
                    ['audience' => 'For clients', 'body' => 'Delivering a safe, premium healthcare journey with end-to-end care.'],
                    ['audience' => 'For partners', 'body' => 'Building an intelligent coordination platform that connects hospitals, resorts and technology.'],
                    ['audience' => 'For the industry', 'body' => 'Shaping a Medical Tourism ecosystem that elevates Vietnam globally.'],
                ],
            ],
            HomeSectionPosition::Benefits->value => [
                'title' => 'MULTI-LAYERED BENEFITS',
                'items' => [
                    [
                        'audience' => 'For clients',
                        'subtitle' => 'A reassuring, high-quality healing journey',
                        'bullets' => [
                            'Pre-departure medical assessment',
                            'Fully personalised experience',
                            'No need to coordinate disparate services',
                            'Quality assured at every touchpoint',
                        ],
                        'image_url' => 'benefits/row-1.jpg',
                    ],
                    [
                        'audience' => 'For partners (Hospitals / Resorts)',
                        'subtitle' => 'Optimise resources and elevate positioning',
                        'bullets' => [
                            'Access international patient pipelines',
                            'Standardise operations to SLA-grade service',
                            'Increase capacity utilisation and efficiency',
                            'Elevate brand standing within the ecosystem',
                        ],
                        'image_url' => 'benefits/row-2.jpg',
                    ],
                ],
            ],
            HomeSectionPosition::Technology->value => [
                'title' => 'TECHNOLOGY & DIFFERENTIATION',
                'items' => [
                    [
                        'title' => 'Operations Hub — the brain of the alliance',
                        'bullets' => [
                            'Intelligent intake-record system',
                            'Real-time itinerary coordination',
                        ],
                    ],
                    [
                        'title' => 'Data-driven recovery management',
                        'bullets' => [
                            'Post-treatment data management',
                            'Healthcare analytics and personalisation',
                        ],
                    ],
                ],
            ],
            HomeSectionPosition::WhyVN->value => [
                'title' => 'WHY VIETNAM & WHY VMTA',
                // Schema constraint: items count must match VI (3)
                'items' => [
                    ['title' => 'World-class medical capability', 'body' => 'Vietnamese physicians have mastered advanced techniques on par with global leaders.'],
                    ['title' => 'Competitive cost', 'body' => 'High clinical quality at a fraction of US/EU costs.'],
                    ['title' => 'Restorative environment', 'body' => 'Pristine beaches, mountains and culture make Vietnam ideal for recovery.'],
                ],
            ],
        ];

        foreach ($map as $position => $payload) {
            $section = HomeSection::where('position', $position)->first();
            if (! $section) {
                continue;
            }
            $section->translateOrNew('en')->fill($payload)->save();
        }
    }

    private function seedAboutSections(): void
    {
        $map = [
            AboutSectionPosition::Hero->value => [
                'title' => 'VMTA — Chief Architect of Vietnam\'s Medical Tourism Ecosystem',
                'body' => 'Vietnam\'s pioneering strategic alliance connecting top-tier hospitals, premium resorts and coordination technology platforms into a single, accountable medical tourism operating system.',
            ],
            AboutSectionPosition::WhoAre->value => [
                'title' => 'Who is VMTA?',
                'body' => 'VMTA is the official Medical Tourism Alliance of Vietnam — designing and operating personalised healthcare journeys backed by clinical data and end-to-end experience standards.',
            ],
            AboutSectionPosition::CoreValues->value => [
                'title' => 'VMTA core values',
                // Schema constraint: items size must be 3 (UpdateAboutSectionRequest::coreValuesRules)
                'items' => [
                    ['title' => 'Integrity', 'body' => 'Quality and transparency at every touchpoint.'],
                    ['title' => 'Excellence', 'body' => 'Top-tier medical standards across the network.'],
                    ['title' => 'Empathy', 'body' => 'Human-centred care for body, mind and spirit.'],
                ],
            ],
            AboutSectionPosition::HowWorks->value => [
                'title' => 'How VMTA works',
                'items' => [
                    ['step' => '01', 'title' => 'Pre-assessment', 'body' => 'Medical history review and itinerary planning.'],
                    ['step' => '02', 'title' => 'Treatment', 'body' => 'Specialised care at accredited alliance hospitals.'],
                    ['step' => '03', 'title' => 'Recovery', 'body' => 'Wellness recovery at partner resorts under medical monitoring.'],
                    ['step' => '04', 'title' => 'Follow-up', 'body' => 'Data-driven post-treatment monitoring.'],
                ],
            ],
            AboutSectionPosition::Difference->value => [
                'title' => 'WHAT MAKES VMTA DIFFERENT',
                'body' => 'A vertically integrated operating system — from medical vetting to wellness recovery — orchestrated by a single accountable coordination hub.',
            ],
            AboutSectionPosition::WhyChoose->value => [
                'title' => 'WHY CHOOSE VMTA',
                'items' => [
                    ['title' => 'Quality-vetted', 'body' => 'Every hospital and resort meets Alliance Standards.'],
                    ['title' => 'Personalised', 'body' => 'Each journey is tailored to your clinical and personal needs.'],
                    ['title' => 'End-to-end', 'body' => 'Single point of accountability across the full journey.'],
                    ['title' => 'Cost-effective', 'body' => 'High clinical quality at competitive pricing.'],
                ],
            ],
            AboutSectionPosition::StartWithUs->value => [
                'title' => 'Start your journey with VMTA',
                'body' => 'Talk to our coordinator to design the right medical-tourism journey for you.',
            ],
        ];

        foreach ($map as $position => $payload) {
            $section = AboutSection::where('position', $position)->first();
            if (! $section) {
                continue;
            }
            $section->translateOrNew('en')->fill($payload)->save();
        }
    }

    private function seedAchievementSections(): void
    {
        $map = [
            AchievementSectionPosition::Hero->value => [
                'title' => "Outstanding Medical Achievements\nin Vietnam",
                'subtitle' => 'VIETNAMESE MEDICINE — RISING ON THE GLOBAL MAP',
                'body' => "Vietnam is steadily asserting its position on the world's medical map, with breakthrough techniques and humane care.\n\nFrom complex transplants to high-precision orthopaedic procedures, Vietnamese teams are matching international standards.\n\nLet medical excellence open new chapters of life.",
                // Preserve icon_path from original seeder.
                'items' => [
                    ['icon_path' => 'stat-heart.png',    'value' => '500+', 'label' => "Successful complex\ntransplant cases"],
                    ['icon_path' => 'stat-hospital.png', 'value' => '24/7', 'label' => "International coordination\nand support"],
                    ['icon_path' => 'stat-people.png',   'value' => '15+',  'label' => "Years of practice in\nadvanced technology"],
                ],
            ],
            AchievementSectionPosition::Capabilities->value => [
                'title' => 'VIETNAM — MEDICAL CAPABILITY REACHING GLOBAL STANDARDS',
                // Preserve icon_path from original seeder.
                'items' => [
                    ['icon_path' => 'icon-doctors.png',           'title' => "EXPERIENCED\nMEDICAL TEAM",      'body' => 'Trained locally and internationally, continuously updating modern techniques'],
                    ['icon_path' => 'icon-technique.png',         'title' => "Mastery of\ncomplex techniques", 'body' => 'Successfully performing world-class surgeries'],
                    ['icon_path' => 'icon-modern-tech.png',       'title' => "Application of\nmodern tech",    'body' => 'From 3D, AI to robotics and advanced sensors'],
                    ['icon_path' => 'icon-trust-destination.png', 'title' => 'TRUSTED DESTINATION',            'body' => 'International-standard expertise with optimal cost'],
                ],
            ],
            AchievementSectionPosition::Assurance->value => [
                'title' => 'VMTA — ASSURANCE FOR A SAFE MEDICAL JOURNEY',
                // Preserve icon_path from original seeder — template renders per-item icons.
                'items' => [
                    ['icon_path' => 'icon-plan.png',       'body' => 'Understand treatment plans before deciding'],
                    ['icon_path' => 'icon-select.png',     'body' => 'Choose the most suitable medical facility'],
                    ['icon_path' => 'icon-coordinate.png', 'body' => 'Coordinated support throughout the journey'],
                    ['icon_path' => 'icon-quality.png',    'body' => 'Peace of mind with our service-quality commitment'],
                ],
            ],
        ];

        foreach ($map as $position => $payload) {
            $section = AchievementSection::where('position', $position)->first();
            if (! $section) {
                continue;
            }
            $section->translateOrNew('en')->fill($payload)->save();
        }
    }

    private function seedMedicalCases(): void
    {
        $map = [
            'ghep-dong-thoi-tim-phoi' => [
                'slug' => 'simultaneous-heart-lung-transplant',
                'title' => 'Simultaneous Heart — Lung Transplantation',
                'subtitle' => 'A turning point on Vietnam\'s organ-transplant medical map',
                'intro' => 'Successfully delivered by Vietnamese teams — a complex procedure historically reserved for a handful of global centres.',
                'col1_items' => [
                    'First combined heart-lung transplant in Vietnam',
                    'Multi-specialty surgical team',
                    'World-class perfusion and ICU protocols',
                    'Real-time international coordination',
                ],
                'col2_items' => [
                    'Survival outcomes meeting global benchmarks',
                    'Reduced rejection rates with personalised protocols',
                    'Shorter ICU stay vs. published averages',
                    'Comprehensive long-term follow-up',
                ],
                'col3_body' => 'Vietnamese medical capability is no longer regional — it is internationally competitive. This case proves what is possible when alliance hospitals, advanced technology and coordinated care align.',
                'detail_content' => [
                    'hero_eyebrow' => 'BREAKTHROUGH CASE',
                    'hero_title' => 'Simultaneous Heart — Lung Transplantation',
                    'hero_highlight' => 'A landmark moment for Vietnamese medicine',
                    'hero_body' => 'A complex multi-organ transplant delivered by a Vietnamese multidisciplinary team — bringing world-class capability home.',
                    'cta_label' => 'Free consultation',
                    'hospital_name' => 'Alliance teaching hospital',
                    'time_value' => '12+ hours of synchronised surgery',
                    'intro_body' => 'Heart — lung transplantation is one of the most complex procedures in modern medicine. Performed successfully in Vietnam, it signals a new chapter where the country can offer truly advanced clinical care.',
                    'reason_items' => [
                        ['title' => 'MULTI-SPECIALTY COORDINATION', 'body' => 'Cardio-thoracic, transplant, ICU and perfusion teams operating in lockstep.'],
                        ['title' => 'PERSONALISED CARE', 'body' => 'Each patient receives an individualised treatment and recovery plan.'],
                        ['title' => 'WORLD-CLASS TEAM', 'body' => 'Surgeons trained at leading global transplant centres.'],
                        ['title' => 'OPTIMAL COST', 'body' => 'High-end care at a fraction of US/EU prices.'],
                    ],
                    'breakthrough_left_items' => [
                        ['title' => 'Combined heart-lung organ procurement', 'body' => 'Synchronised donor harvest preserving both organs intact.'],
                        ['title' => 'Single-stage transplant surgery', 'body' => 'Both organs implanted in a single co-ordinated operation.'],
                    ],
                    'breakthrough_right_items' => [
                        ['title' => 'Survival on par with global benchmarks', 'body' => 'Outcomes matching published international centres.'],
                        ['title' => 'Long-term follow-up programme', 'body' => 'Data-driven rejection monitoring and rehabilitation.'],
                    ],
                    'breakthrough_note' => 'Each step is co-ordinated by VMTA — from medical assessment through surgery to wellness recovery.',
                    'choice_items' => [
                        ['title' => 'TOP CLINICAL TEAM', 'body' => 'Vietnamese specialists with international transplant training.'],
                        ['title' => 'MODERN INFRASTRUCTURE', 'body' => 'OR, ICU and lab equipment matching global standards.'],
                        ['title' => 'COMPETITIVE COST', 'body' => 'Premium care priced for accessibility.'],
                        ['title' => 'HOLISTIC RECOVERY', 'body' => 'Restorative environments at partner resorts.'],
                    ],
                    'process_items' => [
                        ['body' => 'Medical history review and remote assessment.'],
                        ['body' => 'Pre-departure imaging and lab tests.'],
                        ['body' => 'Surgery at an alliance hospital with multidisciplinary team.'],
                        ['body' => 'Post-op ICU then transition to wellness recovery.'],
                    ],
                    'form_title' => 'Consult our coordinator',
                    'form_body' => 'Share a few details and we will design the right treatment plan for you.',
                    'cta_title' => "Request consultation\nfor a treatment plan tailored to you",
                    'cta_body' => "One right decision\ncan change a whole life",
                    'cta_points' => [
                        'Free initial consultation',
                        'Confidential medical review',
                        '24/7 international coordinator',
                    ],
                ],
            ],
            'ghep-than-tu-than' => [
                'title' => 'Autologous Kidney Transplantation',
                'subtitle' => 'Reimplanting a patient\'s own kidney to preserve function',
                'intro' => 'A rare procedure performed at alliance hospitals — preserving renal function and avoiding lifelong immunosuppression.',
                'col1_items' => [
                    'Patient-own donor — zero rejection risk',
                    'Avoids long-term immunosuppression',
                    'Microvascular reconstruction expertise',
                    'Specialist multidisciplinary team',
                ],
                'col2_items' => [
                    'Excellent post-op kidney function',
                    'Faster recovery vs. allograft',
                    'No graft-versus-host complications',
                    'Strong long-term outcomes',
                ],
                'col3_body' => 'Autologous transplantation demonstrates how Vietnamese specialists turn rare clinical needs into reproducible, life-changing procedures.',
            ],
            'thay-khop-hang-in-3d' => [
                'title' => 'Total Hip Replacement with 3D-Printed Technology',
                'subtitle' => 'Personalised implants engineered to each patient\'s anatomy',
                'intro' => 'Patient-specific 3D-printed hip components deliver superior fit, function and longevity over standard implants.',
                'col1_items' => [
                    'Patient-specific anatomical scan',
                    'In-house 3D printing of titanium components',
                    'Robotic-assisted placement',
                    'Minimally invasive approach',
                ],
                'col2_items' => [
                    'Better range of motion',
                    'Reduced wear and revision rates',
                    'Faster post-op recovery',
                    'Lower complication rates',
                ],
                'col3_body' => 'Personalised orthopaedics is a marker of modern medical maturity — Vietnam now offers world-class implant engineering at home.',
            ],
        ];

        foreach ($map as $parentSlug => $payload) {
            $case = MedicalCase::where('slug', $parentSlug)->first();
            if (! $case) {
                continue;
            }
            $case->translateOrNew('en')->fill($payload)->save();
        }
    }
}
