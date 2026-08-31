# Lộ trình phát triển dự án

**Dự án:** VMTA_Laravel — Vietnam Medical Tourism Alliance (Phase 1)
**Trạng thái:** Phase 1 — Migration từ WordPress (`vmta.vn`) sang Laravel 12
**Cập nhật:** 20/05/2026

---

## 1. Bối cảnh

Trang `https://vmta.vn` hiện chạy WordPress + Flatsome (du lịch y tế). Phase 1 mục tiêu **thay thế hoàn chỉnh** sang Laravel 12 với kiến trúc monolith mô-đun trên `packages/Core` đã có, bổ sung 8 package nghiệp vụ.

Lộ trình chi tiết: [`plans/260517-vmta-migration-brainstorm/plan.md`](../plans/260517-vmta-migration-brainstorm/plan.md).

---

## 2. Tóm tắt trạng thái Core (v1.0 đã có)

### Hoàn thành ✅

- [x] `packages/Core` — Base classes (BaseModel, BaseController, BaseRepository, BaseService)
- [x] RBAC system (roles, permissions flat schema)
- [x] User/Role CRUD + permission directives Blade
- [x] Media Manager (Local + Google Drive + chunked upload)
- [x] Activity logging (audit trail)
- [x] Settings table (key/value/group) — chưa có UI editor hoàn chỉnh
- [x] Table Builder
- [x] Artisan commands: `make:package`, `make:table`, `chunks:clear`, `media:cleanup`

### Đang dùng để build Phase 1
Core không cần rewrite, chỉ **mở rộng**:
- Append permissions cho coordinator vào `packages/Core/configs/permissions.php`
- Seed thêm role `coordinator` trong `AdminSeeder`
- Mở rộng Settings (column `type`, `is_encrypted` qua migration phụ — Phase 6)

---

## 3. Lộ trình Phase 1 — VMTA Migration (~9.5 tuần)

### Phase 1: Setup Foundation — ⏳ IN PROGRESS

**Effort:** 1 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-01-setup-foundation.md`

**Deliverables:**
- [ ] Docs rewrite: PDR + system-architecture + roadmap (file này) → Medical Tourism
- [ ] Composer: `astrotomic/laravel-translatable` ^11, `mcamara/laravel-localization` ^2
- [ ] `packages/Localization` — middleware locale, language switcher Blade component
- [ ] `packages/Site` — layout public Blade + Tailwind/Alpine entry assets
- [ ] Core RBAC mở rộng — role `coordinator` + permissions `content.*`, `catalog.*`, `inquiry.*`, `media.*`
- [ ] `routes/web.php` wrap locale group
- [ ] Tailwind config + plugins (`@tailwindcss/typography`, `@tailwindcss/forms`)
- [ ] Smoke test: `/vi` `/en` render layout trống, coordinator không vào trang user/role

### Phase 2: Content CMS

**Effort:** 2 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-02-content-cms.md`

**Entity:** Page, Post (+ category, tag), Menu
**Mỗi entity translatable VI/EN**, dùng Core MediaManager picker.

### Phase 3: Catalog & Search

**Effort:** 2-3 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-03-catalog-search.md`

**Entity:** Specialty, Destination, Hospital, Doctor, Service, Package
**Search:** `laravel/scout` + `teamtnt/tntsearch`. Filter chuyên khoa × địa điểm.

### Phase 4: Inquiry Pipeline

**Effort:** 1 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-04-inquiry-pipeline.md`

**Forms:** Inquiry + Emergency. **Pipeline:** new → reviewing → contacted → closed/rejected.
Mail notify Coordinator. Export CSV. Anti-spam Phase 8.

### Phase 5: Chatbot Proxy ✅

