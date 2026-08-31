# Tổng quan Dự án — Product Development Requirements (PDR)

**Dự án:** VMTA_Laravel — Vietnam Medical Tourism Alliance (Phase 1)
**Tác giả:** Nguyên Khôi
**Email:** ngnguyenkhoib4@gmail.com
**Cập nhật:** 17/05/2026

---

## 1. Tầm nhìn & Mục tiêu

### Tầm nhìn

Xây dựng **nền tảng giới thiệu dịch vụ du lịch y tế (medical tourism)** cho Vietnam Medical Tourism Alliance, thay thế site WordPress + Flatsome hiện tại tại `https://vmta.vn`. Hệ thống cung cấp:

- **Brochure website** đa ngôn ngữ (VI / EN) giới thiệu chuyên khoa, bệnh viện đối tác, điểm đến, gói khám
- **Catalog tra cứu** đa chiều (chuyên khoa × địa điểm × bệnh viện × bác sĩ × dịch vụ)
- **Lead capture pipeline** — inquiry form thường + emergency form, điều phối qua Coordinator
- **Chatbot tư vấn** kết nối Tourism API bên thứ 3 (proxy + SSE relay)
- **Newsletter + Settings + Report** nội bộ phục vụ điều phối viên

Kiến trúc **monolith mô-đun** trên `packages/Core` có sẵn (RBAC, Media, ActivityLog, TableBuilder) cộng thêm 8 package nghiệp vụ mới.

### Mục tiêu chính

1. **Thay thế hoàn chỉnh vmta.vn (WordPress)** với hiệu năng + bảo mật cấp doanh nghiệp
2. **Đa ngôn ngữ VI/EN** ngay từ Phase 1 (URL prefix, hreflang, language switcher)
3. **Pipeline inquiry → coordinator → liên hệ** rõ ràng, không thất thoát lead
4. **Chatbot proxy an toàn** — không lộ API key, kiểm soát rate-limit per session, tôn trọng cookie consent
5. **Tuân thủ PDPA Việt Nam** — cookie consent strict, audit log, 2FA admin
6. **Tận dụng tối đa Core** — RBAC, Media, ActivityLog, TableBuilder, Settings đã có

---

## 2. Người dùng mục tiêu

| Nhóm | Vai trò | Nhu cầu |
|------|--------|--------|
| **Khách truy cập public** | Bệnh nhân tiềm năng / người nhà | Đọc nội dung, lọc catalog, gửi inquiry, chat tư vấn |
| **Admin** | Chủ dự án / IT | Toàn quyền (user, role, settings, mọi nội dung) |
| **Coordinator** | Điều phối viên y tế | CRUD nội dung, xử lý inquiry, gửi email, không động user/role |
| **Đối tác (out of scope phase 1)** | Bệnh viện / phòng khám | Chưa có dashboard, dữ liệu nhập tay bởi Admin/Coordinator |

---

## 3. Tính năng chính (Phase 1)

### 3.1 Đa ngôn ngữ & SEO

- URL prefix `/vi/...`, `/en/...`, root `/` redirect locale mặc định
- Language switcher giữ nguyên path khi đổi locale
- `Accept-Language` fallback lần đầu vào site
- `hreflang` đầy đủ + sitemap đa ngôn ngữ (Phase 8)
- Translation cache (Laravel locale cache)
- Stack: `mcamara/laravel-localization` + `astrotomic/laravel-translatable`

### 3.2 Content CMS (Phase 2)

- **Page** — trang tĩnh (Trang chủ, Giới thiệu, Liên hệ, Điều khoản…)
- **Post** — bài viết blog/tin tức theo category + tag
- **Menu** — quản lý menu header/footer kéo thả
- Editor: TinyMCE / CKEditor với media picker từ Core MediaManager
- Mỗi nội dung đều translatable (VI/EN)

### 3.3 Catalog tra cứu (Phase 3)

6 entity chính, đều translatable + tham chiếu chéo:

| Entity | Mô tả |
|---|---|
| **Specialty** | Chuyên khoa (Tim mạch, Ung bướu, IVF…) |
| **Destination** | Điểm đến (TP.HCM, Hà Nội, Đà Nẵng, Singapore…) |
| **Hospital** | Bệnh viện đối tác (gallery, mô tả, dịch vụ) |
| **Doctor** | Bác sĩ (chuyên khoa, kinh nghiệm, bằng cấp) |
| **Service** | Dịch vụ y tế (khám tổng quát, tầm soát, phẫu thuật…) |
| **Package** | Gói khám / chương trình điều trị (giá tham khảo) |

- Search: `laravel/scout` + `teamtnt/tntsearch` (driver `tntsearch` cho VN tokenization)
- Filter: chuyên khoa × địa điểm × loại dịch vụ
- Detail page: gallery, mô tả, dịch vụ liên quan, CTA gửi inquiry

