# Chuẩn mã & Quy tắc phát triển

**Tài liệu bắt buộc** — Mọi code viết cho VMTA_Laravel phải tuân thủ tiêu chuẩn này.

---

## 1. Repository Pattern (BẮT BUỘC)

### Nguyên tắc cốt lõi

**KHÔNG BAO GIỜ** query Model trực tiếp. **LUÔN LUÔN** sử dụng Repository.

```php
// ❌ CẤM — Query Model trực tiếp
$users = User::where('status', 'active')->get();
$report = Report::find($id);
Report::create($data);

// ✅ ĐÚNG — Qua Repository
$users = $this->userRepository->getActive();
$report = $this->reportRepository->findById($id);
$report = $this->reportRepository->create($data);
```

### Cấu trúc Repository

```
src/Repositories/
├── Interfaces/
│   └── UserRepositoryInterface.php      # Contract
└── Eloquent/
    └── UserRepository.php               # Implementation
```

### Interface (Recommended)

```php
<?php
// packages/Core/src/Repositories/Interfaces/UserRepositoryInterface.php

namespace Packages\Core\Src\Repositories\Interfaces;

use Packages\Core\Src\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
    public function paginate(array $filters = [], int $perPage = 20);
}
```

### Implementation

```php
<?php
// packages/Core/src/Repositories/Eloquent/UserRepository.php

namespace Packages\Core\Src\Repositories\Eloquent;

use Packages\Core\Src\Repositories\BaseRepository;
use Packages\Core\Src\Models\User;

class UserRepository extends BaseRepository
{
    // ⚠️ BẮTBUỘC: Implement model() method
    public function model(): string
    {
        return User::class;
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function paginate(array $filters = [], int $perPage = 20)
    {
        return $this->model
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    public function getActive()
    {
        return $this->model
            ->where('status', 'active')
            ->get();
    }
}
```

### Đăng ký trong ServiceProvider

```php
<?php
// packages/MyPackage/src/Providers/MyPackageServiceProvider.php

use Packages\MyPackage\Src\Repositories\Eloquent\UserRepository;
use Packages\MyPackage\Src\Repositories\Interfaces\UserRepositoryInterface;

public function register(): void
{
    // Đăng ký Repository (BẮT BUỘC)
    $this->app->singleton(UserRepository::class);
    // Hoặc với interface:
    $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
}
```

---

## 2. Base Class Inheritance (BẮT BUỘC)

### Kế thừa đúng cấp độ

```php
// Model — phải extend BaseModel
class Report extends BaseModel
{
    protected $table = 'reports';
    // ...
}

// Controller — phải extend BaseController
class ReportController extends BaseController
{
    public function __construct(
        private ReportRepository $reportRepository
    ) {}
}

// Repository — phải extend BaseRepository
class ReportRepository extends BaseRepository
{
    public function model(): string
    {
        return Report::class;
    }
}

// Service — phải extend BaseService
class ReportService extends BaseService
{
    public function __construct(
        private ReportRepository $reportRepository
    ) {}
}
```

### BaseModel traits

```php
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Traits\Filterable;
use Packages\Core\Src\Traits\HasPermission;

class Report extends BaseModel
{
    use Filterable;          // Query filtering
    use HasPermission;       // Permission checks

    protected $fillable = ['title', 'status', 'user_id'];
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
```

### BaseController helpers

```php
class ReportController extends BaseController
{
    // Helper methods từ BaseController:
    
    // Return redirect với success message
    return $this->redirectWithSuccess(
        route('admin.reports.index'),
        'Báo cáo đã được tạo!'
    );

    // Return redirect với error message
    return $this->backWithError('Báo cáo không tồn tại.');

    // Return data JSON
    return $this->jsonResponse($data);

    // Check permission
    if (!$this->hasPermission('reports.view')) {
        abort(403);
    }
}
```

---

## 3. Dependency Injection (BẮT BUỘC)

### Quy tắc

- **Constructor injection** — ALWAYS
- **KHÔNG dùng** `app()` helper
- **KHÔNG dùng** `resolve()` helper
- **KHÔNG dùng** service locator pattern

```php
// ❌ CẤM
class ReportController extends Controller
{
    public function show($id)
    {
        $repo = app(ReportRepository::class);  // ❌ Service locator
        $report = $repo->findById($id);
    }
}

// ✅ ĐÚNG
class ReportController extends BaseController
{
    public function __construct(
        private ReportRepository $reportRepository,
        private ReportService $reportService
    ) {}

    public function show($id)
    {
        $report = $this->reportRepository->findById($id);
    }
}
```

