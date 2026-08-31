# Achievement Page Editor — Implementation Journal

**Date:** 2026-05-20
**Plan:** `plans/260520-2204-achievement-page-editor/`
**Status:** Implemented + verified

## Goal

Đưa page `/{locale}/thanh-tuu-y-khoa` (Thành tựu Y khoa) từ hard-coded blade sang admin CMS, copy pattern Home/About/Alliance + bảng riêng `medical_cases` cho 3 case lâm sàng động.

## Decisions

| Quyết định | Rationale |
|---|---|
| Slug `achievement` / perm `achievement.manage` | English ngắn gọn, khớp pattern alliance/about/home |
| 3 positions (Hero/Capabilities/Assurance) | Khớp visual sections của page, items range cố định 3/4/4 |
| Bảng riêng `medical_cases` + CRUD độc lập | Cases là N (không cố định), có schema khác sections, CRUD đầy đủ thuận tiện hơn items[] JSON |
| Tab Cases embed list table | 1 entry point cho admin, link sang form create/edit |
| Lang file tách `achievement.php` | Cleaner hơn nested trong `content.php` |
| `icon_path` trong items[] thay vì chỉ `icon_media_id` | Seeder dùng được path tĩnh, không cần MediaFile records — visual khớp bản gốc ngay sau seed |
| Helper `$itemIcon($item, $hardFallback)` inline blade | KISS, không global helper; precedence media_id > icon_path > fallback |
| Hero body 1 textarea (paragraphs split `\n\n`) | Đơn giản hơn 3 input riêng; blade `preg_split` để render |
| Items shape | Hero stats: `{icon_media_id, icon_path, value, label}` × 3. Caps: `{icon_media_id, icon_path, title, body}` × 4. Assurance: `{icon_media_id, icon_path, body}` × 4 |

## Files

**Phase 1 — Migrations & Models** (9 files):
- `database/migrations/2026_05_20_000005…000008_*` (4 migrations)
- `src/Enums/AchievementSectionPosition.php`
- `src/Models/{AchievementSection, MedicalCase}.php`
- `src/Models/Translations/{AchievementSectionTranslation, MedicalCaseTranslation}.php`

**Phase 2 — Repos & Service** (5 files + 1 modified):
- `src/Repositories/Interfaces/{AchievementSection, MedicalCase}RepositoryInterface.php`
- `src/Repositories/Eloquent/{AchievementSection, MedicalCase}Repository.php`
- `src/Services/AchievementPageService.php` (cache + validateItems)
- `src/Providers/ContentServiceProvider.php` — 3 imports + 2 bindings + 1 singleton

**Phase 3 — Admin Section Editor** (10 files):
- `src/Http/Controllers/Admin/AchievementSectionController.php` (index + update with InvalidArgument → 422)
- `src/Http/Requests/Admin/UpdateAchievementSectionRequest.php` (per-position match)
- `resources/views/admin/achievement/{index, _fieldset-hero, _fieldset-capabilities, _fieldset-assurance, _fieldset-cases, _locale-input, _items-repeater}.blade.php`
- `resources/lang/{vi,en}/achievement.php`

**Phase 4 — Cases CRUD** (4 files):
- `src/Http/Controllers/Admin/MedicalCaseController.php` (6 actions, index redirect)
- `src/Http/Requests/Admin/{Store, Update}MedicalCaseRequest.php`
- `resources/views/admin/achievement/cases/form.blade.php` (Alpine.js dynamic col1/col2 items)

**Phase 5 — Wiring** (3 files modified):
- `routes/admin.php` (group achievement với 8 routes)
- `packages/Core/configs/permissions.php` (achievement.manage)
- `ContentServiceProvider.php` (SidebarItem priority 28, icon `medical_services`)

**Phase 6 — Public Render** (3 files modified):
- `src/Http/Controllers/Public/MedicalAchievementController.php` (inject service)
- `resources/views/public/pages/thanh-tuu-y-khoa.blade.php` (rewrite consume DB, giữ CSS classes)
- `configs/content.php` (medical_case_columns config)

**Phase 7 — Seeder** (2 files + 2 modified):
- `database/seeders/AchievementSectionSeeder.php` (3 sections + 3 cases vi+en)
- `database/migrations/2026_05_20_000009_add_detail_content_*.php` (idempotent)
- `database/seeders/DatabaseSeeder.php` (register)
- Blade helper `$itemIcon`

## Smoke test results

- `php artisan migrate` — 4 bảng OK
- `php artisan db:seed --class=AchievementSectionSeeder` — 3+6 sections, 3+6 cases, idempotent verified
- Public `/vi/thanh-tuu-y-khoa` — HTTP 200, 3 stats + 3 cases + 4 capabilities + 4 assurance render
- Public `/en/thanh-tuu-y-khoa` — HTTP 200, EN content render
- Cache hit lần 2 ~30ms
- Admin `/admin/achievement` — redirect (auth gate OK)
- 8 admin routes registered
- DI container resolve Controller + Service + Repo OK
- `validateItems(Hero, [1,2])` throw đúng

## Surprises / Lessons

1. **Hook reminder noise:** Every Write fired a "naming guidance" hook reminder. PHP files use PascalCase per PSR-4 — convention OK, no action needed each time.
2. **Migration column duplicate:** User added `detail_content` to model fillable + casts in parallel with my work; column was added out-of-band. Wrapped migration in `Schema::hasColumn` check → idempotent.
3. **Out-of-band extensions:** User iteratively added case detail page pieces (`detail_content` json, `findActiveBySlug` repo, `showHeartLung` controller action, detail lang keys, `MedicalCaseSeeder`). All compatible with Phase 7 scope.
4. **CSS user override preserved:** User had earlier removed `border-r` from `$valueCardClass`, `$capabilityCardClass`, `$assuranceItemClass`. Blade rewrite carefully kept these intact.
5. **Items icon fallback:** Original blade hard-coded `stat-heart.png` as fallback for ALL stats — refactored to per-item `icon_path` so visual matches without MediaFile uploads.

## Open follow-ups (not in this round)

- CTA per-case (override hard-coded "XEM CHI TIẾT" / "NHẬN TƯ VẤN") — out of scope, plan noted
- SEO meta editable từ admin — out of scope
- Detail page CMS (case detail rendering) — being built in parallel by user (out of plan scope)
- Coordinator role permission `achievement.manage` — not assigned; only Super Admin (`['*']`)
- MediaFile-based icon upload UI — current admin uses numeric Media ID input; future enhancement
