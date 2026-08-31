# Quy tắc phát triển Package

> Tài liệu bắt buộc tuân thủ khi phát triển với Core Package.

---

## QUY TẮC BẮT BUỘC

### 1. Repository Pattern — KHÔNG THƯƠNG LƯỢNG

```php
// ❌ CẤM - Query Model trực tiếp
$reports = Report::where('status', 'active')->get();

// ✅ ĐÚNG - Qua Repository
$reports = $this->reportRepository->getActive();
```

### 2. Kế thừa đúng Base Class

| Loại | Kế thừa |
|------|---------|
| Model | `BaseModel` |
| Controller | `BaseController` |
| Repository | `BaseRepository` |
| Service | `BaseService` |

### 3. Dependency Injection qua Constructor

```php
// ❌ CẤM
$repo = app(ReportRepository::class);

// ✅ ĐÚNG
public function __construct(
    private ReportRepository $reportRepository
) {}
```

### 4. Mỗi Model PHẢI có Repository

Nếu có `Report` Model → PHẢI có `ReportRepository` + `ReportRepositoryInterface`.

### 5. Đăng ký Repository trong ServiceProvider

```php
public function register(): void
{
    $this->app->singleton(ReportRepositoryInterface::class, ReportRepository::class);
}
```

---

## CẤU TRÚC THƯ MỤC BẮT BUỘC

```
packages/{PackageName}/
├── configs/permissions.php       # Quyền RBAC
├── database/migrations/          # Migrations
├── resources/views/              # Blade views
├── routes/
│   ├── web.php                   # Customer routes
│   └── admin.php                 # Admin routes
├── src/
│   ├── Enums/                    # PHP 8.1+ Enums
│   ├── Http/Controllers/         # Extends BaseController
│   ├── Http/Requests/            # Form validation
│   ├── Jobs/                     # Queue jobs
│   ├── Models/                   # Extends BaseModel
│   ├── Providers/{Package}ServiceProvider.php
│   ├── Repositories/             # ⚠️ BẮT BUỘC
│   │   ├── Eloquent/
│   │   └── Interfaces/
│   └── Services/                 # Business logic
├── composer.json
└── Readme.md
```

---

## KHỞI TẠO DỰ ÁN

### Tạo project mới

```bash
laravel-kit new my-project
laravel-kit new my-project --laravel=11 --db-name=mydb
```

### Thêm Core vào project đang có

```bash
cd existing-project
laravel-kit init                # Full setup + migrate + tạo admin
laravel-kit init --skip-migrate # Bỏ qua migrations
laravel-kit init --force        # Ghi đè packages/Core đã tồn tại
```

### Tài khoản admin mặc định

| | |
|---|---|
| Email | `admin@nguyenkhoi.dev` |
| Password | `123456789` |
| Role | Super Admin |

### Chạy AdminSeeder (cho project đã có DB)

```bash
php artisan db:seed --class="Packages\\Core\\Database\\Seeders\\AdminSeeder"
```

### Biến môi trường bắt buộc

```env
APP_ENC_KEY=<32-char-hex-key>   # Dùng bởi EncryptionService
```

---

## CÁC MẪU CODE BỊ CẤM

```php
// ❌ Query trực tiếp Model
Report::all();
Report::where('status', 'active')->get();

// ❌ Dùng app() helper
$repo = app(ReportRepository::class);
$service = resolve(ReportService::class);

// ❌ Không kế thừa base class
class Report extends Model { }  // PHẢI dùng BaseModel

// ❌ Model không có Repository
// Tồn tại Report model nhưng thiếu ReportRepository
```

---

## THỨ TỰ PHÁT TRIỂN PACKAGE

1. Tạo cấu trúc: `php artisan make:package Report --all`
2. **Tạo Repository TRƯỚC** ⚠️
3. Đăng ký Repository trong ServiceProvider
4. Tạo Model (extends BaseModel)
5. Tạo Service (inject Repository)
6. Tạo Controller (inject Repository + Service)
7. Tạo Routes, Views, Permissions

---

## CHECKLIST

### Trước khi code

- [ ] Model có Repository chưa? → Tạo Repository TRƯỚC
- [ ] Repository đăng ký trong ServiceProvider?
- [ ] Dependencies inject qua constructor?
- [ ] Sử dụng Repository thay vì query Model trực tiếp?

### Kế thừa đúng

- [ ] Model extends `BaseModel`?
- [ ] Controller extends `BaseController`?
- [ ] Repository extends `BaseRepository`?

### Namespace đúng

- [ ] `Packages\{PackageName}\Src\{Folder}`?
- [ ] Use statements đầy đủ?

### Sau khi hoàn thành

- [ ] `composer.json` có `extra.laravel.providers`?
- [ ] `composer dump-autoload` thành công?
- [ ] `php artisan migrate` chạy OK?
- [ ] Permissions cấu hình trong `configs/permissions.php`?

---

## TABLE BUILDER

```bash
php artisan make:table ProductTable --model=Product --package=Inventory
```

```php
// Inline Builder
$table = Table::make(Product::query())
    ->heading('Sản phẩm')
    ->columns([...])
    ->filters([...])
    ->actions([...])
    ->paginate(15);

// Trong Blade
{!! $table !!}
```

---

## ACTIVITY LOG

Sử dụng `LogsAdminActivity` trait để tự động log thao tác admin:

```php
use Packages\Core\Src\Traits\LogsAdminActivity;

class ReportController extends BaseController
{
    use LogsAdminActivity;
    // create, update, delete sẽ tự động được log
}
```

---

## TÀI LIỆU LIÊN QUAN

- [Cấu trúc thư mục](structe.md)
- [Hướng dẫn phát triển chi tiết](development.md)

---

*Cập nhật: 14/03/2026*
