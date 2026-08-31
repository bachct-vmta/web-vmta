# Phase 5: Chatbot-Proxy SSE Streaming Implementation

**Date**: 2026-05-20 14:30
**Severity**: Medium
**Component**: packages/Chatbot (new), ChatbotSessionService, TourismApiClient
**Status**: Resolved

## What Happened

Shipped `packages/Chatbot` from scratch — a full server-side SSE proxy for Tourism Chatbot API with admin UI, session management, and Alpine.js widget. 19 new files across controller, service, model, migrations, views, and frontend code.

## The Technical Reality

Got blindsided by a subtle state-refresh bug during code review: DB fallback to `$session->increment()` wasn't refreshing the in-memory Eloquent model, causing the controller to read stale session count. Added `$session->refresh()` after increment. Also discovered a double `markOverflow()` call in the catch block that we'd already executed in the service — removed the duplicate.

The widget had to be strategically hidden on Inquiry routes to avoid the UX nightmare of the chatbot suggesting "use the form" while the user is already filling the form.

## Architecture Decisions

**Session Counters:** Chose predis/predis over phpredis for atomic INCR despite phpredis already being configured — explicit composer dependency feels cleaner and less config-magic.

**Upstream Auth:** TourismApiClient caches JWT in Redis (`chatbot:upstream_token` key) with auto-retry on 401 — avoids token generation per request.

**Streaming:** Used response()->stream() + X-Accel-Buffering: no instead of EventSource because the endpoint requires POST with request body (filtering params). Widget handles ReadableStream manually with fetch.

**Admin Panel:** Built on Core Setting model rather than config-only approach — UI for managing active/inactive state and upstream URL.

## Known Gaps

Guzzle timeout=30s may choke on long SSE streams. Phase 6+ should evaluate timeout=0 or streaming-specific config. Hotline tel: link pending site.phone setting from Phase 6.

## Lessons

State refresh in Eloquent after mutating operations is non-obvious but critical. Double-check service/controller boundaries for duplicate side effects before shipping.

## Next Steps

- Monitor SSE stream timeouts in production (Phase 6)
- Integrate site.phone setting once Phase 6 completes
- Wire metric counter hooks (currently no-op in onComplete callback)
