# Kiến trúc hệ thống

Tài liệu mô tả kiến trúc tổng thể, thành phần, và luồng dữ liệu trong **VMTA_Laravel — Vietnam Medical Tourism Alliance (Phase 1)**.

Phase 1 thay thế site WordPress `https://vmta.vn` bằng Laravel 12 monolith mô-đun: tận dụng `packages/Core` đã có (RBAC, Media, ActivityLog, TableBuilder, Settings) cộng 8 package nghiệp vụ mới (Localization, Site, Content, Catalog, Inquiry, Newsletter, Chatbot, Report).

Định hướng & scope: xem `docs/project-overview-pdr.md`. Lộ trình chi tiết: `plans/260517-vmta-migration-brainstorm/plan.md`.

---

## 1. Tổng quan kiến trúc

### Monolith mô-đun (Modular Monolith)

```
┌─────────────────────────────────────────────────────────────┐
│                  Laravel 12 Application                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │             HTTP Layer (Routes)                       │   │
│  │  - routes/web.php (public)                            │   │
│  │  - Core/routes/admin.php (admin panel)                │   │
│  │  - Core/routes/auth.php (authentication)              │   │
│  │  - Core/routes/media.php (media API)                  │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │          Middleware Layer                             │   │
│  │  - Authentication (auth)                              │   │
│  │  - Permission Middleware (permission:*)               │   │
│  │  - CSRF, Rate Limiting                                │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │       Controller Layer (BaseController)               │   │
│  │  - Admin Controllers (User, Role, Media, etc.)        │   │
│  │  - Auth Controllers (Login, Register)                 │   │
│  │  - Request validation (FormRequest)                   │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │       Service Layer (Business Logic)                │    │
│  │  - PermissionService (RBAC)                          │    │
│  │  - MediaFileService (File operations)                │    │
│  │  - GoogleDriveService (Cloud storage)                │    │
│  │  - UserService, RoleService, etc.                    │    │
│  └─────────────────────────────────────────────────────┘    │
│                          ↓                                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │    Repository Layer (Data Access)                   │    │
│  │  - UserRepository, RoleRepository                    │    │
│  │  - MediaFileRepository, MediaFolderRepository        │    │
│  │  - Interfaces for contracts                          │    │
│  └─────────────────────────────────────────────────────┘    │
│                          ↓                                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │        Model Layer (Data Schema)                    │    │
│  │  - User, Role, MediaFile, MediaFolder, etc.          │    │
│  │  - Relationships, accessors, mutators                │    │
│  └─────────────────────────────────────────────────────┘    │
│                          ↓                                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │    Database Layer (SQLite/MySQL/PostgreSQL)         │    │
│  │  - Migrations manage schema                          │    │
│  │  - Queries executed                                  │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│              Service Layer (Horizontal Services)           │
│  - Event dispatcher (Domain events)                        │
│  - Queue/Job system (Background jobs)                      │
│  - Caching (Database, Redis optional)                      │
│  - Encryption (APP_ENC_KEY)                                │
│  - File storage (Local, Google Drive)                      │
│  - Activity logging (Audit trail)                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Chatbot Proxy Architecture (Phase 5)

### Server-Side Proxy Flow

```
┌──────────────────┐
│  Client Browser  │
│  (Alpine.js)     │
└────────┬─────────┘
         │ POST /chatbot/message
         │ { text: "..." }
         ↓
┌──────────────────────────────────────┐
│  VMTA_Laravel                        │
│                                      │
│  ChatbotController::message()        │
│  ├── Check rate limit (Redis)        │
│  ├── Validate session cookie         │
│  └── Call ChatbotStreamRelay         │
│      ↓                               │
│  ChatbotStreamRelay                  │
│  ├── Open SSE connection to client   │
│  ├── Call TourismApiClient           │
│  │   └── Guzzle SSE request          │
│  └── Pipe upstream → browser         │
│      (event: data → message: type)   │
└──────────────┬───────────────────────┘
               │
               ↓ (Guzzle SSE)
┌──────────────────────────────────┐
│  Tourism API                     │
│  (tourrismbotapi.onelink.vn)     │
│  [API key kept server-side]      │
└──────────────────────────────────┘
```

### Session Management

```
Initial request
  ↓