### 3.4 Inquiry Pipeline (Phase 4)

- **Inquiry form** — đặt câu hỏi/yêu cầu tư vấn (đính kèm package/service tham chiếu)
- **Emergency form** — luồng riêng, ưu tiên cao, hotline + email gấp
- **Pipeline trạng thái:** `new → reviewing → contacted → closed | rejected`
- Coordinator nhận thông báo email khi có inquiry mới
- Lịch sử trao đổi (note) gắn vào từng inquiry
- Export CSV theo khoảng thời gian
- Anti-spam: honeypot + rate-limit + reCAPTCHA v3 (Phase 8)

### 3.5 Chatbot Proxy (Phase 5)

- Widget Alpine.js trên trang public (toggle floating button)
- Proxy server-side gọi Tourism API bên thứ 3, **không expose API key ra client**
- SSE relay streaming response token-by-token
- Giới hạn **10 messages / session**, lưu hội thoại để Coordinator tham khảo
- Tôn trọng cookie consent: widget **không render** khi user reject (strict block)
- Settings: enable/disable, system prompt, max session length

### 3.6 Newsletter + Settings (Phase 6)

- **Newsletter** — subscribe form (email), double opt-in, danh sách trong admin
- **Settings extend Core** — thêm các key: hotline, chatbot handoff URL, chatbot config, social links
- Helper `setting('key')` + admin UI form editor (tận dụng Core SettingsController)

### 3.7 Report & Metrics (Phase 7)

3 chỉ số tối thiểu, chart admin dashboard:

1. **Inquiries / day** (new vs closed)
2. **Pageview top 10** (page slug + count)
3. **Chatbot session count + avg messages**

- Schedule `metrics:flush` mỗi phút trong `routes/console.php`
- Chart: Chart.js trên trang `/admin/reports`

### 3.8 Security & Hardening (Phase 8)

- **2FA admin** (TOTP) + 10 recovery codes/user
- Endpoint `Admin::resetTwoFactor(User)` cho Super Admin
- Artisan fallback `php artisan two-factor:reset {email}` (nếu Super Admin mất)
- **Backup** — `spatie/laravel-backup` lưu local + S3-compatible (AWS / DO Spaces / R2 / MinIO)
- **Audit log** mở rộng cho mọi action quản trị (Core ActivityLog đã có)
- **PDPA cookie consent** — strict block analytics/functional cookie khi reject
- **SEO** — sitemap đa ngôn ngữ, robots.txt, structured data JSON-LD
- **Redis cache** — page/translation/settings cache
- **Load test** — k6 baseline `/`, `/vi/specialty/...`, `/api/chat`

---

## 4. Yêu cầu phi chức năng (Non-Functional Requirements)

### Performance
- Page load public < 1.5s (cached page)
- Admin dashboard < 2s
- API chat first-token < 1s (relay sẵn sàng)
- Support ≥ 5,000 concurrent visitors qua cache + CDN tĩnh

### Security
- HTTPS bắt buộc production
- RBAC 2 role (Admin / Coordinator) — không hardcode quyền
- Sensitive data encrypted (`APP_ENC_KEY`) — chatbot API key, 2FA secret, backup credentials
- CSRF + rate-limit toàn bộ form public + admin
- File upload validate type + size (Core đã có)
- Activity audit trail trên mọi action quản trị

### Scalability
- Stateless backend (session in Redis)
- Queue jobs (database driver dev, Redis prod) cho mail + backup
- Modular package — mỗi feature 1 package độc lập

### Maintainability
- Code follows PSR-12 + Repository Pattern (per `docs/code-standards.md`)
- Tài liệu Tiếng Việt
- Test coverage ≥ 70% feature critical
- Semantic versioning
- Project changelog + roadmap maintained

### Database
- Dev: SQLite (file `database/database.sqlite`)
- Prod: MySQL 8 / MariaDB 10.6+
- Migrations versioned
- Seeders cho roles/permissions + sample content

### Deployment
- Environment-based config (`.env`)
- Zero-downtime migrations (additive only, no destructive in prod)
- Backup chạy daily 02:00 (`routes/console.php`)
- Health check endpoint (Phase 8)

---

## 5. Kiến trúc & Công nghệ

### Stack

| Layer | Công nghệ |
|-------|-----------|
| **Framework** | Laravel 12 |
| **Language** | PHP 8.2+ |
| **Database** | SQLite (dev), MySQL 8 (prod) |
| **Frontend** | Blade + Tailwind CSS + Alpine.js + Vite |
| **i18n** | `mcamara/laravel-localization` + `astrotomic/laravel-translatable` |
| **Search** | `laravel/scout` + `teamtnt/tntsearch` |
| **Mail** | SMTP driver-agnostic (provider chốt khi deploy) |
| **Backup** | `spatie/laravel-backup` (local + S3-compatible) |
| **Cache** | Database (dev), Redis (prod) |
| **Storage** | Local + Google Drive (Core đã có) |

