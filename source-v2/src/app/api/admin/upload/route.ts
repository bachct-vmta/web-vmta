import { NextResponse } from 'next/server';
import { writeFile, mkdir } from 'fs/promises';
import path from 'path';
import { prisma } from '@/lib/prisma';

export async function POST(req: Request) {
  try {
    const formData = await req.formData();
    const file = formData.get('file') as File | null;

    if (!file) {
      return NextResponse.json({ error: 'No file uploaded' }, { status: 400 });
    }

    const appEnv = process.env.APP_ENV || 'development';
    const useCloudStorage = process.env.USE_CLOUD_STORAGE === 'true' || appEnv === 'production';

    const cloudName = process.env.NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME;
    const uploadPreset = process.env.CLOUDINARY_UPLOAD_PRESET;
    const apiKey = process.env.CLOUDINARY_API_KEY;

    let fileUrl = '';
    let isCloud = false;

    // OPTION 2: Upload to Cloud Storage Cloudinary CDN if configured or in Production mode
    if (useCloudStorage && cloudName && cloudName !== 'demo' && uploadPreset && uploadPreset !== 'unsigned_preset') {
      try {
        const cloudinaryData = new FormData();
        cloudinaryData.append('file', file);
        cloudinaryData.append('upload_preset', uploadPreset);
        if (apiKey) cloudinaryData.append('api_key', apiKey);

        const cloudRes = await fetch(`https://api.cloudinary.com/v1_1/${cloudName}/image/upload`, {
          method: 'POST',
          body: cloudinaryData,
        });

        if (cloudRes.ok) {
          const cloudResult = await cloudRes.json();
          if (cloudResult.secure_url) {
            fileUrl = cloudResult.secure_url;
            isCloud = true;
          }
        }
      } catch (cloudErr) {
        console.warn('Cloudinary upload error:', cloudErr);
      }
    }

    // DEV Fallback Local Storage
    if (!fileUrl) {
      const bytes = await file.arrayBuffer();
      const buffer = Buffer.from(bytes);

      const uploadDir = path.join(process.cwd(), 'public', 'uploads');
      await mkdir(uploadDir, { recursive: true });

      const safeName = `${Date.now()}-${file.name.toLowerCase().replace(/[^a-z0-9.]/g, '-')}`;
      const filePath = path.join(uploadDir, safeName);

      await writeFile(filePath, buffer);

      fileUrl = `/uploads/${safeName}`;
    }

    // Record to MediaAsset table in CSDL
    try {
      await prisma.$executeRawUnsafe(
        `INSERT INTO MediaAsset (filename, url, mime_type, size_bytes) VALUES (?, ?, ?, ?);`,
        file.name, fileUrl, file.type || 'image/png', file.size || 0
      );
    } catch (dbErr) {
      console.warn('Failed to insert into MediaAsset table:', dbErr);
    }

    return NextResponse.json({ success: true, url: fileUrl, isCloud, appEnv });
  } catch (error) {
    console.error('Error uploading file:', error);
    return NextResponse.json({ error: 'Failed to upload file' }, { status: 500 });
  }
}
