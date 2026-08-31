# Tóm tắt Codebase

Tài liệu tóm tắt cấu trúc, thành phần chính, và cách các phần tương tác trong VMTA_Laravel.

---

## 1. Cấu trúc tổng thể

```
VMTA_Laravel/
├── app/                           # Laravel main app (minimal, most logic in Core)
│   ├── Http/Kernel.php            # Middleware stack
│   ├── Providers/AppServiceProvider.php
│   └── Models/ (empty - use Core)
│
├── config/                        # Laravel config (app, database, mail, etc.)
│   ├── app.php                    # App name, timezone, locale
│   ├── database.php               # DB connections
│   └── ... (standard Laravel)
│
├── database/
│   ├── migrations/                # App-level migrations (if any)
│   └── seeders/
│
├── packages/Core/                 # ⭐ Core Package — Nền tảng
│   ├── configs/                   # RBAC, Media, Core settings
│   ├── database/
│   │   ├── migrations/            # 8 migrations (users, roles, media, settings, logs, etc.)
│   │   └── seeders/AdminSeeder.php
│   ├── resources/
│   │   ├── lang/en|vi/            # Translations
│   │   └── views/                 # Admin layout, auth, media, dashboard
│   ├── routes/
│   │   ├── web.php                # Public routes
│   │   ├── admin.php              # /admin/* routes
│   │   ├── auth.php               # Login, register, logout
│   │   └── media.php              # Media Manager API routes
│   ├── src/
│   │   ├── Chunks/                # Chunked upload system (5 files)
│   │   ├── Console/Commands/      # 4 Artisan commands
│   │   ├── Enums/                 # UserStatus, StorageDriver
│   │   ├── Events/                # 7 domain events
│   │   ├── Helpers/               # 3 helper functions
│   │   ├── Http/
│   │   │   ├── Controllers/       # BaseController + 9 Admin + 2 Auth controllers
│   │   │   ├── Middleware/        # PermissionMiddleware
│   │   │   ├── Requests/          # 13 form validation classes
│   │   │   └── Resources/         # 2 API resources
│   │   ├── Models/                # 9 models (User, Role, Media*, Activity*, etc.)
│   │   ├── Repositories/          # 9 repositories + interfaces
│   │   ├── Services/              # 13 services
│   │   ├── Tables/                # Table Builder system (BaseTable, Table, Columns, Filters, Actions)
│   │   ├── Traits/                # 5 reusable traits
│   │   ├── View/Components/       # 4 Blade components
│   │   └── Providers/CoreServiceProvider.php
│   ├── stubs/package/             # Templates for make:package command
│   ├── Readme.md                  # Package documentation
│   └── composer.json
│
├── public/
│   ├── index.php                  # Entry point
│   ├── uploads/                   # Media storage (local)
│   └── css, js (compiled)
│
├── resources/
│   ├── css/                       # Tailwind CSS
│   ├── js/                        # JavaScript (Vite)
│   └── views/                     # App-level views (if any)
│
├── routes/
│   ├── web.php                    # App routes (most in Core)
│   └── api.php                    # API routes (if needed)
│
├── storage/
│   ├── app/                       # File storage
│   ├── logs/                      # Application logs
│   └── framework/
│
├── tests/
│   ├── Feature/                   # Integration tests
│   └── Unit/                      # Unit tests
│
├── docs/                          # Documentation (Vietnamese)
│   ├── project-overview-pdr.md
│   ├── code-standards.md
│   ├── codebase-summary.md        # This file
│   ├── system-architecture.md
│   ├── project-roadmap.md
│   ├── deployment-guide.md
│   ├── guide.md
│   └── development.md
│
├── .env.example                   # Environment template
├── artisan                        # Artisan CLI
├── composer.json                  # PHP dependencies (includes packages/core)
├── composer.lock
├── package.json                   # Node dependencies
├── vite.config.js                 # Vite bundler config
├── phpunit.xml                    # Test configuration
└── README.md                      # Project overview

```