### Package Layout

```
packages/
├── Core/              # Đã có — RBAC, Media, ActivityLog, TableBuilder, Settings
├── Localization/      # Phase 1 — i18n config, middleware, language switcher component
├── Site/              # Phase 1 — Blade layout public + Tailwind/Alpine entry assets
├── Content/           # Phase 2 — Page, Post, Menu
├── Catalog/           # Phase 3 — Specialty, Destination, Hospital, Doctor, Service, Package
├── Inquiry/           # Phase 4 — Inquiry + Emergency + pipeline + email
├── Chatbot/           # Phase 5 — proxy + SSE relay + Alpine widget
├── Newsletter/        # Phase 6 — subscribe + double opt-in
└── Report/            # Phase 7 — metrics + admin dashboard chart
```

**Lưu ý:** Settings không tạo package mới — extend `packages/Core` (Core đã có `settings` table + service).

### Data Flow

```
Public visitor
  ↓ /vi/specialty/tim-mach
mcamara middleware (set locale)
  ↓
Site layout + Catalog controller
  ↓
CatalogService → SpecialtyRepository
  ↓
Eloquent (translatable) → DB
  ↓
Blade view (Tailwind + Alpine) → response
```

```
Public visitor (chat)
  ↓ /api/chat (POST message)
ChatbotController → ChatbotService
  ↓ rate-limit per session (10 msgs)
Proxy → Tourism API (server-side, API key trong .env)
  ↓ SSE stream
Relay back to Alpine widget (token by token)
```

---

## 6. Công cụ & Quy trình

### Development Tools

- **Artisan Commands** (Core có sẵn): `make:package`, `make:table`, `chunks:clear`, `media:cleanup`
- **Phase 7+:** `metrics:flush`, `backup:run`, `sitemap:generate`, `two-factor:reset`
- **Laravel Pint** — code formatter
- **PHPUnit** — test runner
- **Vite** — frontend bundler

### Workflow
1. Đọc `plans/260517-vmta-migration-brainstorm/phase-NN-*.md`
2. Implement theo Implementation Steps
3. Smoke test thủ công + unit test critical
4. Update `docs/project-roadmap.md` + `docs/project-changelog.md` (nếu có)

### Commit
- Conventional commits: `feat(catalog):`, `fix(chatbot):`, `refactor(core):`…
- Không commit `.env`, credentials, backup files
- Không reference plan IDs/finding codes trong code/comment (chỉ trong plan file + PR description)

---

## 7. Definition of Done (Phase 1)

Feature được coi là **XONG** khi:

- [ ] Code implement đầy đủ Implementation Steps của phase
- [ ] Smoke test pass theo Success Criteria
- [ ] Unit test critical path xanh
- [ ] No breaking change trên `packages/Core` (hoặc documented + migration kèm)
- [ ] Works trên SQLite dev (target MySQL prod kiểm tra Phase 8)
- [ ] Coordinator role không truy cập được phạm vi Admin
- [ ] Composer + npm install không lỗi, `package:discover` sạch
- [ ] Vite build sản phẩm + hot reload OK

---

## 8. Yêu cầu bảo mật

### Authentication & Authorization
- Mật khẩu bcrypt (Core có sẵn)
- Session timeout 120 phút
- Password reset token expiry 60 phút
- Rate limit: 5 login fail → khóa 15 phút
- RBAC enforce ở middleware Core
- 2FA TOTP bắt buộc Admin (Phase 8)

### Data Protection
- Encrypt với `APP_ENC_KEY`:
  - Chatbot API key + provider credentials
  - 2FA secret + recovery codes hashed
  - Backup S3 credentials
- Không commit `.env`
- Inquiry data PII — không log raw vào activity log (chỉ log action + ID)

### API Security
- CSRF token mọi form public
- SQL injection prevention (Eloquent only, no raw query trừ migration)
- XSS prevention (Blade auto-escape, content editor sanitize)
- Upload validate type + size
- Rate limit `/api/chat`, `/inquiry`, `/newsletter/subscribe`

### Audit Trail
- Mọi action Coordinator/Admin log (who, when, what, IP) qua Core ActivityLog
- Retention 90 ngày, có thể export CSV (PDPA compliance)

---

## 9. Lộ trình Phase 1 (8 phases)

Chi tiết xem `plans/260517-vmta-migration-brainstorm/plan.md` + `docs/project-roadmap.md`.