### Job injection

```php
<?php
namespace Packages\Report\Src\Jobs;

use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Services\ReportService;

class ProcessReportJob implements ShouldQueue
{
    // Inject trong handle() method:
    public function handle(
        ReportRepository $reportRepository,
        ReportService $reportService
    ): void {
        $report = $reportRepository->findById($this->reportId);
        $reportService->process($report);
    }
}
```

---

## 4. Namespace & File Organization

### PSR-4 Autoloading

```
packages/MyPackage/
├── composer.json
│   "psr-4": {
│       "Packages\MyPackage\Src\": "src/",
│       "Packages\MyPackage\Database\": "database/"
│   }
└── src/
    ├── Models/
    ├── Repositories/
    │   ├── Eloquent/
    │   └── Interfaces/
    ├── Http/
    │   ├── Controllers/
    │   │   ├── Admin/
    │   │   └── Web/
    │   └── Requests/
    ├── Services/
    ├── Events/
    ├── Enums/
    ├── Jobs/
    ├── Traits/
    ├── Tables/
    └── Providers/
```

### Naming conventions

| Loại | Convention | Ví dụ |
|------|-----------|-------|
| **File** | kebab-case | `user-repository.php` |
| **Class** | PascalCase | `UserRepository` |
| **Property** | camelCase | `$userRepository` |
| **Method** | camelCase | `getActiveUsers()` |
| **Constant** | UPPER_SNAKE_CASE | `REPORT_LIMIT = 100` |
| **Database table** | snake_case plural | `reports` |
| **Column** | snake_case | `created_at` |
| **Route parameter** | kebab-case | `/reports/{report-id}` |

---

## 5. File Size Management

### Limits

- **Model** < 200 LOC (split large models into smaller ones)
- **Controller** < 300 LOC (extract methods to Service)
- **Repository** < 250 LOC (split into multiple repositories)
- **Service** < 300 LOC (extract business logic to smaller services)
- **Migration** < 150 LOC (split into multiple migrations)

### Splitting Strategy

**Model too large?** → Extract concerns into Services/Traits
```php
// ❌ Large model
class Report extends BaseModel
{
    public function calculateMetrics() { /* 100 LOC */ }
    public function generatePDF() { /* 80 LOC */ }
    public function sendNotification() { /* 50 LOC */ }
}

// ✅ Split into services
class Report extends BaseModel { }
class ReportMetricsService { }
class ReportPDFService { }
class ReportNotificationService { }
```

**Controller too large?** → Move logic to Service
```php
// ❌ Large controller
class ReportController extends BaseController
{
    public function store(Request $request)
    {
        // Validation
        // Create report
        // Calculate metrics
        // Send notifications
        // Generate PDF
        // Return response
    }
}

// ✅ Use service
class ReportController extends BaseController
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function store(Request $request)
    {
        $report = $this->reportService->create($request->validated());
        return redirect()->route('admin.reports.show', $report);
    }
}
```

---

## 6. Naming Conventions

### Controllers
```php
// ✅ ĐÚNG
class UserController extends BaseController { }
class AdminReportController extends BaseController { }
class ApiMediaFileController extends BaseController { }

// ❌ SAI
class UsersController { }
class ReportAdminController { }
class MediaController { }  // Quá generic
```

### Models
```php
// ✅ ĐÚNG
class User extends BaseModel { }
class ActivityLog extends BaseModel { }
class MediaFolder extends BaseModel { }

// ❌ SAI
class Users { }
class Logs { }
class Folders { }
```

### Repositories
```php
// ✅ ĐÚNG
class UserRepository extends BaseRepository { }
class ActivityLogRepository extends BaseRepository { }

// ❌ SAI
class UserRepo { }
class LogRepository { }
```

### Services
```php
// ✅ ĐÚNG
class ReportService extends BaseService { }
class GoogleDriveService extends BaseService { }
class PermissionService extends BaseService { }

// ❌ SAI
class ReportBusiness { }
class Drive { }
```

### Form Requests
```php
// ✅ ĐÚNG
class StoreReportRequest extends FormRequest { }
class UpdateReportRequest extends FormRequest { }

// ❌ SAI
class ReportRequest { }
class CreateReportRequest { }
```

### Enums
```php
// ✅ ĐÚNG
enum ReportStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
}

// ❌ SAI
enum Status: string { }  // Quá generic
```

### Routes
```php
// ✅ ĐÚNG
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');

// ❌ SAI
Route::get('report/list', ...);
Route::post('createReport', ...);
```

