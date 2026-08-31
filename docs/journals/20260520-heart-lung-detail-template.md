# Heart-Lung Detail Template

## Summary

Implemented `/vi/ghep-dong-thoi-tim-phoi/` as admin-editable medical case detail, not static clone.

## Changed

- Added `detail_content` JSON support on `medical_case_translations`.
- Extended Medical Case admin form with detail page fields.
- Added public route/controller method/view for heart-lung case detail.
- Added local cloned visual assets under `public/images/heart-lung-transplant/`.
- Added detail CSS module and Vite import.
- Added idempotent seed data for the case.
- Added focused feature test for detail route render.

## Follow-up Alignment — 21/05/2026

- Restored the four production images in `Vì sao chọn Việt Nam?` cards.
- Reused the local CTA banner image for the final consultation block instead of the teal fallback block.
- Kept the image slots template-owned so existing seeded/admin text still renders without data rewrites.
- Realigned `Thành tựu y khoa đột phá` with production: wider panels, overlapping lung image, larger proportional icons, and stethoscope icon for the final outcome item.
- Converted the `Thành tựu y khoa đột phá` section from custom `heart-lung-*` selectors to Tailwind utility classes in the Blade template.

## Validation

- `php artisan migrate`
- `php artisan db:seed --class=Packages\\Content\\Database\\Seeders\\MedicalCaseSeeder`
- `npm run build`
- `php artisan test tests/Feature/Content/MedicalAchievementPageTest.php`
- `agent-browser` desktop + mobile screenshots

## Unresolved Questions

- None.