| Phase | Mục tiêu | Effort |
|---|---|---|
| 1 | Setup Foundation — docs + RBAC + i18n + theme skeleton | 1 tuần |
| 2 | Content CMS — Page/Post/Menu | 2 tuần |
| 3 | Catalog — 6 entity + Scout/TNTSearch + filter | 2-3 tuần |
| 4 | Inquiry Pipeline — form + emergency + coordinator + email | 1 tuần |
| 5 | Chatbot Proxy — proxy + SSE relay + Alpine widget + 10-msg | 1.5 tuần |
| 6 | Newsletter + Settings (extend Core) | 0.5 tuần |
| 7 | Report 3 metric + admin dashboard chart | 0.5 tuần |
| 8 | 2FA + Backup + Audit PDPA + SEO + Redis + load test | 1 tuần |
| **Tổng** | | **~9.5 tuần** |

---

## 10. Chỉ số thành công

| Metric | Target |
|--------|--------|
| Page load public (cached) | < 1.5s |
| Admin dashboard | < 2s |
| Chat first-token | < 1s |
| Test coverage critical path | ≥ 70% |
| Uptime production | ≥ 99.9% |
| Inquiry response SLA | < 24h |
| Cookie consent compliance | 100% (PDPA strict) |

---

## 11. Rủi ro & Giải pháp

| Rủi ro | Xác suất | Tác động | Giải pháp |
|--------|---------|---------|----------|
| Tourism API bên thứ 3 thay schema/down | Medium | High | Proxy layer, error fallback, monitor |
| Translation thiếu khi nội dung mới | High | Medium | Fallback `vi`, admin báo missing key |
| Lead thất thoát do mail không gửi | Medium | High | Queue retry + DB log + notify |
| Cookie consent edge-case (reject sau khi đã chat) | Low | Medium | Disable widget + clear session khi reject |
| Migration WP → Laravel (Phase out-of-scope) | High | Medium | Phase 1 không migrate dữ liệu, nhập tay/seed |
| Performance degrade khi catalog lớn | Medium | Medium | Redis cache + scout index + pagination |
| 2FA reset abuse | Low | High | Audit log + chỉ Super Admin + artisan fallback hạn chế |

---

## 12. Constraints & Assumptions

### Constraints
- **Tech**: PHP 8.2+, Laravel 12, MySQL 8 prod
- **Solo dev**, timeline ~9.5 tuần
- **Stack chốt** không đổi (Blade + Tailwind + Alpine + Vite — không SPA)
- **Tourism API key** do bên đối tác cung cấp, lưu `.env`

### Assumptions
- Nội dung VI/EN do team Marketing chuẩn bị song song, nhập qua admin
- Server prod ≥ 4 vCPU / 8 GB RAM (đủ cho 5K visitors + chatbot)
- Email SMTP credentials chốt trước Phase 4 (cho inquiry mail)
- Admin có Super Admin account `admin@nguyenkhoi.dev` (Core seed)

---

## 13. Out of Scope (Phase 1)

Loại trừ rõ ràng, có thể vào Phase 2 sau:

- Booking online + thanh toán
- Customer auth (đăng ký/đăng nhập bệnh nhân)
- Member dashboard / hồ sơ cá nhân
- Medical records mã hoá (HIPAA-like)
- Public REST API cho đối tác
- Bản đồ tích hợp (Google Maps embed)
- Mobile app
- Multi-tenant (mỗi bệnh viện 1 site con)
- Marketplace plugin

---

## 14. Dependencies & Integrations

### External Services
- **Tourism API** (bên thứ 3) — chatbot
- **SMTP provider** — mail inquiry/newsletter/2FA
- **S3-compatible storage** — backup (AWS / DO / R2 / MinIO)
- **Google Drive** — optional media (Core đã hỗ trợ)
- **reCAPTCHA v3** — anti-spam form (Phase 8)

### Internal
- `packages/Core` (bắt buộc, đã có)
- 8 package nghiệp vụ mới (tạo dần qua Phase 1-7)

### Third-party Libraries chính
- `laravel/framework` ^12
- `astrotomic/laravel-translatable` ^11
- `mcamara/laravel-localization` ^2
- `laravel/scout` + `teamtnt/tntsearch`
- `spatie/laravel-backup`
- `pragmarx/google2fa-laravel` (Phase 8)

---

## 15. Approval & Version Control

| Bộ phận | Tên | Ký | Ngày |
|--------|------|----|----|
| Product Owner | TBD | | |
| Tech Lead | Nguyên Khôi | | 17/05/2026 |
| Ops | TBD | | |

**Version History:**
- v2.0 — Rewrite PDR cho Vietnam Medical Tourism Alliance (17/05/2026)
- v1.0 — Initial CMS Framework PDR

---

*Tài liệu này được cập nhật liên tục khi có thay đổi yêu cầu hoặc phạm vi dự án.*
