# Core Package - Cấu trúc thư mục

> Tài liệu mô tả chi tiết cấu trúc của package Core — Nền tảng cho hệ thống.

---

## Tổng quan cấu trúc

```
packages/Core/
├── composer.json                    # PSR-4: Packages\Core\Src\ + Packages\Core\Database\
│
├── configs/
│   ├── core.php                     # Cấu hình chung (enc_key, app settings)
│   ├── permissions.php              # Định nghĩa quyền RBAC
│   └── file-manager.php             # Cấu hình Media Manager
│
├── database/
│   ├── migrations/                  # 8 migration files
│   │   ├── create_roles_table
│   │   ├── add_fields_to_users_table
│   │   ├── create_settings_table
│   │   ├── create_images_table       # Media files & folders
│   │   ├── seed_default_media_settings
│   │   ├── create_google_drive_credentials_table
│   │   ├── add_storage_driver_to_media_files
│   │   └── create_activity_logs_table
│   └── seeders/
│       └── AdminSeeder.php          # Auto-detect DB structure (Core/Spatie)
│
├── docs/
│   ├── development.md               # Hướng dẫn phát triển package
│   ├── guide.md                     # Quy tắc bắt buộc
│   └── structe.md                   # File này
│
├── resources/
│   ├── lang/                        # Đa ngôn ngữ (en, vi)
│   └── views/
│       ├── admin/                   # Admin dashboard, test-editor
│       ├── auth/                    # Login, Register
│       ├── media/                   # Media Manager UI
│       ├── layouts/                 # Layout chính (admin.blade.php)
│       └── components/              # CKEditor, Blade components
│
├── routes/
│   ├── web.php                      # Routes trang web
│   ├── admin.php                    # Routes admin panel
│   ├── auth.php                     # Routes authentication
│   └── media.php                    # Routes Media Manager API
│
├── src/
│   ├── Chunks/                      # Chunk upload system
│   │   ├── ChunkFile.php
│   │   ├── FileMerger.php
│   │   ├── Save/ChunkSave.php
│   │   ├── Storage/ChunkStorage.php
│   │   └── Exceptions/ChunkSaveException.php
│   │
│   ├── Console/Commands/            # 4 Artisan commands
│   │   ├── MakePackageCommand.php   # php artisan make:package
│   │   ├── MakeTableCommand.php     # php artisan make:table
│   │   ├── ClearChunksCommand.php   # php artisan chunks:clear
│   │   └── MediaCleanupCommand.php  # php artisan media:cleanup (cron */5)
│   │
│   ├── Enums/                       # PHP 8.1+ Enums
│   │   ├── UserStatus.php           # active, locked, pending
│   │   └── StorageDriver.php        # local, google_drive
│   │
│   ├── Events/                      # 7 Domain Events
│   │   ├── UserCreated/Updated/Deleted.php
│   │   ├── RoleChanged.php
│   │   ├── SettingChanged.php
│   │   └── MediaUploaded/Deleted.php
│   │
│   ├── Helpers/                     # 3 Helper files
│   │   ├── core_helper.php
│   │   ├── media_helper.php
│   │   └── file_manager_helper.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BaseController.php   # Controller base class
│   │   │   ├── Admin/               # 9 Admin controllers
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
│   │   ├── Middleware/
│   │   │   └── PermissionMiddleware.php
│   │   ├── Requests/                # 13 Form Requests
│   │   │   ├── StoreUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   ├── StoreRoleRequest.php
│   │   │   ├── UpdateRoleRequest.php
│   │   │   └── Media/ (9 requests)
│   │   └── Resources/
│   │       ├── MediaFileResource.php
│   │       └── MediaFolderResource.php
│   │
│   ├── Models/                      # 9 Eloquent Models
│   │   ├── BaseModel.php            # Base model với common traits
│   │   ├── User.php                 # User (extends Authenticatable)
│   │   ├── Role.php                 # RBAC roles
│   │   ├── Setting.php              # Key-value settings
│   │   ├── ActivityLog.php          # Admin activity tracking
│   │   ├── GoogleDriveCredential.php # GDrive OAuth tokens
│   │   ├── MediaFile.php            # Media files
│   │   ├── MediaFolder.php          # Media folders
│   │   └── MediaSetting.php         # Media config
│   │
│   ├── Providers/
│   │   └── CoreServiceProvider.php  # Auto-discover via composer.json
│   │
│   ├── Repositories/
│   │   ├── Eloquent/                # 9 Implementations
│   │   │   ├── BaseRepository.php
│   │   │   ├── UserRepository.php
│   │   │   ├── RoleRepository.php
│   │   │   ├── SettingRepository.php
│   │   │   ├── ActivityLogRepository.php
│   │   │   ├── GoogleDriveCredentialRepository.php
│   │   │   ├── MediaFileRepository.php
│   │   │   ├── MediaFolderRepository.php
│   │   │   └── MediaSettingRepository.php
│   │   └── Interfaces/              # 9 Contracts
│   │
│   ├── Services/                    # 13 Services
│   │   ├── BaseService.php
│   │   ├── PermissionService.php    # RBAC permissions
│   │   ├── EncryptionService.php    # AES encryption (APP_ENC_KEY)
│   │   ├── StorageDriverService.php # Local / Google Drive storage
│   │   ├── GoogleDriveService.php   # GDrive API operations
│   │   ├── SidebarService.php       # Admin sidebar navigation
│   │   ├── SidebarItem.php          # Sidebar DTO
│   │   ├── WidgetService.php        # Dashboard widgets
│   │   ├── WidgetItem.php           # Widget DTO
│   │   ├── MediaFileService.php     # File upload, delete, move
│   │   ├── MediaFolderService.php   # Folder CRUD
│   │   ├── MediaResizeService.php   # Image resizing
│   │   └── DocumentPreviewService.php
│   │
│   ├── Tables/                      # Table Builder System
│   │   ├── Table.php                # Inline builder
│   │   ├── BaseTable.php            # Base class-based
│   │   ├── UserTable.php            # Example
│   │   ├── Columns/ (8 types)       # Text, Badge, Boolean, Date, Numeric, Image, Avatar
│   │   ├── Filters/ (4 types)       # Base, Select, Boolean, Text
│   │   ├── Actions/ (2 types)       # Action, BulkAction
│   │   └── Contracts/ (3 interfaces)
│   │
│   ├── Traits/                      # 5 Reusable traits
│   │   ├── Filterable.php           # Query filtering cho models
│   │   ├── HasPermission.php        # Permission checks
│   │   ├── HasTable.php             # Table Builder integration
│   │   ├── LogsAdminActivity.php    # Auto-log admin actions
│   │   └── LoadAndPublishDataTrait.php # Package asset publishing
│   │
│   └── View/Components/Media/       # 4 Blade components
│       ├── ConfirmModal.php
│       ├── ActionFormModal.php
│       ├── InputField.php
│       └── ButtonField.php
│
└── stubs/package/                   # Templates cho make:package (12+ files)
```

