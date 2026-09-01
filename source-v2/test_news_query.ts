import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  const posts: any[] = await prisma.$queryRawUnsafe(`SELECT * FROM Post WHERE is_published = 1 ORDER BY id DESC;`);
  console.log('Posts count:', posts.length);
  console.log('First post:', posts[0]);
}

main().catch(console.error).finally(() => prisma.$disconnect());
