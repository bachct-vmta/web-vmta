# Hướng dẫn phát triển Package

> Tài liệu chi tiết về kiến trúc và cách phát triển package với Core Package.

---

## Mục lục

1. [Khởi tạo dự án](#-khởi-tạo-dự-án)
2. [Quy tắc bắt buộc](#-quy-tắc-bắt-buộc)
3. [Cấu trúc Package](#-cấu-trúc-package)
4. [Repository Pattern](#-repository-pattern)
5. [Controller](#-controller)
6. [Service](#-service)
7. [Enums & Events](#-enums--events)
8. [Activity Log](#-activity-log)
9. [Jobs](#-jobs)
10. [Table Builder](#-table-builder)
11. [Checklist phát triển](#-checklist-phát-triển)

---

## Khởi tạo dự án

### Tạo project mới

```bash
laravel-kit new my-project
laravel-kit new my-project --laravel=11 --db-name=mydb
```

### Thêm Core vào project đang có

```bash
cd existing-project
laravel-kit init                # Full: copy Core + migrate + tạo admin
laravel-kit init --skip-migrate # Bỏ qua migrations
laravel-kit init --force        # Ghi đè packages/Core đã tồn tại
```

### Chạy AdminSeeder (reset admin user)

```bash
php artisan db:seed --class="Packages\\Core\\Database\\Seeders\\AdminSeeder"
```

AdminSeeder tự động detect DB structure (Core native / Spatie-style).

### Tài khoản admin mặc định

| Field | Value |
|-------|-------|
| Email | `admin@nguyenkhoi.dev` |
| Password | `123456789` |
| Role | Super Admin |

### Biến môi trường bắt buộc

```env
APP_ENC_KEY=<32-char-hex-key>   # Bắt buộc — dùng bởi EncryptionService
```

> `laravel-kit init` tự động tạo `APP_ENC_KEY` nếu chưa có.

---

## 🚨 Quy tắc bắt buộc

### 1. Repository Pattern là BẮT BUỘC

```php
// ❌ CẤM - Không query Model trực tiếp
$reports = Report::where('status', 'active')->get();

// ✅ ĐÚNG - Sử dụng Repository
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

---

## 📁 Cấu trúc Package

```
packages/{PackageName}/
├── configs/
│   └── permissions.php       # Quyền RBAC
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Customer routes
│   └── admin.php             # Admin routes
├── src/
│   ├── Enums/                # PHP 8.1+ Enums
│   ├── Events/               # Domain events
│   ├── Http/
│   │   ├── Controllers/      # Extends BaseController
│   │   └── Requests/         # Form validation
│   ├── Jobs/                 # Queue jobs
│   ├── Models/               # Extends BaseModel
│   ├── Providers/
│   │   └── {Package}ServiceProvider.php
│   ├── Repositories/         # ⚠️ BẮT BUỘC
│   │   ├── Eloquent/
│   │   └── Interfaces/
│   ├── Services/             # Business logic
│   └── Traits/               # Package-specific traits
├── composer.json
└── Readme.md
```

---

## 🗄️ Repository Pattern

### Bước 1: Tạo Repository Interface (Recommended)

```php
<?php
// packages/Report/src/Repositories/Interfaces/ReportRepositoryInterface.php

namespace Packages\Report\Src\Repositories\Interfaces;

use Packages\Report\Src\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReportRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Report;
    public function create(array $data): Report;
    public function update(Report $report, array $data): Report;
    public function delete(Report $report): bool;
    public function getByStatus(string $status): Collection;
}
```

### Bước 2: Tạo Repository Implementation (BẮT BUỘC)

```php
<?php
// packages/Report/src/Repositories/Eloquent/ReportRepository.php

namespace Packages\Report\Src\Repositories\Eloquent;

use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Report\Src\Models\Report;
use Packages\Report\Src\Enums\ReportStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportRepository extends BaseRepository
{
    public function model(): string
    {
        return Report::class;
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Report
    {
        return $this->model->with('user')->find($id);
    }

    public function create(array $data): Report
    {
        return $this->model->create($data);
    }

    public function update(Report $report, array $data): Report
    {
        $report->update($data);
        return $report->fresh();
    }

    public function delete(Report $report): bool
    {
        return $report->delete();
    }

    public function getByStatus(ReportStatus $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->with('user')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->getByStatus(ReportStatus::PENDING);
    }

    public function countByUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)->count();
    }

    public function getUserReports(int $userId, int $limit = 10): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
```

### Bước 3: Đăng ký trong ServiceProvider

```php
<?php
// packages/Report/src/Providers/ReportServiceProvider.php

namespace Packages\Report\Src\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Core\Src\Traits\LoadAndPublishDataTrait;
use Packages\Core\Src\Services\PermissionService;
use Packages\Report\Src\Repositories\Eloquent\ReportRepository;

class ReportServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        // ⚠️ BẮT BUỘC: Đăng ký Repository
        $this->app->singleton(ReportRepository::class);
        
        // Đăng ký Service
        $this->app->singleton(\Packages\Report\Src\Services\ReportService::class);
    }

    public function boot(): void
    {
        $this->setNamespace('Report')
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadRoutes(['web', 'admin']);

        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        if (class_exists(PermissionService::class)) {
            $permissionService = $this->app->make(PermissionService::class);
            $permissionsPath = __DIR__ . '/../../configs/permissions.php';
            
            if (file_exists($permissionsPath)) {
                $permissionService->registerPermissions(require $permissionsPath);
            }
        }
    }
}
```

---

## 🎮 Controller

```php
<?php
// packages/Report/src/Http/Controllers/Admin/ReportController.php

namespace Packages\Report\Src\Http\Controllers\Admin;

use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function __construct(
        private ReportRepository $reportRepository,
        private ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $reports = $this->reportRepository->paginate($request->all(), 20);
        return view('report::admin.reports.index', compact('reports'));
    }

    public function show(int $id)
    {
        $report = $this->reportRepository->findById($id);

        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }

        return view('report::admin.reports.show', compact('report'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        $report = $this->reportService->create($request->user(), $validated);

        return $this->redirectWithSuccess(
            route('admin.reports.show', $report),
            'Tạo báo cáo thành công!'
        );
    }

    public function update(Request $request, int $id)
    {
        $report = $this->reportRepository->findById($id);
        
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
        ]);

        $this->reportRepository->update($report, $validated);

        return $this->backWithSuccess('Cập nhật thành công.');
    }

    public function destroy(int $id)
    {
        $report = $this->reportRepository->findById($id);
        
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }

        $this->reportRepository->delete($report);

        return $this->backWithSuccess('Đã xóa báo cáo.');
    }
}
```

---

## ⚙️ Service

```php
<?php
// packages/Report/src/Services/ReportService.php

namespace Packages\Report\Src\Services;

use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Models\Report;
use Packages\Report\Src\Enums\ReportStatus;
use Packages\Core\Src\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        private ReportRepository $reportRepository
    ) {}

    public function create(User $user, array $data): Report
    {
        return DB::transaction(function () use ($user, $data) {
            return $this->reportRepository->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'type' => $data['type'],
                'status' => ReportStatus::PENDING,
            ]);
        });
    }

    public function process(Report $report): Report
    {
        return DB::transaction(function () use ($report) {
            $this->reportRepository->update($report, [
                'status' => ReportStatus::PROCESSING
            ]);

            $result = $this->performProcessing($report);

            return $this->reportRepository->update($report, [
                'status' => ReportStatus::COMPLETED,
                'data' => ['result' => $result],
            ]);
        });
    }

    public function getUserSummary(User $user): array
    {
        return [
            'total' => $this->reportRepository->countByUser($user->id),
            'recent' => $this->reportRepository->getUserReports($user->id, 5),
            'pending' => $this->reportRepository->getByStatus(ReportStatus::PENDING)
                ->where('user_id', $user->id)
                ->count(),
        ];
    }

    private function performProcessing(Report $report): array
    {
        return ['status' => 'success'];
    }
}
```

---

## 📋 Jobs

```php
<?php
// packages/Report/src/Jobs/ProcessReportJob.php

namespace Packages\Report\Src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Services\ReportService;

class ProcessReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $reportId
    ) {}

    public function handle(
        ReportRepository $reportRepository,
        ReportService $reportService
    ): void {
        $report = $reportRepository->findById($this->reportId);

        if (!$report) {
            return;
        }

        $reportService->process($report);
    }
}
```

---

## 📊 Table Builder

### Tạo Table Class

```bash
php artisan make:table ProductTable --model=Product --package=Inventory
```

### Option 1: Inline Builder

```php
use Packages\Core\Src\Tables\Table;
use Packages\Core\Src\Tables\Columns\{TextColumn, BadgeColumn, BooleanColumn, NumericColumn, DateColumn};
use Packages\Core\Src\Tables\Filters\{SelectFilter, BooleanFilter};
use Packages\Core\Src\Tables\Actions\Action;

