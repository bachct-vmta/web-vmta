import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function dumpAll() {
  console.log('=== ABOUT SECTIONS ===');
  const about = await prisma.$queryRaw`SELECT * FROM about_section_translations`;
  console.log(JSON.stringify(about, null, 2));

  console.log('=== ALLIANCE SECTIONS ===');
  const alliance = await prisma.$queryRaw`SELECT * FROM alliance_section_translations`;
  console.log(JSON.stringify(alliance, null, 2));

  console.log('=== CONTACT SECTIONS ===');
  const contact = await prisma.$queryRaw`SELECT * FROM contact_section_translations`;
  console.log(JSON.stringify(contact, null, 2));

  console.log('=== PAGES ===');
  const pages = await prisma.$queryRaw`SELECT * FROM page_translations`;
  console.log(JSON.stringify(pages, null, 2));
}

dumpAll().finally(() => prisma.$disconnect());