---

## Namespace

```
Packages\Core\Src\          → src/         # Source code
Packages\Core\Database\     → database/    # Seeders, migrations
```

---

## Models (9)

| Model | Mô tả |
|-------|--------|
| `BaseModel` | Base class với common traits |
| `User` | Người dùng (extends Authenticatable) |
| `Role` | Vai trò RBAC |
| `Setting` | Key-value cài đặt |
| `ActivityLog` | Log thao tác admin |
| `GoogleDriveCredential` | OAuth tokens Google Drive |
| `MediaFile` | File trong Media Manager |
| `MediaFolder` | Thư mục Media Manager |
| `MediaSetting` | Cài đặt Media |

## Repositories (9)

| Repository | Interface |
|------------|-----------|
| `BaseRepository` | `RepositoryInterface` |
| `UserRepository` | `UserRepositoryInterface` |
| `RoleRepository` | `RoleRepositoryInterface` |
| `SettingRepository` | `SettingRepositoryInterface` |
| `ActivityLogRepository` | `ActivityLogRepositoryInterface` |
| `GoogleDriveCredentialRepository` | `GoogleDriveCredentialRepositoryInterface` |
| `MediaFileRepository` | `MediaFileRepositoryInterface` |
| `MediaFolderRepository` | `MediaFolderRepositoryInterface` |
| `MediaSettingRepository` | `MediaSettingRepositoryInterface` |

## Services (13)

| Service | Mô tả |
|---------|--------|
| `BaseService` | Service base class |
| `PermissionService` | RBAC permissions |
| `EncryptionService` | AES encryption (cần `APP_ENC_KEY` trong `.env`) |
| `StorageDriverService` | Local / Google Drive storage switching |
| `GoogleDriveService` | Google Drive API operations |
| `SidebarService` + `SidebarItem` | Admin sidebar navigation |
| `WidgetService` + `WidgetItem` | Dashboard widgets |
| `MediaFileService` | Upload, xóa, di chuyển files (chunk upload) |
| `MediaFolderService` | CRUD folders |
| `MediaResizeService` | Resize ảnh |
| `DocumentPreviewService` | Preview documents |

## Traits (5)

| Trait | Mô tả |
|-------|--------|
| `Filterable` | Query filtering cho models |
| `HasPermission` | Permission checks cho controllers |
| `HasTable` | Tích hợp Table Builder |
| `LogsAdminActivity` | Auto-log thao tác admin (create, update, delete) |
| `LoadAndPublishDataTrait` | Publish views, configs, migrations cho packages |

## Enums (2)

| Enum | Values |
|------|--------|
| `UserStatus` | `active`, `locked`, `pending` |
| `StorageDriver` | `local`, `google_drive` |

---

## Service Provider

Package tự động đăng ký qua Laravel Package Discovery:

```json
{
    "extra": {
        "laravel": {
            "providers": ["Packages\\Core\\Src\\Providers\\CoreServiceProvider"]
        }
    }
}
```

---

*Cập nhật: 14/03/2026*
