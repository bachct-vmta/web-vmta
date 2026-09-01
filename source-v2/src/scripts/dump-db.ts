import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function dump() {
  console.log('=== SECTIONS ===');
  const sections = await prisma.$queryRaw`SELECT s.position, st.locale, st.title, st.subtitle, st.body, st.items FROM home_sections s JOIN home_section_translations st ON s.id = st.home_section_id`;
  console.log(JSON.stringify(sections, null, 2));

  console.log('=== POSTS ===');
  const posts = await prisma.$queryRaw`SELECT p.id, p.slug, p.image, pt.locale, pt.title, pt.excerpt FROM posts p JOIN post_translations pt ON p.id = pt.post_id`;
  console.log(JSON.stringify(posts, null, 2));

  console.log('=== MENUS ===');
  const menus = await prisma.$queryRaw`SELECT m.code, mi.id, mi.url, mit.locale, mit.title FROM menus m JOIN menu_items mi ON m.id = mi.menu_id JOIN menu_item_translations mit ON mi.id = mit.menu_item_id`;
  console.log(JSON.stringify(menus, null, 2));
}

dump().finally(() => prisma.$disconnect());
