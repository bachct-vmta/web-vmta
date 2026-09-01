import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('Creating MediaAsset table in dev.db...');

  await prisma.$executeRawUnsafe(`
    CREATE TABLE IF NOT EXISTS MediaAsset (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      filename TEXT NOT NULL,
      url TEXT NOT NULL,
      mime_type TEXT DEFAULT 'image/png',
      size_bytes INTEGER DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
  `);

  console.log('MediaAsset table created successfully!');

  // Seed default existing images into MediaAsset if empty
  const countRes: any[] = await prisma.$queryRawUnsafe(`SELECT COUNT(*) as count FROM MediaAsset;`);
  const count = Number(countRes[0]?.count || 0);

  if (count === 0) {
    const initialImages = [
      { filename: 'logo-vmta.png', url: '/images/home/header/logo-vmta.png' },
      { filename: 'banner-bg.png', url: '/images/home/hero/banner-bg.png' },
      { filename: 'about-hero.png', url: '/images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png' },
      { filename: 'lien-minh-du-lich-y-te-ra-mat.jpg', url: '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg' },
      { filename: 'implant.jpg', url: '/images/specialties/nha-khoa/implant.jpg' },
      { filename: 'veneer.jpg', url: '/images/specialties/nha-khoa/veneer.jpg' },
      { filename: 'orthodontics.jpg', url: '/images/specialties/nha-khoa/orthodontics.jpg' },
      { filename: 'intro-dental.jpg', url: '/images/specialties/nha-khoa/intro-dental.jpg' },
      { filename: 'contact-section.jpg', url: '/images/contact/section-image.jpg' },
    ];

    for (const img of initialImages) {
      await prisma.$executeRawUnsafe(
        `INSERT INTO MediaAsset (filename, url, mime_type) VALUES (?, ?, 'image/jpeg');`,
        img.filename, img.url
      );
    }
    console.log('Seeded initial images into MediaAsset library!');
  }
}

main()
  .catch(console.error)
  .finally(async () => {
    await prisma.$disconnect();
  });
