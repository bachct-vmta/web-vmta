# VMTA Laravel — CMS/Admin Panel Framework

Khung công tác quản lý nội dung và bảng điều khiển toàn vẹn cho SMM Panel dựa trên Laravel 12, được xây dựng theo mô hình monolith mô-đun với Core Package tái sử dụng.

**Bản quyền:** Nguyên Khôi | Email: ngnguyenkhoib4@gmail.com

---

## Đặc điểm

- **Laravel 12** + PHP ^8.2 — Framework web hiện đại
- **Modular Monolith** — Core Package (`packages/Core`) + Local Composer packages
- **RBAC** — Role-Based Access Control với phân quyền linh hoạt
- **Media Manager** — Upload, quản lý file với hỗ trợ Google Drive
- **Chunked Upload** — Tải file lớn chia nhỏ
- **SQLite** — Database mặc định (hỗ trợ PostgreSQL, MySQL)
- **Table Builder** — Bảng dữ liệu động với lọc, sắp xếp, hành động
- **Activity Log** — Ghi nhật ký tự động mọi thao tác admin
- **Tailwind CSS + Vite** — Frontend hiện đại

---

## Khởi động nhanh

### Yêu cầu hệ thống

- PHP 8.2 trở lên
- Composer 2.0+
- Node.js 18+
- SQLite (hoặc MySQL/PostgreSQL)

### Cài đặt

```bash
# Clone repository
git clone <repository-url> vmta-laravel
cd vmta-laravel

# Cài đặt dependencies
composer install
npm install

# Sao chép file cấu hình
cp .env.example .env

# Tạo khóa ứng dụng
php artisan key:generate

# Tạo khóa mã hóa (bắt buộc cho APP_ENC_KEY)
php artisan key:generate --guard=app_enc

# Chạy migrations
php artisan migrate

# Seed dữ liệu ban đầu (tài khoản admin)
php artisan db:seed

# Build assets
npm run build

# Khởi động server
php artisan serve
```

### Tài khoản Admin mặc định

| Trường | Giá trị |
|-------|--------|
| Email | `admin@nguyenkhoi.dev` |
| Mật khẩu | `123456789` |
| Vai trò | Super Admin |

Truy cập: `http://localhost:8000/admin`

---

## Cấu hình môi trường

### Biến bắt buộc

```env
APP_NAME=VMTA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Bắt buộc — khóa mã hóa 32 ký tự hex
APP_ENC_KEY=32-char-hex-key-here

# Database (SQLite mặc định)
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# Media storage — local hoặc google_drive
MEDIA_STORAGE_DRIVER=local

# Admin routes (mặc định: /admin)
CORE_ADMIN_PREFIX=admin
CORE_ADMIN_ROUTE_NAME=admin
```

### Google Drive (tùy chọn)

Để sử dụng Google Drive làm storage:

```env
MEDIA_STORAGE_DRIVER=google_drive
GOOGLE_DRIVE_CLIENT_ID=your-client-id
GOOGLE_DRIVE_CLIENT_SECRET=your-client-secret
GOOGLE_DRIVE_REDIRECT_URI=http://localhost:8000/admin/media/google-drive/callback
```

---

## Quy tắc phát triển

### Repository Pattern (BẮT BUỘC)

Mọi tương tác với Model **PHẢI** qua Repository:

```php
// ❌ CẤM
$users = User::where('status', 'active')->get();

// ✅ ĐÚNG
$users = $this->userRepository->getActive();
```

Xem chi tiết tại `docs/code-standards.md`.

### Kế thừa Base Class

| Loại | Kế thừa |
|------|---------|
| Model | `BaseModel` |
| Controller | `BaseController` |
| Repository | `BaseRepository` |
| Service | `BaseService` |

### Dependency Injection

```php
// ✅ ĐÚNG
public function __construct(
    private UserRepository $userRepository
) {}
```

---

## Cấu trúc thư mục

