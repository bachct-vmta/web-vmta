# Core Package

Package cốt lõi cho hệ thống SMM Panel, cung cấp authentication, RBAC, base classes, và Table Builder.

## Chức năng

- **Authentication**: Đăng nhập, đăng ký, logout
- **RBAC**: Role-based access control với permissions
- **Base Classes**: BaseModel, BaseRepository, BaseController
- **Table Builder**: Declarative table generation system
- **Widget Service**: Dynamic dashboard widgets
- **Sidebar Service**: Dynamic admin navigation
- **Traits**: Filterable, HasPermission, LoadAndPublishDataTrait

---

## Cấu trúc

```
Core/
├── configs/
│   └── permissions.php
├── database/migrations/
├── resources/views/
│   ├── layouts/admin.blade.php
│   ├── admin/
│   ├── auth/
│   └── components/table/
├── routes/
│   ├── web.php
│   ├── admin.php
│   └── auth.php
└── src/
    ├── Console/Commands/
    ├── Http/Controllers/
    ├── Http/Middleware/
    ├── Models/
    ├── Providers/
    ├── Repositories/
    ├── Services/
    ├── Tables/           ← NEW: Table Builder
    │   ├── Table.php
    │   ├── BaseTable.php
    │   ├── Columns/
    │   ├── Filters/
    │   └── Actions/
    └── Traits/
```

---

## Table Builder

### Overview

Table Builder cho phép tạo data tables một cách declarative, giảm code lặp lại trong views.

**Features:**
- ✅ Columns với sorting, searching
- ✅ Filters (Select, Boolean, Text)
- ✅ Row actions với permission checks
- ✅ Pagination tự động
- ✅ 2 patterns: Inline Builder & Class-based

---

### Option 1: Inline Builder (Quick & Simple)

Dùng cho tables đơn giản, không cần tái sử dụng:

```php
use Packages\Core\Src\Tables\Table;
use Packages\Core\Src\Tables\Columns\{TextColumn, BadgeColumn, BooleanColumn};
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Actions\Action;

public function index()
{
    $table = Table::make(User::with('role'))
        ->heading('Quản lý người dùng')
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('email')
                ->searchable(),
            BadgeColumn::make('role.name')
                ->label('Vai trò')
                ->colors(['Admin' => 'red', 'User' => 'gray']),
            BooleanColumn::make('is_active')
                ->label('Trạng thái'),
        ])
        ->filters([
            SelectFilter::make('role_id')
                ->options(Role::pluck('name', 'id')),
        ])
        ->actions([
            Action::make('edit')
                ->iconEdit()
                ->route('admin.users.edit')
                ->permission('users.edit'),
            Action::make('delete')
                ->iconDelete()
                ->route('admin.users.destroy')
                ->method('DELETE')
                ->confirm('Bạn có chắc?')
                ->danger()
                ->permission('users.delete'),
        ])
        ->paginate(15);

    return view('admin.users.index', ['table' => $table]);
}
```

**Trong Blade:**
```blade
{!! $table !!}
```

---

### Option 2: Class-based Table (Reusable)

Dùng cho tables phức tạp, cần tái sử dụng:

**Tạo table class:**
```bash
php artisan make:table ProductTable --model=Product --package=Inventory
```

**ProductTable.php:**
```php
namespace Packages\Inventory\Src\Tables;

use Packages\Core\Src\Tables\BaseTable;

class ProductTable extends BaseTable
{
    protected ?string $heading = 'Quản lý sản phẩm';
    protected int $perPage = 20;

    protected function model(): string
    {
        return Product::class;
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            NumericColumn::make('price')->money('đ'),
            BooleanColumn::make('is_active'),
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
        ];
    }
}
```

**Controller (3 dòng!):**
```php
public function index(ProductTable $table)
{
    return view('inventory::admin.products.index', ['table' => $table]);
}
```

---

### Column Types

| Type | Class | Mô tả |
|------|-------|-------|
| Text | `TextColumn` | Text với limit, prefix, suffix |
| Badge | `BadgeColumn` | Colored pill-style tags, auto-detect Enum |
| Boolean | `BooleanColumn` | Check/cross Material icons |
| Date | `DateColumn` | Format, diffForHumans |
| Numeric | `NumericColumn` | Number format, currency |
| Image | `ImageColumn` | Thumbnails |
| Avatar | `AvatarColumn` | User avatar với initials, gradient colors, secondary text |

**Column Methods:**
```php
TextColumn::make('name')
    ->label('Custom Label')
    ->searchable()
    ->sortable()
    ->alignRight()
    ->limit(50)
    ->default('N/A');
```

---

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
```

---

### Action Types

**Row Actions:**
```php
Action::make('edit')
    ->label('Sửa')
    ->iconEdit()              // preset icons: iconEdit, iconDelete, iconView
    ->route('admin.users.edit')
    ->permission('users.edit');

Action::make('delete')
    ->iconDelete()
    ->route('admin.users.destroy')
    ->method('DELETE')
    ->confirm('Bạn có chắc muốn xóa?')
    ->danger()                // red color
    ->hidden(fn($user) => $user->is_super_user);
```

---

## Repositories

Core sử dụng Repository Pattern cho tất cả database queries:

```php
class YourController extends BaseController
{
    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository
    ) {}
    
    public function index()
    {
        // ✅ ĐÚNG: Sử dụng Repository
        $users = $this->userRepository->paginateFiltered(['search' => 'test']);
        
        // ❌ SAI: Query trực tiếp Model
        // $users = User::where(...)->get();
    }
}
```

**Available Repositories:**
- `UserRepository` - CRUD cho User
- `RoleRepository` - CRUD cho Role
- `SettingRepository` - Wrapper cho settings

---

## Widget Service

Đăng ký widgets cho dashboard:

```php
use Packages\Core\Src\Services\{WidgetService, WidgetItem};

app(WidgetService::class)->registerWidget(new WidgetItem(
    name: 'Tổng đơn hàng',
    value: fn() => Order::count(),
    icon: '<svg>...</svg>',
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600',
    permission: 'orders.index',
    priority: 10
));
```

---

## Blade Directives

```blade
@permission('users.create')
    <!-- Visible if user has permission -->
@endpermission

@superuser
    <!-- Only for super admin -->
@endsuperuser

@role('admin')
    <!-- Only for users with admin role -->
@endrole
```

---

## Artisan Commands

| Command | Mô tả |
|---------|-------|
| `php artisan make:package {Name}` | Tạo package mới |
| `php artisan make:table {Name}` | Tạo table class |

```bash
# Tạo table cho package Customer
php artisan make:table CustomerTable --model=Customer --package=Customer
```

---

## License

MIT
