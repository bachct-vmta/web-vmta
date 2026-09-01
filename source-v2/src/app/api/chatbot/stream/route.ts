import { NextRequest, NextResponse } from 'next/server';
import { tourismApiClient } from '@/lib/services/TourismApiClient';

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { content, conversation_id } = body;

    if (!content) {
      return NextResponse.json({ error: 'Content is required' }, { status: 400 });
    }

    let activeConversationId = conversation_id;
    if (!activeConversationId) {
      activeConversationId = await tourismApiClient.createConversation();
    }

    const upstreamResponse = await tourismApiClient.streamMessage(activeConversationId, content);

    if (!upstreamResponse.ok || !upstreamResponse.body) {
      return NextResponse.json(
        { error: 'Failed to connect to upstream RAG AI service' },
        { status: upstreamResponse.status || 500 }
      );
    }

    return new Response(upstreamResponse.body, {
      headers: {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-cache, no-transform',
        Connection: 'keep-alive',
        'X-Conversation-Id': activeConversationId,
      },
    });
  } catch (error: unknown) {
    const message = error instanceof Error ? error.message : 'Internal server error';
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