---

## 2. Package Overview

### 2.0a Main Packages

| Package | Purpose | Status |
|---|---|---|
| `Core` | Base infrastructure (RBAC, Media, ActivityLog, TableBuilder, Settings) | ✅ v1.0 |
| `Localization` | i18n middleware, language switcher | ✅ Phase 1 |
| `Site` | Public layout, Tailwind/Alpine assets | ✅ Phase 1 |
| `Content` | Pages, Posts, Menus, Banners (translatable) | ✅ Phase 2 |
| `Catalog` | Specialties, Hospitals, Services, Doctors (searchable) | ✅ Phase 3 |
| `Inquiry` | Inquiry forms, lead pipeline, coordinator email | ✅ Phase 4 |
| `Chatbot` | Tourism API proxy, SSE relay, floating widget | ✅ Phase 5 |
| `Newsletter` | Double opt-in subscription | 🟡 Phase 6 |
| `Report` | Metrics dashboard (inquiries, pageviews, chatbot) | 🟡 Phase 7 |

---

## 3. Core Package — Chi tiết cấu trúc

### 3.1 Models (9)

| Model | Mô tả | Traits |
|-------|--------|--------|
| `BaseModel` | Base with timestamps, soft delete | Filterable, LogsAdminActivity |
| `User` | User auth + profile | HasApiTokens, Authenticatable |
| `Role` | RBAC roles | — |
| `Setting` | Key-value settings | — |
| `ActivityLog` | Admin action logs | — |
| `GoogleDriveCredential` | OAuth tokens | — |
| `MediaFile` | File records | — |
| `MediaFolder` | Folder records | — |
| `MediaSetting` | Media config | — |

**Key relationships:**
- User → Roles (many-to-many)
- User → ActivityLogs (one-to-many)
- MediaFile → MediaFolder (belongs-to)
- MediaFile → User (belongs-to)

### 3.2 Repositories (9)

Each model has corresponding Repository:

```php
UserRepository → User model
RoleRepository → Role model
SettingRepository → Setting model
ActivityLogRepository → ActivityLog model
GoogleDriveCredentialRepository → GoogleDriveCredential model
MediaFileRepository → MediaFile model
MediaFolderRepository → MediaFolder model
MediaSettingRepository → MediaSetting model
```

**All extend `BaseRepository`**, which provides:
- `model()` — return model class
- `find()`, `findById()`, `all()`, `get()`
- `create()`, `update()`, `delete()`
- `paginate()`, `get()`

### 3.3 Services (13)

| Service | Mô tả |
|---------|--------|
| `BaseService` | Base service class |
| `PermissionService` | RBAC permission management |
| `EncryptionService` | AES encryption (APP_ENC_KEY) |
| `StorageDriverService` | Local / Google Drive switching |
| `GoogleDriveService` | Google Drive API operations |
| `SidebarService` | Admin sidebar navigation |
| `WidgetService` | Dashboard widgets |
| `MediaFileService` | File upload, delete, move, resize |
| `MediaFolderService` | Folder CRUD |
| `MediaResizeService` | Image resizing (thumbnail, medium, large) |
| `DocumentPreviewService` | Preview documents |
| `EncryptionService` | Encrypt/decrypt sensitive data |

### 3.4 Controllers (11)

**Admin Controllers:**
1. `DashboardController` — Dashboard overview
2. `UserController` — User CRUD
3. `RoleController` — Role CRUD
4. `MediaController` — Media manager
5. `MediaFileController` — File operations
6. `MediaFolderController` — Folder operations
7. `MediaSettingsController` — Media config
8. `GoogleDriveController` — Google Drive auth
9. `CKEditorController` — CKEditor file picker

**Auth Controllers:**
1. `LoginController` — Login form + process
2. `RegisterController` — Register form + process

### 3.5 Routes (4 files)

```
routes/
├── web.php          # "/" (public home, if any)
├── admin.php        # /admin/* (guarded, RBAC)
├── auth.php         # /login, /register, /logout
└── media.php        # /admin/media/* (API endpoints)
```