---

## 7. Forbidden Code Patterns

### ❌ Không bao giờ

```php
// 1. Direct Model query
Report::all();
Report::where('status', 'active')->get();
User::find($id);

// 2. Service locator
app(ReportRepository::class);
resolve(ReportService::class);

// 3. Model không Repository
// Nếu tồn tại Report model → PHẢI có ReportRepository

// 4. Không kế thừa base class
class Report extends Model { }      // ❌ Phải extend BaseModel
class Controller extends Controller { }  // ❌ Phải extend BaseController

// 5. Property inject (không support)
class ReportController
{
    public ReportRepository $reportRepository;  // ❌ SAI
}

// 6. Magic string (should use const/enum)
if ($user->role === 'admin') { }        // ❌ Sử dụng enum/const
$status = 'active';                     // ❌ Sử dụng UserStatus enum

// 7. Business logic trong Controller
class ReportController extends BaseController
{
    public function store(Request $request)
    {
        // ❌ Logic ở đây
        $report = Report::create($request->all());
        // ... 50 more lines ...
        return response($report);
    }
}

// 8. No pagination validation
$perPage = $request->query('per_page');     // ❌ Có thể 1000 hoặc -1
$results = Model::paginate($perPage);

// 9. No transaction for multi-step operations
$report = Report::create($data);
$this->sendNotification($report);           // ❌ Nếu fail, rollback?

// 10. Hardcoded values
'localhost:5432'  // ❌ Sử dụng config/env
'admin@example.com'  // ❌ Sử dụng config
```

---

## 8. Code Structure & Best Practices

### Model structure

```php
<?php
namespace Packages\Report\Src\Models;

use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Traits\Filterable;
use Packages\Report\Src\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends BaseModel
{
    use Filterable;

    // 1. Constants
    public const REPORT_LIMIT = 100;

    // 2. Properties
    protected $table = 'reports';
    protected $fillable = ['title', 'status', 'user_id', 'data'];

    // 3. Casts
    protected $casts = [
        'status' => ReportStatus::class,
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    // 4. Accessors & Mutators (if needed)
    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
        ];
    }

    // 5. Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 6. Scopes
    public function scopeActive($query)
    {
        return $query->where('status', ReportStatus::ACTIVE);
    }

    // 7. Methods
    public function isPending(): bool
    {
        return $this->status === ReportStatus::PENDING;
    }
}
```

### Service structure

```php
<?php
namespace Packages\Report\Src\Services;

use Packages\Core\Src\Services\BaseService;
use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Models\Report;
use Illuminate\Support\Facades\DB;

class ReportService extends BaseService
{
    public function __construct(
        private ReportRepository $reportRepository
    ) {}

    // Business logic methods
    public function create(array $data): Report
    {
        return DB::transaction(function () use ($data) {
            return $this->reportRepository->create($data);
        });
    }

    public function process(Report $report): Report
    {
        return DB::transaction(function () use ($report) {
            // Do something
            return $this->reportRepository->update($report, [
                'status' => 'completed'
            ]);
        });
    }

    // Helper methods
    private function validate(array $data): bool
    {
        return true;
    }
}
```

### Controller structure

```php
<?php
namespace Packages\Report\Src\Http\Controllers\Admin;

use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Report\Src\Repositories\Eloquent\ReportRepository;
use Packages\Report\Src\Services\ReportService;
use Packages\Report\Src\Http\Requests\StoreReportRequest;
use Packages\Report\Src\Http\Requests\UpdateReportRequest;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function __construct(
        private ReportRepository $reportRepository,
        private ReportService $reportService
    ) {}

    // List with table builder
    public function index()
    {
        $reports = $this->reportRepository->paginate(request()->all());
        return view('report::admin.reports.index', compact('reports'));
    }

    // Show single resource
    public function show(int $id)
    {
        $report = $this->reportRepository->findById($id);
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }
        return view('report::admin.reports.show', compact('report'));
    }

    // Create form
    public function create()
    {
        return view('report::admin.reports.create');
    }

    // Store
    public function store(StoreReportRequest $request)
    {
        $report = $this->reportService->create($request->validated());
        return $this->redirectWithSuccess(
            route('admin.reports.show', $report),
            'Báo cáo đã được tạo!'
        );
    }

    // Edit form
    public function edit(int $id)
    {
        $report = $this->reportRepository->findById($id);
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }
        return view('report::admin.reports.edit', compact('report'));
    }

    // Update
    public function update(UpdateReportRequest $request, int $id)
    {
        $report = $this->reportRepository->findById($id);
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }

        $this->reportRepository->update($report, $request->validated());
        return $this->redirectWithSuccess(
            route('admin.reports.show', $report),
            'Báo cáo đã được cập nhật!'
        );
    }

    // Delete
    public function destroy(int $id)
    {
        $report = $this->reportRepository->findById($id);
        if (!$report) {
            return $this->backWithError('Không tìm thấy báo cáo.');
        }

        $this->reportRepository->delete($report);
        return $this->redirectWithSuccess(
            route('admin.reports.index'),
            'Báo cáo đã được xóa!'
        );
    }
}
```

