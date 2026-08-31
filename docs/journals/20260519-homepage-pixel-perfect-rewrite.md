# Homepage Pixel-Perfect Rewrite — VMTA.vn Complete

**Date**: 2026-05-19 17:16
**Severity**: Medium
**Component**: HomepageSection/PublicSite rendering
**Status**: Resolved (pending asset finalization + about-page CMS)

## What Happened

Shipped complete homepage rewrite across 4 phases + visual iteration. 10 commits, 231/231 tests pass. Enum cardinality (9→8 cases), 8 public partials, Tailwind v4 @theme tokens, dynamic HLS.js + marquee CSS, seeded VI content from vmta.vn, admin CRUD 8 fieldsets with variable hero marquee (4-10 items).

## The Brutal Truth

TDD passed but visual fidelity was ~70% on first agent-browser check. Tests only validate markup substrings — caught syntax, missed design. Blunt realization: pixel-perfect requires screenshot diff tests OR reference images in subagent context. Wrote ~40% more markup than needed because partials were generic Tailwind instead of reverse-engineered Flatsome theme styling. Wasted 4 hours on visual iteration that should've been prevented by better brief.

## Technical Details

**Critical bugs caught by code-review:**
- CTA routes hardcoded `/lien-he` instead of `route('contact.show')` (3 places, 404 risk)
- `safeIframeUrlRule` regex: `^https://(www\.)?(youtube\.com/embed|player\.vimeo\.com)/|^https?://.*\.m3u8(\?|$)` — strict allowlist
- Hero marquee variable cardinality (4-10) validated via `expectedItemsRange()` contract
- Bullets sub-array: `translations.{locale}.items.*.bullets.*` rules

**Synthetic UUID blindspot:** Plan asset URLs (f4f514ed-abcd-1234-5678…webp) fabricated, missed by 2 reviewers, caught on wget download attempt.

**withoutVite() suite-wide:** Pragmatic fix avoids Vite manifest 500s in tests, but future Vite assertions will silently no-op — risk flagged.

## Lessons Learned

1. **Visual TDD needs screenshots.** Markup assertions pass, design fails. Next time: attach live reference screenshot to subagent prompt or instruct load via agent-browser first.
2. **Validate plan artifacts.** Grep source HTML for asset URLs before committing to download script.
3. **Route helpers > hardcoded URLs.** Use `route('name.show')` everywhere, not string literals.
4. **Ownership boundaries matter.** Phase 1 touching Phase 4 file (UpdateHomeSectionRequest) to fix PHP fatal worked but confused handoff.

## Outstanding

- 3/25 assets missing (wget limit, Flatsome plugin binaries unreachable)
- About page `/vi/gioi-thieu` CMS not created (runtime 404 until seeded)
- Visual sign-off: Bebas Neue diacritics, flag image substitutes, hero BG content match (~88% current)
- Newsletter hidden consent — GDPR compliance decision pending
