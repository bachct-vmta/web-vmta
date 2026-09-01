import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('Upgrading Post table schema in dev.db with Per-locale Slugs and SEO Metadata...');

  const columnsToAdd = [
    `ALTER TABLE Post ADD COLUMN slug_vi TEXT;`,
    `ALTER TABLE Post ADD COLUMN slug_en TEXT;`,
    `ALTER TABLE Post ADD COLUMN status TEXT DEFAULT 'published';`,
    `ALTER TABLE Post ADD COLUMN meta_title_vi TEXT;`,
    `ALTER TABLE Post ADD COLUMN meta_title_en TEXT;`,
    `ALTER TABLE Post ADD COLUMN meta_description_vi TEXT;`,
    `ALTER TABLE Post ADD COLUMN meta_description_en TEXT;`,
    `ALTER TABLE Post ADD COLUMN meta_keywords_vi TEXT;`,
    `ALTER TABLE Post ADD COLUMN meta_keywords_en TEXT;`,
  ];

  for (const sql of columnsToAdd) {
    try {
      await prisma.$executeRawUnsafe(sql);
    } catch (err: any) {
      // Column might already exist
    }
  }

  // Populate slug_vi and slug_en for existing posts
  await prisma.$executeRawUnsafe(`UPDATE Post SET slug_vi = slug WHERE slug_vi IS NULL;`);
  await prisma.$executeRawUnsafe(`UPDATE Post SET slug_en = slug WHERE slug_en IS NULL;`);

  console.log('Post schema upgraded successfully!');
}

main()
  .catch(console.error)
  .finally(async () => {
    await prisma.$disconnect();
  });