---

## 9. Code Comments & Documentation

### Comment Style

```php
// Use // for single-line comments
// Use /** */ for PHPDoc (function signatures)

/**
 * Get active reports with optional filtering.
 *
 * @param array $filters Filter criteria
 * @param int $perPage Pagination limit
 * @return \Illuminate\Pagination\LengthAwarePaginator
 */
public function paginate(array $filters = [], int $perPage = 20)
{
    return $this->model
        ->filter($filters)
        ->latest()
        ->paginate($perPage);
}
```

### What to comment

- ✅ Complex business logic
- ✅ Non-obvious algorithm decisions
- ✅ PHPDoc for public methods
- ❌ Obvious code (`$count = 0; // Initialize count`)
- ❌ Variable names that explain themselves

### Example

```php
// ✅ GOOD
// Orphaned reports (never viewed, older than 30 days)
// are pruned nightly to save storage
public function pruneOrphanedReports(): int
{
    return $this->reportRepository->deleteOlderThan(30);
}

// ❌ BAD
public function test()
{
    $x = 5; // Set x to 5
    $y = $x * 2; // Multiply x by 2
}
```

---

## 10. Error Handling

### Validation

```php
// Form Request (recommended)
class StoreReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,completed',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề báo cáo không được bỏ trống.',
        ];
    }
}

// In controller
public function store(StoreReportRequest $request)
{
    // Validated data already checked
    $report = $this->reportService->create($request->validated());
}
```

### Exception handling

```php
try {
    $report = $this->reportRepository->findById($id);
    if (!$report) {
        throw new \Exception('Report not found');
    }
    return $this->redirectWithSuccess(route('admin.reports.show', $report));
} catch (\Exception $e) {
    return $this->backWithError('Error: ' . $e->getMessage());
}
```

---

## 11. Testing

### File organization

```
tests/
├── Feature/
│   └── ReportControllerTest.php
└── Unit/
    ├── ReportRepositoryTest.php
    └── ReportServiceTest.php
```

### Testing rules

- ✅ Test happy path + error cases
- ✅ Use meaningful test names
- ✅ One assertion per test (generally)
- ✅ Use database transactions for isolation
- ❌ Don't test framework (Laravel internals)
- ❌ Don't use real external services

---

## 12. Migration & Database

### Migration naming

```bash
# ✅ GOOD
create_reports_table
add_status_to_reports_table
add_indexes_to_reports_table

# ❌ BAD
migration1
fix_reports
update
```

### Migration structure

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
```

---

## 13. Security Standards

### Input validation
- Always validate user input (FormRequest)
- Use sanitization (Blade auto-escaping)
- Type-hint parameters

### SQL Injection prevention
- Use Eloquent only (no raw SQL)
- Use parameterized queries

### XSS prevention
- Blade auto-escapes by default
- Use `{!! !!}` carefully (only for HTML content)

### CSRF protection
- `@csrf` in forms (automatic)
- `VerifyCsrfToken` middleware (automatic)

### Sensitive data
- Encrypt with `APP_ENC_KEY`
- Don't log passwords/tokens
- Use `hidden` on forms

---

## 14. Checklist trước khi commit

- [ ] Code tuân thủ Repository Pattern (no direct Model queries)
- [ ] All classes extend correct Base class
- [ ] Dependencies injected via constructor
- [ ] No `app()` or `resolve()` helpers
- [ ] File size < limits (Model 200, Controller 300, etc.)
- [ ] Naming conventions followed
- [ ] No forbidden patterns (magic strings, hardcoded values)
- [ ] PHPDoc for public methods
- [ ] Error handling in place
- [ ] Input validation (FormRequest)
- [ ] Tests written (unit + feature)
- [ ] Test coverage ≥ 70%
- [ ] No security issues (OWASP top 10)
- [ ] Migrations created/updated
- [ ] Seeders updated
- [ ] Documentation updated
- [ ] Changelog entry added

---

*Cập nhật: 17/05/2026*