EnsureChatbotSession middleware
  ├── Check for existing chatbot_uuid cookie
  ├── If missing:
  │   ├── Generate UUID v4
  │   ├── Create ChatbotSession record (expires_at +24h)
  │   ├── Set Redis counter to 0 (TTL 24h)
  │   └── Set HTTP-only cookie (24h)
  ↓
Request proceeds
  ├── Session enforced
  ├── Message limit checked (Redis INCR)
  ├── If exceeds 10 → SessionLimitReachedException
  └── User sees CTA to contact coordinator
```

### Rate Limiting & Fallback

```
POST /chatbot/message
  ↓
ChatbotSessionService::incrementMessageCount()
  ├── Try Redis: INCR chatbot_session_{uuid}
  │   ├── Success → return count
  │   └── Redis down → catch exception
  ├── If Redis fail:
  │   └── Query DB: chatbot_sessions.message_count++
  ├── If count > 10:
  │   └── throw SessionLimitReachedException
  └── Proceed to API call
```

---

## 3. Core Package Architecture

### Package Structure

```
packages/Core/
├── configs/
│   ├── core.php              # Admin prefix, route names
│   ├── permissions.php       # RBAC permission matrix
│   └── file-manager.php      # Media storage config
│
├── database/
│   ├── migrations/           # 8 migrations
│   │   ├── *_create_roles_table
│   │   ├── *_add_fields_to_users_table
│   │   ├── *_create_settings_table
│   │   ├── *_create_media_files_table
│   │   ├── *_create_media_folders_table
│   │   ├── *_create_google_drive_credentials_table
│   │   ├── *_add_storage_driver_to_media_files_table
│   │   └── *_create_activity_logs_table
│   └── seeders/
│       └── AdminSeeder.php   # Creates default admin user
│
├── resources/
│   ├── lang/
│   │   ├── en/               # English translations
│   │   └── vi/               # Vietnamese translations
│   └── views/
│       ├── admin/            # Admin dashboard, CRUD forms
│       ├── auth/             # Login, register forms
│       ├── media/            # Media manager UI
│       ├── components/       # CKEditor, modals
│       └── layouts/
│           └── admin.blade.php
│
├── routes/
│   ├── web.php               # Public routes
│   ├── admin.php             # /admin/* routes (guarded)
│   ├── auth.php              # /login, /register, /logout
│   └── media.php             # /admin/media/* API routes
│
├── src/
│   ├── Chunks/               # Chunked upload
│   │   ├── ChunkFile.php
│   │   ├── FileMerger.php
│   │   ├── Save/ChunkSave.php
│   │   └── Storage/ChunkStorage.php
│   │
│   ├── Console/Commands/     # 4 Artisan commands
│   │   ├── MakePackageCommand.php
│   │   ├── MakeTableCommand.php
│   │   ├── ClearChunksCommand.php
│   │   └── MediaCleanupCommand.php
│   │
│   ├── Enums/
│   │   ├── UserStatus.php
│   │   └── StorageDriver.php
│   │
│   ├── Events/               # 7 domain events
│   │   ├── UserCreated.php
│   │   ├── UserUpdated.php
│   │   ├── UserDeleted.php
│   │   ├── RoleChanged.php
│   │   ├── SettingChanged.php
│   │   ├── MediaUploaded.php
│   │   └── MediaDeleted.php
│   │
│   ├── Helpers/              # Global functions
│   │   ├── core_helper.php
│   │   ├── media_helper.php
│   │   └── file_manager_helper.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BaseController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── MediaController.php
│   │   │   │   ├── MediaFileController.php
│   │   │   │   ├── MediaFolderController.php
│   │   │   │   ├── MediaSettingsController.php
│   │   │   │   ├── GoogleDriveController.php
│   │   │   │   └── CKEditorController.php
│   │   │   └── Auth/
│   │   │       ├── LoginController.php
│   │   │       └── RegisterController.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── PermissionMiddleware.php
│   │   │
│   │   ├── Requests/         # 13 form validation classes
│   │   │   ├── StoreUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   ├── StoreRoleRequest.php
│   │   │   ├── UpdateRoleRequest.php
│   │   │   └── Media/* (9 request classes)
│   │   │
│   │   └── Resources/        # API resources
│   │       ├── MediaFileResource.php
│   │       └── MediaFolderResource.php
│   │
│   ├── Models/               # 9 Eloquent models
│   │   ├── BaseModel.php
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Setting.php
│   │   ├── ActivityLog.php
│   │   ├── GoogleDriveCredential.php
│   │   ├── MediaFile.php
│   │   ├── MediaFolder.php
│   │   └── MediaSetting.php
│   │
│   ├── Repositories/
│   │   ├── Eloquent/         # 9 implementations
│   │   │   ├── BaseRepository.php
│   │   │   ├── UserRepository.php
│   │   │   ├── RoleRepository.php
│   │   │   ├── SettingRepository.php
│   │   │   ├── ActivityLogRepository.php
│   │   │   ├── GoogleDriveCredentialRepository.php
│   │   │   ├── MediaFileRepository.php
│   │   │   ├── MediaFolderRepository.php
│   │   │   └── MediaSettingRepository.php
│   │   └── Interfaces/       # 9 contracts
│   │
│   ├── Services/             # 13 services
│   │   ├── BaseService.php
│   │   ├── PermissionService.php
│   │   ├── EncryptionService.php
│   │   ├── StorageDriverService.php
│   │   ├── GoogleDriveService.php
│   │   ├── SidebarService.php & SidebarItem.php
│   │   ├── WidgetService.php & WidgetItem.php
│   │   ├── MediaFileService.php
│   │   ├── MediaFolderService.php
│   │   ├── MediaResizeService.php
│   │   └── DocumentPreviewService.php
│   │
│   ├── Tables/               # Table builder
│   │   ├── Table.php (inline builder)
│   │   ├── BaseTable.php (class-based)
│   │   ├── UserTable.php (example)
│   │   ├── Columns/          # 8 column types
│   │   ├── Filters/          # 4 filter types
│   │   ├── Actions/          # 2 action types
│   │   └── Contracts/        # Interfaces
│   │
│   ├── Traits/               # 5 reusable traits
│   │   ├── Filterable.php
│   │   ├── HasPermission.php
│   │   ├── HasTable.php
│   │   ├── LogsAdminActivity.php
│   │   └── LoadAndPublishDataTrait.php
│   │
│   ├── View/Components/      # 4 Blade components
│   │   ├── ConfirmModal.php
│   │   ├── ActionFormModal.php
│   │   ├── InputField.php
│   │   └── ButtonField.php
│   │
│   └── Providers/
│       └── CoreServiceProvider.php
│
└── stubs/package/            # Templates for make:package
```

---

## 4. VMTA Feature Packages (Phase 1)

9 package nghiệp vụ chồng lên Core, mỗi package theo cùng skeleton (`composer.json`, `src/{Feature}ServiceProvider.php`, `src/Models|Repositories|Services|Http/Controllers|Http/Middleware`, `resources/views`, `routes/`, `database/migrations|seeders`, `configs/`).

| Package | Phase | Trách nhiệm chính | Phụ thuộc |
|---|---|---|---|
| `Localization` | 1 | i18n config, `SetLocaleFromRoute` middleware, `language-switcher` Blade component, wrap `mcamara/laravel-localization` | — |
| `Site` | 1 | Layout public Blade (`layouts/public`, `partials/header|footer|head-meta`), Tailwind/Alpine entry assets cho Vite | Localization |
| `Content` | 2 | Page, Post (+ category, tag), Menu. Mọi entity translatable (`astrotomic/laravel-translatable`) | Site, Core (Media) |
| `Catalog` | 3 | Specialty, Destination, Hospital, Doctor, Service, Package. Tích hợp `laravel/scout` + `teamtnt/tntsearch`, filter chuyên khoa × địa điểm | Site, Core (Media) |
| `Inquiry` | 4 | Inquiry form + Emergency form, pipeline trạng thái, email notify Coordinator, export CSV | Catalog, Core (Mail) |
| `Chatbot` | 5 | Proxy Tourism API server-side (giữ API key trong `.env`), SSE relay streaming, Alpine widget, rate-limit 10 msg/session, strict block khi reject cookie | Site, Settings (Core) |
| `Newsletter` | 6 | Subscribe form + double opt-in, danh sách admin | Core (Mail) |
| `Report` | 7 | 3 metric (inquiries/day, pageview top 10, chatbot session), chart Chart.js trên `/admin/reports`, schedule `metrics:flush` trong `routes/console.php` | Inquiry, Chatbot |
| `Dental` | 8 | Hạng mục "Khám nha": DentalCategory / DentalFacility / DentalService, bảng riêng prefix `dental_`, sidebar group + 3 màn quản trị, publish-draft, trang public `/{locale}/kham-nha/...` | Site, Core (Media), Inquiry (form tư vấn), Content (sidebar tin) |

**Lưu ý:** Settings **không** tạo package mới — extend `packages/Core` (Core đã có `settings` table). Phase 6 thêm migration phụ (`type`, `is_encrypted`), service helper `setting('key')`, admin UI editor nằm trong Core.

### Boot order (Service Provider)

```
1. Core           (RBAC, Media, ActivityLog, Settings, TableBuilder)
2. Localization   (locale middleware + helper, must boot trước Site & route group)
3. Site           (layout, asset publish)
4. Content
5. Catalog
6. Inquiry
7. Chatbot
8. Newsletter
9. Report
10. Dental
```

Đăng ký trong `bootstrap/providers.php` (Laravel 12 convention) hoặc auto-discovery qua `composer.json` `extra.laravel.providers`.

---

## 5. Service Provider Boot Sequence

```
1. Laravel bootstraps (bootstrap/app.php)
   ↓
2. Composer autoload discovers packages
   - Reads extra.laravel.providers from all composer.json files
   - Registers all service providers
   ↓
3. CoreServiceProvider::register() — Service container
   ├── Singleton repositories
   ├── Singleton services
   └── Binding interfaces to implementations
   ↓
4. CoreServiceProvider::boot() — Bootstrap
   ├── Load migrations
   ├── Load routes (web, admin, auth, media)
   ├── Publish views
   ├── Load translations
   └── Register permissions
   ↓
5. Feature package service providers (if any)
   ├── Register their repositories
   ├── Boot their routes, views, permissions
   └── Register events, jobs, commands
   ↓
6. Application ready
   - Routes compiled
   - Services configured
   - Ready for requests
```

---

## 6. RBAC (Role-Based Access Control) System

### Permission Model

```
User
├── Roles (many-to-many via pivot table)
│   └── permissions (JSON: { "users.create": true, ... })
└── permissions (JSON override: { "reports.delete": false, ... })
```

### Permission Check Flow

```
Request
  ↓
PermissionMiddleware ('permission:users.create')
  ├── Is authenticated? → No → abort(401)
  ├── Is super admin? → Yes → allow
  ├── User has override? → Check user-level override
  ├── User has role permission? → Check role permissions
  └── Allow or abort(403)
  ↓
Controller executes
  ↓
View renders with @permission, @role, @superuser directives
```

### Permission Definition

```php
// configs/permissions.php
return [
    'users' => [
        'view' => 'View users',
        'create' => 'Create users',
        'edit' => 'Edit users',
        'delete' => 'Delete users',
    ],
    'roles' => [
        'view', 'create', 'edit', 'delete'
    ],
    'reports' => [
        'view', 'create', 'edit', 'delete'
    ],
];
```

### Usage in Code

```php
// Route-level
Route::post('/users', [UserController::class, 'store'])
    ->middleware('permission:users.create');

// Controller-level
if (!$this->hasPermission('users.edit')) {
    abort(403);
}

// Blade template
@permission('users.view')
    <button>View Users</button>
@endpermission

@role('admin')
    <button>Admin Only</button>
@endrole

@superuser
    <button>Super Admin Only</button>
@endsuperuser
```

---

## 7. Media Storage Architecture

### Multi-Driver Support

```
StorageDriverService
├── Detects MEDIA_STORAGE_DRIVER
├── Routes to appropriate driver:
│   ├── Local Storage
│   │   ├── Upload to public/uploads/
│   │   ├── Generate public URL
│   │   └── Delete from disk
│   │
│   └── Google Drive Storage
│       ├── GoogleDriveService
│       │   ├── Authenticate (OAuth)
│       │   ├── Upload to GDrive folder
│       │   ├── Generate sharing link
│       │   └── Delete from GDrive
│       │
│       └── GoogleDriveCredential model
│           └── Stores encrypted tokens
```

### Chunked Upload Flow

```
Frontend
  ↓
Chunk 1 → POST /admin/media/upload
          ├── ChunkStorage::save() → store/chunks/chunk-0
          ├── ChunkFile record created
          └── Response: { uploaded: 1/3 }
  ↓
Chunk 2 → POST /admin/media/upload
          ├── ChunkStorage::save() → store/chunks/chunk-1
          ├── ChunkFile record created
          └── Response: { uploaded: 2/3 }
  ↓
Chunk 3 → POST /admin/media/upload
          ├── ChunkStorage::save() → store/chunks/chunk-2
          ├── ChunkFile record created
          ├── All chunks present? → YES
          ├── FileMerger::merge() → store/uploads/final-file.zip
          ├── MediaFile record created
          ├── Event MediaUploaded dispatched
          ├── Clean up chunk files
          └── Response: { url: 'https://...', success: true }
```

### Image Resizing

```
Original upload (e.g., 3000x2000px)
  ↓
MediaResizeService
  ├── Thumbnail (300x200) → store/uploads/thumbs/
  ├── Medium (800x600) → store/uploads/medium/
  └── Large (1500x1000) → store/uploads/large/
  ↓
Stored in MediaFile.sizes JSON
  {
    "original": "https://...",
    "thumbnail": "https://...",
    "medium": "https://...",
    "large": "https://..."
  }
```

---

## 8. Event System

### Domain Events (7)

```
Event → Listener(s) → Action
  ↓
UserCreated → Send welcome email, create default settings
UserUpdated → Log change, clear cache
UserDeleted → Cleanup (delete sessions, files, etc.)
RoleChanged → Clear permission cache
SettingChanged → Clear settings cache
MediaUploaded → Generate thumbnails, index for search
MediaDeleted → Cleanup storage, remove previews
```

### Usage Pattern

```php
// Dispatch event
event(new UserCreated($user));

// Listen (in service provider or listener)
Event::listen(UserCreated::class, function (UserCreated $event) {
    Mail::send(new WelcomeEmail($event->user));
});
```

---

## 9. Queue & Job System

### Async Operations

```
Long-running operation (e.g., batch import)
  ↓
Dispatch job → Queue (database driver default)
  ↓
Return response to user (optimistic)
  ↓
Queue worker processes job
  ├── ProcessImportJob::handle()
  ├── Database transactions
  ├── Events dispatched
  └── Notifications sent
  ↓
User notified (webhook, email, etc.)
```

### Job Injection

```php
class ProcessReportJob implements ShouldQueue
{
    public function __construct(
        private int $reportId
    ) {}

    public function handle(
        ReportRepository $reportRepository,
        ReportService $reportService
    ): void {
        $report = $reportRepository->findById($this->reportId);
        $reportService->process($report);
    }
}

// Dispatch
ProcessReportJob::dispatch(report->id);
```

---

## 10. Caching Strategy

### Cache Layers

```
Request
  ↓
Database cache (default, stored in DB)
  ├── Settings cache
  ├── Permissions cache
  ├── Sidebar config cache
  └── Query results (custom)
  ↓
Redis cache (optional, production)
  ├── Session cache
  ├── Fragment cache
  └── Full page cache (tuỳ chọn)
```

### Cache invalidation

```
Setting changed → event(new SettingChanged($setting))
  ↓ Listener
Cache::forget('settings')
  ↓
Next request loads from DB
```

---

## 11. Authentication Flow

### Login Flow

```
POST /login
  ↓
LoginRequest validates email & password
  ↓
Auth::attempt() — verify credentials
  ├── User found?
  ├── Password matches?
  └── Account active? (status != 'locked')
  ↓
Session created (Laravel default)
  ├── Encrypted session cookie
  ├── User model loaded to Auth facade
  └── Remember token (if requested)
  ↓
Redirect to dashboard
```

### Middleware Stack

```
Incoming request
  ├── VerifyCsrfToken
  ├── ConvertEmptyStringsToNull
  ├── TrustProxies
  ├── HandleCors (if API)
  ├── Authenticate (auth middleware)
  ├── PermissionMiddleware (route-level)
  └── Request handler
```

---

## 12. Data Flow: Create User Example

```
1. User navigates to /admin/users/create
   ↓
2. Route: GET /admin/users/create → UserController::create()
   ├── Check permission (middleware)
   └── Return form view
   ↓
3. User fills form and submits
   POST /admin/users
   ↓
4. FormRequest validates (StoreUserRequest)
   ├── Check required fields
   ├── Check email unique
   ├── Validate role exists
   └── Auto-call $request->validated()
   ↓
5. UserController::store(StoreUserRequest $request)
   ├── Logged in admin user known
   ├── Call UserService::create($request->validated())
   ↓
6. UserService::create()
   ├── DB::transaction() wrap
   ├── Call UserRepository::create($data)
   ├── Dispatch event UserCreated($user)
   └── Return $user
   ↓
7. UserRepository::create($data)
   ├── Call $this->model->create($data)
   ├── User model creates record
   └── Return model instance
   ↓
8. Database — INSERT INTO users (...)
   ├── Generate auto-id
   ├── Store timestamps
   ├── Commit transaction
   ↓
9. Event UserCreated dispatched
   ├── Listeners react
   ├── Send welcome email (async job)
   ├── Create default settings
   └── Log activity (LogsAdminActivity trait)
   ↓
10. Response sent
    ├── Redirect to /admin/users/{id}
    ├── Flash message: "User created!"
    └── Update activity log
    ↓
11. Browser loads user show page
    ├── Route GET /admin/users/{id}
    ├── UserController::show($id)
    ├── UserRepository::findById($id)
    ├── Render view with user data
    └── Display to admin
```

---

## 13. Component Interactions

```
┌─────────────────────────────────────────────┐
│           Admin Frontend                    │
│  (Blade views + Tailwind CSS + Vite JS)     │
└────────────────────┬────────────────────────┘
                     │ HTTP Request/Response
                     ↓
┌─────────────────────────────────────────────┐
│         Routing Layer (routes/)              │
│  - Route matching                            │
│  - Middleware assignment                     │
└────────────────────┬────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────┐
│    Middleware Chain                         │
│  - Authentication                           │
│  - Permission checking                      │
│  - CSRF verification                        │
└────────────────────┬────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────┐
│       Controller Layer                      │
│  (User, Role, Media, etc.)                  │
│  - Request validation                       │
│  - Response formatting                      │
└────────────────────┬────────────────────────┘
                     │
          ┌──────────┴──────────┐
          ↓                     ↓
      ┌──────────┐       ┌──────────┐
      │ Service  │       │ Query    │
      │ Layer    │       │ Builder  │
      └──────────┘       └──────────┘
          │                    │
          ↓                    ↓
      ┌──────────────────────────────┐
      │   Repository Layer           │
      │  (Data access abstraction)    │
      └──────────────────┬───────────┘
                         │
                         ↓
      ┌──────────────────────────────┐
      │   Model Layer (Eloquent)     │
      │  (Data transformation)        │
      └──────────────────┬───────────┘
                         │
                         ↓
      ┌──────────────────────────────┐
      │   Query Builder              │
      │  (SQL generation)             │
      └──────────────────┬───────────┘
                         │
                         ↓
      ┌──────────────────────────────┐
      │   Database Connection        │
      │  (PDO, execute SQL)           │
      └──────────────────┬───────────┘
                         │
                         ↓
      ┌──────────────────────────────┐
      │   Database (SQLite/MySQL)    │
      │  (Data persistence)           │
      └──────────────────────────────┘
```

---

## 14. Security Architecture

### Input Validation

```
User input
  ↓
FormRequest validates (type, length, format)
  ├── Built-in rules (required, email, unique, etc.)
  ├── Custom rules
  └── Throw ValidationException if fails
  ↓
$request->validated() — guaranteed safe data
  ↓
Passed to Service/Repository
```

### Output Escaping

```
Data in database
  ↓
Model retrieves
  ↓
Controller passes to view
  ↓
Blade template
  ├── {{ $user->name }} — Auto-escaped (safe)
  ├── {!! $html !!} — Not escaped (dangerous, use only for trusted HTML)
  └── Response sent
```

### Authentication & Authorization

```
User logs in
  ↓
Auth::attempt() — verify credentials
  ↓
Session created
  ↓
Per-request:
  ├── Authenticate middleware — verify session valid
  ├── Permission middleware — check permission
  └── Controller executes
```

### Data Encryption

```
Sensitive data (e.g., Google Drive tokens)
  ↓
EncryptionService::encrypt() (using APP_ENC_KEY)
  ↓
Stored as encrypted string in DB
  ↓
On retrieve:
  ├── EncryptionService::decrypt()
  └── Original value returned
```

---

## 15. Performance Considerations

### Query Optimization

```
N+1 Problem (bad):
  $users = User::all();           // 1 query
  foreach ($users as $user) {
    echo $user->role->name;       // N queries
  }

Eager loading (good):
  $users = User::with('role')->all();  // 1 query
  foreach ($users as $user) {
    echo $user->role->name;  // No additional queries
  }
```

### Caching

```
Settings accessed on every request
  ↓
First request: Load from DB, cache result
  ↓
Subsequent requests: Load from cache
  ↓
On setting change: Invalidate cache
  ↓
Next request: Reload from DB
```

### Lazy Loading & Pagination

```
Table with 100k rows
  ↓
Paginate (15 per page)
  ├── Query: LIMIT 15 OFFSET 0
  ├── Return 15 items + pagination meta
  └── Reduce memory usage
```

---

## 16. Scalability Strategy

### Horizontal Scaling

```
Load balancer
  ├── Server 1 (PHP-FPM)
  ├── Server 2 (PHP-FPM)
  └── Server 3 (PHP-FPM)
      ↓
      Shared database
      ↓
      Shared file storage (cloud)
      ↓
      Shared cache layer (Redis)
```

### Stateless Architecture

- No server-side session files (use database/Redis)
- Each request can go to any server
- File uploads to cloud (S3, Google Drive)

---

## 17. Architecture Diagram (ASCII)

```
┌────────────────────────────────────────────────────┐
│              USER BROWSER                          │
│  (Admin dashboard, forms, uploads)                 │
└──────────────────────┬─────────────────────────────┘
                       │
                       │ HTTP/HTTPS
                       ↓
┌────────────────────────────────────────────────────┐
│          LARAVEL APPLICATION                       │
│  ┌───────────────────────────────────────────────┐ │
│  │ CORE PACKAGE (packages/Core/)                 │ │
│  │  - Models (User, Role, Media, etc.)           │ │
│  │  - Controllers (Admin CRUD)                   │ │
│  │  - Services (RBAC, Media, etc.)               │ │
│  │  - Repositories (Data access)                 │ │
│  │  - Table Builder                              │ │
│  │  - RBAC System                                │ │
│  │  - Media Manager (Local + Google Drive)       │ │
│  │  - Activity Logging                           │ │
│  │  - Chunked Upload                             │ │
│  └───────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────┐ │
│  │ FEATURE PACKAGES (packages/{Feature}/)        │ │
│  │  - Custom modules (Reports, Products, etc.)   │ │
│  └───────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────┐ │
│  │ SERVICES LAYER (Horizontal services)          │ │
│  │  - Event dispatcher                           │ │
│  │  - Queue/Job system                           │ │
│  │  - Caching (DB, Redis)                        │ │
│  │  - Encryption                                 │ │
│  └───────────────────────────────────────────────┘ │
└──────────────────────┬─────────────────────────────┘
                       │
       ┌───────────────┼───────────────┐
       ↓               ↓               ↓
  ┌─────────┐     ┌─────────┐    ┌──────────┐
  │Database │     │Storage  │    │Services  │
  │(SQLite/ │     │(Local/  │    │(Mail,    │
  │MySQL/   │     │GDrive)  │    │Queue,    │
  │PG)      │     │         │    │Cache)    │
  └─────────┘     └─────────┘    └──────────┘
```

---

*Cập nhật: 20/05/2026*