public function index()
{
    $table = Table::make(Product::with('category'))
        ->heading('Quản lý sản phẩm')
        ->columns([
            TextColumn::make('name')
                ->label('Tên sản phẩm')
                ->searchable()
                ->sortable(),
            BadgeColumn::make('category.name')
                ->label('Danh mục'),
            NumericColumn::make('price')
                ->label('Giá')
                ->money('đ')
                ->alignRight(),
            BooleanColumn::make('is_active')
                ->label('Trạng thái'),
            DateColumn::make('created_at')
                ->label('Ngày tạo')
                ->since(),
        ])
        ->filters([
            SelectFilter::make('category_id')
                ->options(Category::pluck('name', 'id'))
                ->placeholder('Tất cả'),
        ])
        ->actions([
            Action::make('edit')
                ->iconEdit()
                ->route('admin.products.edit')
                ->permission('products.edit'),
            Action::make('delete')
                ->iconDelete()
                ->route('admin.products.destroy')
                ->method('DELETE')
                ->confirm('Bạn có chắc?')
                ->danger()
                ->permission('products.delete'),
        ])
        ->paginate(15);

    return view('inventory::admin.products.index', ['table' => $table]);
}
```

**Trong Blade:**
```blade
{!! $table !!}
```

### Option 2: Class-based Table

```php
<?php
// packages/Inventory/src/Tables/ProductTable.php

