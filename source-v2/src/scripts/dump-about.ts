import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function dumpAbout() {
  console.log('=== ABOUT SECTIONS ===');
  const about = await prisma.$queryRaw`SELECT * FROM about_section_translations`;
  console.log(JSON.stringify(about, null, 2));
}

dumpAbout().finally(() => prisma.$disconnect());
