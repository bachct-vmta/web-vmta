import { PrismaClient } from '@prisma/client';
const prisma = new PrismaClient();
async function main() {
  const sections = await prisma.$queryRaw`SELECT * FROM CmsSection`;
  console.log('SECTIONS:', JSON.stringify(sections, null, 2));
  const translations = await prisma.$queryRaw`SELECT * FROM CmsSectionTranslation`;
  console.log('TRANSLATIONS COUNT:', (translations as any[]).length);
}
main().finally(() => prisma.$disconnect());
