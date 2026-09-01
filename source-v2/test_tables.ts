import { PrismaClient } from '@prisma/client';
const prisma = new PrismaClient();
async function main() {
  const tables = await prisma.$queryRaw`SELECT name FROM sqlite_master WHERE type='table'`;
  console.log('TABLES:', JSON.stringify(tables, null, 2));

  const sections = await prisma.$queryRaw`SELECT * FROM cmssection`;
  console.log('CMSSECTION COUNT:', (sections as any[]).length);
}
main().finally(() => prisma.$disconnect());