**Effort:** 1.5 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-05-chatbot-proxy.md`

Proxy Tourism API + SSE relay. Alpine.js widget. 10 messages/session. Strict block khi user reject cookie.

**Completed 20/05/2026:**
- [x] `packages/Chatbot` created with server-side proxy pattern
- [x] TourismApiClient (Guzzle + JWT caching)
- [x] ChatbotSession model + migration + repository
- [x] ChatbotSessionService (Redis atomic counter + DB fallback)
- [x] ChatbotStreamRelay (SSE handler)
- [x] ChatbotController (GET /session, POST /message)
- [x] EnsureChatbotSession middleware (UUID cookie, 24h TTL)
- [x] Alpine.js floating widget + typewriter effect
- [x] Admin settings UI (GET|PUT /admin/chatbot/settings)
- [x] Rate limiting: 10 messages/session, Redis-backed with fallback
- [x] Dependencies: guzzlehttp/guzzle, predis/predis
- [x] Translations (VI/EN)

### Phase 6: Settings (extend Core) + Newsletter

**Effort:** 0.5 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-06-settings-newsletter.md`

Newsletter double opt-in. Settings UI helper + admin editor.

### Phase 7: Report & Metrics

**Effort:** 0.5 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-07-report-metrics.md`

3 metric: inquiries/day, pageview top 10, chatbot session count. Chart.js dashboard.
Schedule `metrics:flush` mỗi phút trong `routes/console.php`.

### Phase 8: Security Hardening

**Effort:** 1 tuần
**File:** `plans/260517-vmta-migration-brainstorm/phase-08-security-hardening.md`

2FA TOTP + recovery codes + Super Admin reset + artisan fallback.
Backup `spatie/laravel-backup` — local + S3-compatible.
PDPA cookie consent strict. SEO sitemap đa ngôn ngữ. Redis cache. Load test k6.

---

## 4. Out of Scope Phase 1 (xem PDR §13)

Booking/thanh toán, customer auth, member dashboard, medical records mã hoá, public REST API, bản đồ, mobile app, multi-tenant, marketplace plugin.

---

## 5. Milestones & Checkpoints

| Milestone | Phase hoàn thành | Mục tiêu kiểm tra |
|---|---|---|
| **M1: Foundation ready** | Phase 1 | `/vi` `/en` render, 2 role hoạt động, Tailwind build |
| **M2: Content live** | Phase 2 | Admin tạo page/post VI/EN, public render |
| **M3: Catalog browsable** | Phase 3 | Search + filter public hoạt động |
| **M4: Lead capture** | Phase 4 | Form inquiry → coordinator nhận mail + xử lý pipeline |
| **M5: Chat interactive** | Phase 5 | Widget chat hoạt động, SSE streaming, rate-limit OK |
| **M6: Ops ready** | Phase 6+7 | Settings editor, newsletter chạy, report chart đầy đủ |
| **M7: Production ready** | Phase 8 | 2FA admin, backup test restore, k6 baseline đạt SLA |

---

## 6. Theo dõi tiến độ

- **Plan files** trong `plans/260517-vmta-migration-brainstorm/` là nguồn sự thật về tiến độ chi tiết.
- **Project changelog** (`docs/project-changelog.md`) — ghi nhận từng deliverable hoàn thành.
- **Project manager skill** (`/ck:project-management`) — sync trạng thái phase files ↔ plan.md sau mỗi phase.

---

## 7. Risk Register

| Rủi ro | Mitigation |
|---|---|
| Translation thiếu khi nội dung mới | Fallback `vi`, admin báo missing key |
| Tourism API down | Proxy fallback message, monitor |
| Inquiry mail không gửi | Queue retry + DB log + admin notify |
| Performance catalog | Redis cache + scout index + pagination |
| 2FA admin lock-out | Recovery codes + Super Admin reset + artisan fallback |
| Migration WP data | Out of scope Phase 1, nhập tay Phase 2-3 |

---

## 8. Version History

- **v2.0** — 17/05/2026 — Rewrite cho Phase 1 VMTA Migration
- **v1.0** — Roadmap ban đầu cho CMS Framework (SMM)

---

*Tài liệu cập nhật mỗi khi kết thúc một phase.*