namespace Packages\Inventory\Src\Tables;

use Packages\Core\Src\Tables\BaseTable;
use Packages\Inventory\Src\Models\Product;
use Packages\Inventory\Src\Models\Category;
use Packages\Core\Src\Tables\Columns\{TextColumn, BadgeColumn, NumericColumn, BooleanColumn};
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ProductTable extends BaseTable
{
    protected ?string $heading = 'Quản lý sản phẩm';
    protected int $perPage = 20;
    protected ?string $defaultSort = 'created_at';

    protected function model(): string
    {
        return Product::class;
    }

    protected function query(Builder $query): Builder
    {
        return $query->with('category');
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            BadgeColumn::make('category.name')->label('Danh mục'),
            NumericColumn::make('price')->money('đ')->alignRight(),
            BooleanColumn::make('is_active')->labels('Bật', 'Tắt'),
        ];
    }

    protected function filters(): array
    {
        return [
            SelectFilter::make('category_id')
                ->options(Category::pluck('name', 'id')),
        ];
    }

    protected function actions(): array
    {
        return [
            Action::make('edit')->iconEdit()->route('admin.products.edit'),
            Action::make('delete')->iconDelete()->route('admin.products.destroy')
                ->method('DELETE')->confirm('Xóa?')->danger(),
        ];
    }
}
```

**Controller:**
```php
public function index(ProductTable $table)
{
    return view('inventory::admin.products.index', ['table' => $table]);
}
```

### Column Types

| Type | Class | Mô tả |
|------|-------|-------|
| Text | `TextColumn` | Text với limit, prefix, suffix |
| Badge | `BadgeColumn` | Colored tags, auto-detect Enum |
| Boolean | `BooleanColumn` | Check/cross icons |
| Date | `DateColumn` | Format, diffForHumans |
| Numeric | `NumericColumn` | Number format, currency |
| Image | `ImageColumn` | Thumbnails |
| Avatar | `AvatarColumn` | Avatar với initials |

**Column Methods:**
```php
TextColumn::make('name')
    ->label('Label')
    ->searchable()
    ->sortable()
    ->alignRight()
    ->limit(50)
    ->default('N/A')
    ->hidden()
    ->formatStateUsing(fn($v) => strtoupper($v));

BadgeColumn::make('status')
    ->colors([
        'active' => 'green',
        'pending' => 'yellow',
        'inactive' => 'gray',
    ]);

NumericColumn::make('price')
    ->money('đ')
    ->decimals(2);

DateColumn::make('created_at')
    ->since()           // "2 hours ago"
    ->dateTime()        // "01/01/2024 12:00"
    ->format('d/m/Y');  // Custom format
```

### Filter Types

| Type | Class | Mô tả |
|------|-------|-------|
| Select | `SelectFilter` | Dropdown với options |
| Boolean | `BooleanFilter` | Yes/No/All dropdown |
| Text | `TextFilter` | Text search input |

```php
SelectFilter::make('status')
    ->label('Trạng thái')
    ->options(['active' => 'Hoạt động', 'inactive' => 'Tạm khóa'])
    ->placeholder('Tất cả');

BooleanFilter::make('is_active')
    ->labels('Hoạt động', 'Tạm khóa');