**Key admin routes:**
- `GET /admin` — Dashboard
- `GET|POST /admin/users` — User list/create
- `GET|POST|PUT|DELETE /admin/users/{id}` — User CRUD
- `GET|POST /admin/roles` — Role list/create
- `GET|POST|PUT|DELETE /admin/roles/{id}` — Role CRUD
- `GET /admin/media` — Media manager
- `POST /admin/media/upload` — File upload
- `DELETE /admin/media/files/{id}` — File delete
- `GET /admin/activity-logs` — Activity logs

### 3.6 Events (7)

All in `src/Events/`:

```php
UserCreated — When user created
UserUpdated — When user updated
UserDeleted — When user deleted
RoleChanged — When role changed
SettingChanged — When setting changed
MediaUploaded — When file uploaded
MediaDeleted — When file deleted
```

**Usage:**
```php
use Packages\Core\Src\Events\UserCreated;

// Dispatch
event(new UserCreated($user));

// Listen
public function __construct()
{
    Event::listen(UserCreated::class, [UserCreatedListener::class, 'handle']);
}
```

### 3.7 Traits (5)

| Trait | Mô tả | Used in |
|-------|--------|---------|
| `Filterable` | Query filtering (where, like) | Models |
| `HasPermission` | Permission checks | Controllers, Models |
| `HasTable` | Table Builder integration | Controllers |
| `LogsAdminActivity` | Auto-log create/update/delete | Controllers |
| `LoadAndPublishDataTrait` | Publish views, configs, migrations | ServiceProviders |

### 3.8 Enums (2)

```php
enum UserStatus: string {
    case ACTIVE = 'active';
    case LOCKED = 'locked';
    case PENDING = 'pending';

    public function label(): string { }
    public function color(): string { }
}

enum StorageDriver: string {
    case LOCAL = 'local';
    case GOOGLE_DRIVE = 'google_drive';
}
```

### 3.9 Artisan Commands (4)

```bash
php artisan make:package Report              # Create new package
php artisan make:table ProductTable --model=Product --package=Inventory
php artisan chunks:clear                     # Delete old chunks
php artisan media:cleanup                    # Remove orphaned media
php artisan db:seed --class="Packages\Core\Database\Seeders\AdminSeeder"
```

---

## 4. Data Flow & Request Lifecycle

### HTTP Request → Response

```
1. HTTP Request
   ↓
2. routes/admin.php (or auth.php, media.php)
   ↓
3. Middleware (auth, permission, etc.)
   ↓
4. Controller (extends BaseController)
   │   ├── Constructor DI (receive Repository, Service)
   │   ├── Validation (FormRequest)
   │   └── Business logic (delegate to Service)
   ↓
5. Service (extends BaseService)
   │   ├── Complex logic
   │   ├── Call Repository methods
   │   └── Dispatch events
   ↓
6. Repository (extends BaseRepository)
   │   ├── Query Model
   │   └── Return data
   ↓
7. Model (extends BaseModel)
   │   ├── Database query
   │   ├── Cast, transform
   │   └── Apply scopes/relationships
   ↓
8. Database (SQLite/MySQL/PostgreSQL)
   ↓
9. Controller returns:
   - Blade view
   - JSON response
   - Redirect
   ↓
10. HTTP Response
```

### Example: Create User

```php
// 1. Route
Route::post('/admin/users', [UserController::class, 'store'])->middleware('permission:users.create');

// 2. Form Request validation
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => 'required', 'email' => 'required|email|unique:users'];
    }
}

// 3. Controller
class UserController extends BaseController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService
    ) {}

    public function store(StoreUserRequest $request)
    {
        // $request->validated() already checked
        $user = $this->userService->create($request->validated());
        return $this->redirectWithSuccess(route('admin.users.show', $user));
    }
}

// 4. Service
class UserService extends BaseService
{
    public function __construct(private UserRepository $userRepository) {}

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create($data);
            event(new UserCreated($user));
            return $user;
        });
    }
}

// 5. Repository
class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }
}

// 6. Model
class User extends BaseModel
{
    protected $fillable = ['name', 'email', 'password'];
}

// 7. Database
// users table created via migration
// Row inserted
```

