import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

function serialize(obj: any) {
  return JSON.stringify(obj, (key, value) =>
    typeof value === 'bigint' ? value.toString() : value, 2
  );
}

async function dumpMenu() {
  console.log('=== MENU ITEMS ===');
  const items = await prisma.$queryRaw`SELECT * FROM menu_items`;
  console.log(serialize(items));

  console.log('=== MENU ITEM TRANSLATIONS ===');
  const trans = await prisma.$queryRaw`SELECT * FROM menu_item_translations`;
  console.log(serialize(trans));
}

dumpMenu().finally(() => prisma.$disconnect());
