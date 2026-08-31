<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Http\Requests\StoreRoleRequest;
use Packages\Core\Src\Http\Requests\UpdateRoleRequest;
use Packages\Core\Src\Models\Role;
use Packages\Core\Src\Repositories\Interfaces\RoleRepositoryInterface;
use Packages\Core\Src\Services\PermissionService;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Columns\TextColumn;
use Packages\Core\Src\Tables\Table;
use Packages\Core\Src\Traits\LogsAdminActivity;

class RoleController extends BaseController
{
    use LogsAdminActivity;

    /**
     * Inject Repository and Service via constructor
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private PermissionService $permissionService
    ) {}

    /**
     * Display a listing of roles using Table Builder
     */
    public function index(Request $request)
    {
        $table = Table::make(Role::withCount('users'))
            ->heading('Quản lý vai trò')
            ->columns([
                // Role Name with description
                TextColumn::make('name')
                    ->label('Vai trò')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($value, $record) => sprintf(
                        '<div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center">
                                <span class="material-symbols-rounded text-indigo-600 dark:text-indigo-400">shield_person</span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-white">%s</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">%s</div>
                            </div>
                        </div>',
                        e($value),
                        e($record->slug)
                    ))
                    ->html(),

                // User count badge
                NumericColumn::make('users_count')
                    ->label('Người dùng')
                    ->formatStateUsing(fn ($value) => sprintf(
                        '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            <span class="material-symbols-rounded text-[14px]">group</span>
                            %d người
                        </span>',
                        $value
                    ))
                    ->html()
                    ->sortable(),

                // Permissions count
                TextColumn::make('permissions')
                    ->label('Quyền hạn')
                    ->formatStateUsing(fn ($value) => sprintf(
                        '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                            <span class="material-symbols-rounded text-[14px]">key</span>
                            %d quyền
                        </span>',
                        is_array($value) ? count($value) : 0
                    ))
                    ->html(),

                // Default role
                BooleanColumn::make('is_default')
                    ->label('Mặc định')
                    ->labels('Mặc định', '-'),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Sửa')
                    ->iconEdit()
                    ->route('admin.roles.edit')
                    ->permission('roles.edit'),
                Action::make('more')
                    ->label('Thêm')
                    ->iconMore(),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Xóa')
                    ->icon('delete_sweep')
                    ->danger()
                    ->confirm('Bạn có chắc muốn xóa những vai trò đã chọn?')
                    ->permission('roles.delete'),
            ])
            ->defaultSort('id', 'asc')
            ->paginate(15);

        return view('core::admin.roles.index', compact('table'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $groupedPermissions = $this->permissionService->getGroupedPermissions();

        return view('core::admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Convert permissions array to flag => true format
        $permissions = [];
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $flag) {
                $permissions[$flag] = true;
            }
        }
        $validated['permissions'] = $permissions;

        $validated['is_default'] = $request->boolean('is_default', false);

        // If setting as default, remove default from others
        if ($validated['is_default']) {
            $this->roleRepository->clearDefault();
        }

        DB::transaction(function () use ($validated) {
            $this->roleRepository->createRole($validated);
        });

        return $this->redirectWithSuccess('admin.roles.index', 'Tạo vai trò thành công.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->loadCount('users');
        $groupedPermissions = $this->permissionService->getGroupedPermissions();

        return view('core::admin.roles.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $groupedPermissions = $this->permissionService->getGroupedPermissions();

        return view('core::admin.roles.edit', compact('role', 'groupedPermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Convert permissions array to flag => true format
        $permissions = [];
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $flag) {
                $permissions[$flag] = true;
            }
        }
        $validated['permissions'] = $permissions;

        $validated['is_default'] = $request->boolean('is_default', false);

        // If setting as default, remove default from others
        if ($validated['is_default'] && ! $role->is_default) {
            $this->roleRepository->clearDefault();
        }

        DB::transaction(function () use ($role, $validated) {
            $this->roleRepository->updateRole($role, $validated);
        });

        return $this->redirectWithSuccess('admin.roles.index', 'Cập nhật vai trò thành công.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Check if role has users
        if ($this->roleRepository->hasUsers($role)) {
            return $this->backWithError('Không thể xóa vai trò đang có người dùng.');
        }

        $this->roleRepository->deleteRole($role);

        return $this->redirectWithSuccess('admin.roles.index', 'Xóa vai trò thành công.');
    }

    /**
     * Bulk delete selected roles
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $validated['ids'])->get();
        $rolesWithUsers = $roles->filter(fn ($role) => $this->roleRepository->hasUsers($role));

        if ($rolesWithUsers->isNotEmpty()) {
            $names = $rolesWithUsers->pluck('name')->join(', ');

            return $this->backWithError("Không thể xóa vai trò đang có người dùng: {$names}");
        }

        DB::transaction(function () use ($roles) {
            foreach ($roles as $role) {
                $this->roleRepository->deleteRole($role);
            }
        });

        return $this->redirectWithSuccess('admin.roles.index', "Đã xóa {$roles->count()} vai trò.");
    }
}