```
VMTA_Laravel/
├── app/                    # Laravel main app (minimal)
├── bootstrap/              # Framework bootstrap
├── config/                 # Config files
├── database/               # Migrations, seeders
├── docs/                   # Tài liệu (Vietnamese)
│   ├── project-overview-pdr.md
│   ├── code-standards.md
│   ├── codebase-summary.md
│   ├── system-architecture.md
│   ├── project-roadmap.md
│   ├── deployment-guide.md
│   ├── guide.md            # Quy tắc phát triển package
│   └── development.md      # Chi tiết hướng dẫn
├── packages/Core/          # ⭐ Core Package — Nền tảng
│   ├── configs/            # Cấu hình RBAC, Media
│   ├── database/           # Migrations, seeders
│   ├── resources/          # Views, translations
│   ├── routes/             # Admin, auth, media routes
│   └── src/                # Source code
├── public/                 # Web root
├── resources/              # App views, assets
├── routes/                 # App routes
├── storage/                # Logs, uploads
├── tests/                  # Unit & feature tests
└── composer.json           # Dependencies

```

---

## Workflow phát triển Package

Core Package mẫu được đặt trong `packages/Core/`. Để tạo package mới:

```bash
php artisan make:package Report
```

Quy trình bắt buộc:

1. **Tạo Model** (extends `BaseModel`)
2. **Tạo Repository** (extends `BaseRepository`) — ⚠️ BẮT BUỘC
3. **Đăng ký Repository** trong ServiceProvider
4. **Tạo Service** (inject Repository)
5. **Tạo Controller** (extends `BaseController`)
6. **Tạo Routes** + **Permissions**
7. **Tạo Views** (dùng Blade)
8. **Chạy migrations**: `composer dump-autoload` → `php artisan migrate`

Chi tiết: `docs/guide.md`, `docs/development.md`

---

## Tính năng chính

### Quản lý người dùng & Vai trò
- CRUD người dùng
- Tạo/chỉnh sửa vai trò với phân quyền linh hoạt
- Lock/unlock tài khoản
- Admin dashboard

### Media Manager
- Upload file (đơn/đa)
- Chunked upload cho file lớn
- Tạo thư mục
- Hỗ trợ Local / Google Drive
- Xóa với tối ưu hóa DB

### RBAC (Role-Based Access Control)
- Super Admin (truy cập toàn bộ)
- Tùy chỉnh vai trò + quyền
- Directive Blade: `@permission`, `@anypermission`, `@role`, `@superuser`
- Middleware `PermissionMiddleware`

### Activity Log
- Log tự động mọi create/update/delete
- Trace admin actions
- Query lịch sử hoạt động

### Table Builder
- Xây dựng bảng động
- Columns: Text, Badge, Boolean, Date, Numeric, Image, Avatar
- Filters: Select, Boolean, Text
- Actions: Edit, Delete, Custom
- Inline + Class-based

---

## Công cụ Artisan

```bash
# Tạo package mới
php artisan make:package PackageName

# Tạo bảng dữ liệu
php artisan make:table ProductTable --model=Product --package=Inventory

# Xóa chunks cũ (chạy cron */5)
php artisan chunks:clear

# Dọn dẹp media không sử dụng
php artisan media:cleanup

# Seed tài khoản admin mặc định
php artisan db:seed --class="Packages\Core\Database\Seeders\AdminSeeder"
```

---

## Kiểm tra & Thử nghiệm

```bash
# Chạy tất cả tests
php artisan test

# Kiểm tra code style
./vendor/bin/pint

# Linting & format
npm run lint
```

---

## Triển khai

### Production Checklist

- [ ] Copy `.env.example` → `.env`, cập nhật biến môi trường
- [ ] `composer install --no-dev`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `npm run build`
- [ ] Đặt `APP_DEBUG=false`
- [ ] Đặt `APP_ENV=production`
- [ ] Backup database

Chi tiết: `docs/deployment-guide.md`

---

## Tài liệu

Tất cả tài liệu được viết bằng **tiếng Việt** trong thư mục `docs/`:

- **project-overview-pdr.md** — Tổng quan, yêu cầu dự án
- **code-standards.md** — Chuẩn mã, quy tắc bắt buộc
- **codebase-summary.md** — Tóm tắt codebase
- **system-architecture.md** — Kiến trúc hệ thống
- **project-roadmap.md** — Lộ trình phát triển
- **deployment-guide.md** — Hướng dẫn triển khai
- **guide.md** — Quy tắc package (ngắn gọn)
- **development.md** — Chi tiết hướng dẫn phát triển

---

## Hỗ trợ & Liên hệ

- **Email**: ngnguyenkhoib4@gmail.com
- **License**: MIT

---

*Cập nhật: 17/05/2026*