---

## 5. Package Integration

### Local Composer Packages

In root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/*",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "packages/core": "@dev"
    }
}
```

### Package Discovery

Core ServiceProvider auto-discovered via:

```json
// packages/Core/composer.json
{
    "extra": {
        "laravel": {
            "providers": [
                "Packages\\Core\\Src\\Providers\\CoreServiceProvider"
            ]
        }
    }
}
```

Laravel's `package:discover` command runs after `composer install` and registers all providers.

### Create New Package

```bash
php artisan make:package Report
```

Creates `packages/Report/` with:
- `composer.json` (PSR-4 autoload)
- `src/` (Models, Controllers, Services, Repositories)
- `database/` (migrations, seeders)
- `routes/` (web.php, admin.php)
- `resources/views/` (Blade templates)
- `configs/permissions.php` (RBAC)
- `Readme.md`

Then register in root `composer.json`:
```json
{
    "require": {
        "packages/core": "@dev",
        "packages/report": "@dev"    // Add this
    }
}
```

Run:
```bash
composer dump-autoload
php artisan package:discover
php artisan migrate
```

---

## 6. RBAC (Role-Based Access Control) Flow

### Permission Definition

```php
// packages/Core/configs/permissions.php
return [
    'users' => [
        'view' => 'Xem người dùng',
        'create' => 'Tạo người dùng',
        'edit' => 'Chỉnh sửa người dùng',
        'delete' => 'Xóa người dùng',
    ],
    'reports' => [
        'view', 'create', 'edit', 'delete'
    ],
];
```

### Permission Check

```php
// In controller (use middleware)
Route::post('/reports', [ReportController::class, 'store'])
    ->middleware('permission:reports.create');

// In blade
@permission('reports.view')
    <!-- Show report -->
@endpermission

// In code
if ($this->hasPermission('reports.edit')) {
    // Edit allowed
}
```

### Permission Middleware

```php
// Packages\Core\Src\Http\Middleware\PermissionMiddleware
public function handle(Request $request, Closure $next, string $permission)
{
    if (!auth()->check() || !auth()->user()->hasPermission($permission)) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}
```

### User-Role-Permission hierarchy

```
User
├── Roles (many-to-many)
│   └── Permissions (JSON)
└── Permissions (JSON overrides)

HasPermission trait checks:
1. If Super Admin → return true
2. Check user-level permission overrides
3. Check role permissions
```

---

## 7. Media Manager Flow

### File Upload (Chunked)

```
1. Frontend JS sends chunks
   POST /admin/media/upload { chunk, chunkIndex, totalChunks, fileName }
   ↓
2. MediaFileController::uploadChunk()
   ├── ChunkStorage::save() — store chunk file
   ├── ChunkFile — track chunk metadata
   ↓
3. When all chunks received:
   ├── FileMerger::merge() — combine chunks
   ├── Move to final storage (local or Google Drive)
   ├── MediaFile model saved
   ├── Event MediaUploaded dispatched
   └── Return success response

4. Periodic cleanup: php artisan chunks:clear (removes old chunks)
```

### Storage Drivers

```php
// config (env var)
MEDIA_STORAGE_DRIVER=local      // or google_drive

// StorageDriverService routes to correct service
class StorageDriverService
{
    public function upload(File $file, string $path): string
    {
        $driver = config('file-manager.storage.driver');
        if ($driver === 'google_drive') {
            return $this->googleDriveService->upload($file, $path);
        }
        return $this->localStorage->upload($file, $path);
    }
}
```

### CKEditor Integration

```javascript
// Editor config
{
    ckfinder: {
        uploadUrl: '/admin/media/ckeditor/upload'
    }
}

// Controller
CKEditorController::upload()
├── Validate file
├── Save to storage
├── Return JSON { uploaded: true, url: '...' }
```

---

## 8. Activity Logging

### Auto-logging

```php
// In Controller
class UserController extends BaseController
{
    use LogsAdminActivity;

    public function store(StoreUserRequest $request)
    {
        // Automatically logs: action=create, resource=User, admin_id, data
        $user = $this->userService->create($request->validated());
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Logs: action=update, resource=User, admin_id, changes
        $this->userService->update($user, $request->validated());
    }

    public function destroy(User $user)
    {
        // Logs: action=delete, resource=User, admin_id
        $this->userService->delete($user);
    }
}
```

### Query Activity Logs

```php
// In Admin\ActivityLogController (if exists)
$logs = $this->activityLogRepository->paginate(request()->all());

// Filter by actor, action, resource, date range
$logs->filter(['actor_id' => 1, 'action' => 'create', 'resource' => 'User'])
```

---

## 9. Table Builder System

### Inline Builder (Simple)

```php
use Packages\Core\Src\Tables\Table;
use Packages\Core\Src\Tables\Columns\TextColumn;

$table = Table::make(User::query())
    ->heading('Users')
    ->columns([
        TextColumn::make('name')->searchable()->sortable(),
        TextColumn::make('email'),
    ])
    ->paginate(15);

return view('users.index', ['table' => $table]);
```

**In Blade:**
```blade
{!! $table !!}
```

### Class-based (Reusable)

```php
// UserTable.php
class UserTable extends BaseTable
{
    protected ?string $heading = 'Users';
    protected int $perPage = 20;

    protected function model(): string { return User::class; }

    protected function columns(): array
    {
        return [
            TextColumn::make('name')->searchable(),
            BadgeColumn::make('status')->colors(['active' => 'green']),
        ];
    }

    protected function filters(): array
    {
        return [
            SelectFilter::make('status')->options(['active' => 'Active']),
        ];
    }

    protected function actions(): array
    {
        return [
            Action::make('edit')->route('admin.users.edit'),
            Action::make('delete')->route('admin.users.destroy')->method('DELETE'),
        ];
    }
}

// In Controller
public function index(UserTable $table)
{
    return view('users.index', ['table' => $table]);
}
```

### Columns

8 types: TextColumn, BadgeColumn, BooleanColumn, DateColumn, NumericColumn, ImageColumn, AvatarColumn, HTMLColumn

### Filters

SelectFilter, BooleanFilter, TextFilter

### Actions

Action (single row), BulkAction (multiple rows)

---

## 10. Key Files Reference

### Bootstrap & Config

- `app/Providers/AppServiceProvider.php` — App-level service provider
- `config/app.php` — App name, timezone, providers
- `config/database.php` — Database connections
- `bootstrap/app.php` — Framework bootstrap
- `.env` — Environment variables (git-ignored)
- `.env.example` — Template (git-tracked)

### Routes

- `routes/web.php` — Public routes
- `packages/Core/routes/admin.php` — Admin routes
- `packages/Core/routes/auth.php` — Auth routes
- `packages/Core/routes/media.php` — Media API

### Migrations

All in `packages/Core/database/migrations/`:
1. Create users (from Laravel auth)
2. Add fields to users (role_id, status, etc.)
3. Create roles
4. Create settings
5. Create media files & folders
6. Create google_drive_credentials
7. Create activity_logs

### Seeders

- `packages/Core/database/seeders/AdminSeeder.php` — Creates admin user + Super Admin role

### Views

All in `packages/Core/resources/views/`:
- `admin/` — Admin dashboard, user/role management
- `auth/` — Login, register forms
- `media/` — Media manager UI
- `layouts/admin.blade.php` — Main admin layout
- `components/` — CKEditor, modals, form fields

### Helpers

- `packages/Core/src/Helpers/core_helper.php` — General helpers
- `packages/Core/src/Helpers/media_helper.php` — Media-related helpers
- `packages/Core/src/Helpers/file_manager_helper.php` — File manager helpers

---

## 11. Key Dependencies

### Composer (PHP)

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1",
        "packages/core": "@dev"
    }
}
```

### NPM (Frontend)

```json
{
    "devDependencies": {
        "vite": "^latest",
        "tailwindcss": "^latest"
    }
}
```

---

## 12. Development Entry Points

### For Backend Developers

Start reading:
1. `docs/guide.md` — Quick rules
2. `docs/code-standards.md` — Codebase standards
3. `docs/development.md` — Detailed guide
4. `packages/Core/src/Providers/CoreServiceProvider.php` — How Core bootstraps
5. `packages/Core/src/Repositories/BaseRepository.php` — Base repository
6. `packages/Core/src/Services/PermissionService.php` — RBAC logic

### For DevOps

1. `.env.example` — Required environment variables
2. `config/database.php` — Database setup
3. `docs/deployment-guide.md` — Production checklist
4. `composer.json` → scripts:setup, scripts:dev

### For New Package Development

1. `php artisan make:package MyPackage`
2. Follow structure in `packages/Core/stubs/package/`
3. Read `docs/guide.md` → Repository Pattern section
4. Copy from existing Core package (Models, Controllers, Services)

---

## 13. Testing & Quality

### Test structure

```
tests/
├── Feature/
│   └── Admin/UserControllerTest.php
└── Unit/
    ├── UserRepositoryTest.php
    └── UserServiceTest.php
```

### Running tests

```bash
php artisan test                          # All tests
php artisan test tests/Feature/...        # Feature tests only
php artisan test --coverage               # Coverage report
```

### Code style

```bash
./vendor/bin/pint              # Format code
npm run lint                   # Lint JavaScript
```

---

## 14. Common Workflows

### Create a new resource (e.g., Reports)

1. Create package: `php artisan make:package Report`
2. Create Model: `Report extends BaseModel`
3. Create Repository & Interface
4. Register Repository in ServiceProvider
5. Create Service with business logic
6. Create Controller extending BaseController
7. Create FormRequests (StoreReportRequest, UpdateReportRequest)
8. Create routes (routes/admin.php)
9. Create permissions (configs/permissions.php)
10. Create views (resources/views/admin/reports/)
11. Create migrations
12. Run: `composer dump-autoload` → `php artisan migrate`

### Deploy to production

1. Copy `.env.example` → `.env`
2. Update env vars (DB, APP_ENC_KEY, MEDIA_STORAGE_DRIVER, etc.)
3. `composer install --no-dev`
4. `php artisan migrate --force`
5. `php artisan config:cache`
6. `php artisan route:cache`
7. `npm run build`
8. Set permissions on `storage/` and `bootstrap/cache/`
9. Restart PHP-FPM / app server

---

---

## 15. Chatbot Package — Phase 5

Server-side proxy to Tourism API (https://tourrismbotapi.onelink.vn). Keeps API key in `.env`, relays SSE streams to client, Alpine.js floating widget, 10-message limit per session, Redis counter with DB fallback.

**Key components:**
- `TourismApiClient` (Guzzle + JWT cache)
- `ChatbotSessionService` (Redis atomic counter + DB fallback)
- `ChatbotStreamRelay` (SSE handler)
- `ChatbotController` (GET /session, POST /message)
- `EnsureChatbotSession` middleware (UUID cookie, 24h TTL)
- Alpine widget (floating bottom-right, typewriter effect, hidden on Inquiry routes)

**Routes:**
- GET `/chatbot/session` — Initialize session
- POST `/chatbot/message` — Stream message
- GET|PUT `/admin/chatbot/settings` — Admin config

**Database:**
- `chatbot_sessions` — UUID, message_count, user_agent, expires_at

**Dependencies added:**
- `guzzlehttp/guzzle` — HTTP client
- `predis/predis` — Redis client

*Cập nhật: 20/05/2026*
