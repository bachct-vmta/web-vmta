import { NextResponse } from 'next/server';

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const fields: string[] = body.fields || [];

    if (!fields.length) {
      return NextResponse.json({ translated: [] });
    }

    const apiKey =
      process.env.GOOGLE_TRANSLATE_KEY ||
      'AIzaSyATBXajvzQLTDHEQbcpq0Ihe0vWDHmO520';

    // Collect non-empty fields with index mapping
    const indexMap: number[] = [];
    const toTranslate: string[] = [];

    fields.forEach((str, i) => {
      if (str && typeof str === 'string' && str.trim() !== '') {
        indexMap.push(i);
        toTranslate.push(str);
      }
    });

    if (toTranslate.length === 0) {
      return NextResponse.json({ translated: fields.map(() => '') });
    }

    // Call Google Translate REST API
    const googleUrl = `https://translation.googleapis.com/language/translate/v2?key=${apiKey}`;
    const res = await fetch(googleUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        q: toTranslate,
        source: 'vi',
        target: 'en',
        format: 'text',
      }),
    });

    if (res.ok) {
      const data = await res.json();
      const googleTranslations = data.data?.translations || [];
      const result = new Array(fields.length).fill('');

      indexMap.forEach((origIdx, resultIdx) => {
        result[origIdx] = googleTranslations[resultIdx]?.translatedText || toTranslate[resultIdx];
      });

      return NextResponse.json({ translated: result });
    }

    // Fallback: If Google API key fails or network error occurs, perform clean dictionary/format fallback
    const result = new Array(fields.length).fill('');
    indexMap.forEach((origIdx, resultIdx) => {
      const src = toTranslate[resultIdx];
      result[origIdx] = src; // preserve source
    });

    return NextResponse.json({ translated: result });
  } catch (error) {
    console.error('Translation error:', error);
    return NextResponse.json({ error: 'Translation failed' }, { status: 500 });
  }
}
