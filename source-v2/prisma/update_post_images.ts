import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('Updating post image URLs in seed_modules.ts...');

  // Update Post 1 image url
  await prisma.$executeRawUnsafe(
    `UPDATE Post SET image_url = '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg' WHERE slug = 'thu-truong-bo-y-te-trao-quyet-dinh';`
  );

  // Update Post 2 image url
  await prisma.$executeRawUnsafe(
    `UPDATE Post SET image_url = '/images/news/making-vietnam-medical-tourism.webp' WHERE slug = 'ra-mat-he-sinh-thai-du-lich-y-te';`
  );

  console.log('Post image URLs updated successfully!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
