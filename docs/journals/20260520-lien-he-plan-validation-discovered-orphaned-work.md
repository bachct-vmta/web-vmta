# Lien-He Plan Validation — Discovered 70% Work Already Done

**Date**: 2026-05-20 16:09
**Severity**: Low
**Component**: Contact/Partner page rebuild, inquiry routing
**Status**: Resolved — plan superseded + revised (TDD verify-only phases replace rebuild)

## What Happened

User asked to fix `/vi/lien-he` contact page to match reference layout. Scout found stale plan `260520-1500-lien-he-contact-page-rebuild/` (status pending) with 404 root cause documented. Brainstorm session decided fresh scrape-driven rebuild, created new plan `260520-1609-lien-he-rebuild-via-scrape/` with 5 phases (Scrape → DB → Route → UI → Visual Loop). During **plan validate full-tier**, discovered: Phase 2 (DB migrations) and Phase 3 (routing + controller) were **already implemented in a different session** and never marked complete.

## The Brutal Truth

Shipped a plan to rebuild work that was already done. The stale 404 screenshot (6.5K bytes) made us assume the page was broken, but `curl /vi/lien-he` returns HTTP 200. Brainstorm + Scout phases saw "status: pending" on old plan but skipped the obvious check: "does this code actually exist in the codebase right now?" We'd have re-implemented PartnerController, migration, and slug routing—14 hours of work for a 0.5-hour verification task.

## Technical Details

**Hidden completed work:**
- `InquirySource::PartnerInquiry` enum entry exists
- Migration `2026_05_20_100004_add_partner_fields_to_inquiries_table.php` applied (industry, company_name columns)
- `Inquiry::$fillable` already includes partner fields
- `PartnerController.php` (POST /partner endpoint) exists, handles submission
- `PartnerRequest.php` validation rules defined
- Route slug mapping: `packages/Inquiry/src/Providers/InquiryServiceProvider:89` defines `['vi' => ['contact' => 'lien-he', 'partner' => 'doi-tac']]`
- Content catch-all exclusions (`.where('slug', '!', ...'lien-he|khan-cap|contact')`) prevent collision
- HTTP 200 response confirmed live

**Orphan artifacts discovered:**
- `packages/Inquiry/routes/public.php` exists but is never `include()`d — provider uses inline route registration, making the file dead code

**Stale/misleading:**
- Old plan marked "pending" but implementation was complete
- Screenshot `laravel-lien-he-top.png` (404) predates actual fix, used as proof of broken state

## What We Tried

1. Brainstorm: decided to scrape vmta.test + rebuild from scratch (legitimate approach, wrong premise)
2. Plan: scaffolded 5 phases with blockedBy chain (correct structure, wrong scope)
3. Validate: grepped 20+ claims — Phase 2 (migration check) returned matches immediately
4. Recovery: marked old plan `status: superseded`, revised new plan: Phase 2/3 become verify-only + Pest tests + cleanup

## Root Cause Analysis

**Why brainstorm/scout didn't catch this:**

1. Old plan had `status: pending` — we trusted the status label instead of verifying claims
2. Stale screenshot was convenient proof of "broken" state; we didn't curl the live endpoint
3. Scout phase read the old plan's *conclusion* (route 404 root cause) but didn't verify if the subsequent fix was actually committed
4. No diff check between old plan start-state and current HEAD — mental model was frozen at plan-creation time

**Systemic issue:** Plan status field is a memory of intent, not ground truth. Codebase is the source of truth.

## Lessons Learned

1. **Always verify plan claims via grep/curl before committing to rebuild.** "Status: pending" ≠ "not done" — check the repo state. 15-min verification saved 14 hours of re-work.
2. **Screenshot artifacts have shelf life.** Reference images should include timestamp or be regenerated fresh. A 6.5K 404 PNG is ground truth only if you trust the photo date.
3. **Full-tier validate catches this.** `/ck:plan validate` did find the orphan file, confirmed migration exists, and proved route works. This is its real value: claim-by-claim audit of live state.
4. **Verify-only phases are legitimate.** When implementation is done but tests are missing, convert rebuild phase to "verify existing code + write Pest tests + cleanup dead files." Keeps the plan structure, eliminates re-work.
5. **Old plan tracking matters.** Setting `supersededBy:` link helps future developer understand why old plan was abandoned, not just that it's stale.

## Outstanding

- DB migration applied? Verify via `php artisan migrate:status` in dev (Phase 2 verify task)
- Font fallback plan: custom fonts in ref (vmta.test) may not match Laravel font-variables (`font-sharp-bo`, `font-utm-helve`) — need to check computed styles
- Clean up `packages/Inquiry/routes/public.php` (dead file, Phase 3 cleanup)

## Next Steps

1. Execute Phase 2 (Pest tests for PartnerInquiry DB fields) — verify applied migration
2. Execute Phase 3 (Pest tests for /doi-tac route) — verify controller + slug mapping
3. Delete orphan `packages/Inquiry/routes/public.php`
4. Scrape vmta.test CSS + execute Phase 4 (UI delta patches, ~30% new code instead of full rebuild)
5. Visual diff loop: use agent-browser to iterate against reference until ~95% match
