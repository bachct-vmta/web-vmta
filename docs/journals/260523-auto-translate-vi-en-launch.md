# Auto-Translate VI→EN Feature Launch

**Date**: 2026-05-23
**Severity**: Low
**Component**: Content (Posts, Pages, Categories admin forms)
**Status**: Resolved

## What Happened

Shipped `POST /admin/translate` endpoint proxying Chrome Translation API (translate-pa.googleapis.com/v1/translateHtml). Added "🌐 Dịch từ Tiếng Việt" button to EN tabs in admin forms. Users now auto-fill English fields from Vietnamese via one click.

## Key Decisions

1. **Chrome's public API** — No vendor lock-in, works server-side without Chrome-specific headers. Key stored in `.env` only.
2. **Backend proxy** — API key never exposed to browser. Wrapped in try/catch for ConnectionException.
3. **Dynamic locale indexing** — `array_search('vi', $locales)` instead of hardcoded [0]/[1]. Prevents silent mis-translation if locale order shifts.
4. **DOM queries** — `getElementsByName()` avoids quote escaping inside x-data attributes.
5. **CKEditor integration** — Access via `textarea._ckEditor.getData()`/`setData()` (instance stored on DOM element).

## Technical Details

**Files changed**: TranslationController (new), routes/admin.php, config/services.php, .env/.env.example, three _fields.blade.php views.

**Edge case handled**: Conditional `@if($locale === 'en')` in Blade injects Alpine state only for EN tab, avoiding wasted DOM nodes.

## Lessons Learned

- Dynamic array_search > hardcoded indexes. One line prevents weeks of debugging if requirements shift.
- Third-party API wrapper logic belongs in controller — makes it swappable (e.g., DeepL later).
- CKEditor instance access requires DOM element property, not jQuery or classic selectors.

## Unresolved

None. Code reviewer findings (ConnectionException, locale indexes) already addressed. Posts SEO fields gap is pre-existing, out of scope.