TextFilter::make('search')
    ->placeholder('Tìm kiếm...')
    ->searchColumns(['name', 'email', 'phone']);
```

### Action Types

```php
Action::make('edit')
    ->label('Sửa')
    ->iconEdit()
    ->route('admin.products.edit')
    ->permission('products.edit');

Action::make('delete')
    ->iconDelete()
    ->route('admin.products.destroy')
    ->method('DELETE')
    ->confirm('Bạn có chắc muốn xóa?')
    ->danger()
    ->permission('products.delete')
    ->hidden(fn($product) => $product->has_orders);

Action::make('view-invoice')
    ->urlUsing(fn($order) => route('invoices.view', ['id' => $order->invoice_id]));
```

---

## Enums & Events

### Enums

Dùng PHP 8.1+ Enum với `label()` và `color()` methods:

```php
<?php
namespace Packages\Report\Src\Enums;

enum ReportStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ xử lý',
            self::PROCESSING => 'Đang xử lý',
            self::COMPLETED => 'Hoàn thành',
            self::FAILED => 'Thất bại',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::COMPLETED => 'green',
            self::FAILED => 'red',
        };
    }
}
```

### Core Enums sẵn có

| Enum | Values |
|------|--------|
| `UserStatus` | `active`, `locked`, `pending` |
| `StorageDriver` | `local`, `google_drive` |

### Events

Core cung cấp sẵn 7 domain events:

| Event | Dispatch khi |
|-------|-------------|
| `UserCreated` | Tạo user mới |
| `UserUpdated` | Cập nhật user |
| `UserDeleted` | Xóa user |
| `RoleChanged` | Thay đổi role |
| `SettingChanged` | Thay đổi setting |
| `MediaUploaded` | Upload file |
| `MediaDeleted` | Xóa file |

---

## Activity Log

Sử dụng trait `LogsAdminActivity` để tự động log thao tác admin:

```php
use Packages\Core\Src\Traits\LogsAdminActivity;

class ReportController extends BaseController
{
    use LogsAdminActivity;

    // Mọi thao tác store/update/destroy sẽ tự động log
}
```

Xem log tại bảng `activity_logs`.

## ✅ Checklist phát triển

### Phase 1: Cấu trúc
- [ ] Tạo thư mục package
- [ ] Tạo `composer.json` với PSR-4
- [ ] Tạo `Readme.md`

### Phase 2: Core Components
- [ ] Tạo ServiceProvider (use `LoadAndPublishDataTrait`)
- [ ] Tạo Models (extends `BaseModel`)
- [ ] **Tạo Repositories (extends `BaseRepository`)** ⚠️
- [ ] Tạo Enums với `label()` và `color()`
- [ ] Tạo Form Requests

### Phase 3: Business Logic
- [ ] Tạo Services (inject Repositories)
- [ ] Tạo Controllers (extends `BaseController`)
- [ ] Tạo Jobs (inject Repositories)

### Phase 4: Routes & Permissions
- [ ] Tạo `routes/web.php`
- [ ] Tạo `routes/admin.php`
- [ ] Tạo `configs/permissions.php`
- [ ] Đăng ký permissions trong ServiceProvider

### Phase 5: Views
- [ ] Tạo Blade views với package namespace
- [ ] Sử dụng `@permission` directives
- [ ] Tuân thủ UI/UX standards

### Phase 6: Database
- [ ] Tạo migrations
- [ ] Thêm indexes
- [ ] Soft deletes nếu cần

### Phase 7: Registration
- [ ] Thêm package vào root `composer.json` (require)
- [ ] Đảm bảo `composer.json` của package có `extra.laravel.providers`
- [ ] Run `composer dump-autoload` (auto chạy `package:discover`)
- [ ] Run `php artisan migrate`
- [ ] Clear caches

> **Lưu ý:** KHÔNG cần đăng ký ServiceProvider trong `config/app.php`.
> Laravel auto-discover thông qua `extra.laravel.providers` trong `composer.json` của package.

### Phase 8: Testing
- [ ] Test CRUD operations
- [ ] Test permissions
- [ ] Test relationships
- [ ] Test queue jobs

---

## Nhắc nhở

> **REPOSITORY PATTERN KHÔNG ĐƯỢC THƯƠNG LƯỢNG**
>
> Mỗi khi viết code tương tác với Model:
> - ❌ **KHÔNG BAO GIỜ:** `Report::where()->get()`
> - ✅ **LUÔN LUÔN:** `$this->reportRepository->methodName()`

---

*Cập nhật: 14/03/2026*