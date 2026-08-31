<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Http\Requests\StoreUserRequest;
use Packages\Core\Src\Http\Requests\UpdateUserRequest;
use Packages\Core\Src\Models\User;
use Packages\Core\Src\Repositories\Interfaces\RoleRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\UserRepositoryInterface;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;
use Packages\Core\Src\Traits\LogsAdminActivity;

class UserController extends BaseController
{
    use LogsAdminActivity;

    /**
     * Inject Repositories via constructor
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository
    ) {}

    /**
     * Display a listing of users using Table Builder
     */
    public function index()
    {
        $roles = $this->roleRepository->getAll();

        $table = Table::make(User::with('role'))
            ->heading('Quản lý người dùng')
            ->columns([
                // Avatar + Name + Email combined
                AvatarColumn::make('name')
                    ->label('Người dùng')
                    ->secondary('email')
                    ->searchable()
                    ->sortable(),
                // Role badge
                BadgeColumn::make('role.name')
                    ->label('Vai trò')
                    ->colors([
                        'Admin' => 'purple',
                        'Super Admin' => 'purple',
                        'Moderator' => 'blue',
                        'User' => 'gray',
                    ]),
                // Balance
                NumericColumn::make('balance')
                    ->label('Số dư')
                    ->money('đ')
                    ->alignRight()
                    ->sortable(),
                // Status
                BooleanColumn::make('is_active')
                    ->label('Hoạt động')
                    ->labels('Hoạt động', 'Bị khóa'),
                // Date
                DateColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_id')
                    ->label('Vai trò')
                    ->options($roles->pluck('name', 'id'))
                    ->placeholder('Tất cả vai trò'),
                SelectFilter::make('is_active')
                    ->label('Trạng thái')
                    ->options([
                        '1' => 'Hoạt động',
                        '0' => 'Bị khóa',
                    ])
                    ->placeholder('Tất cả'),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Sửa')
                    ->iconEdit()
                    ->route('admin.users.edit')
                    ->permission('users.edit'),
                Action::make('more')
                    ->label('Thêm')
                    ->iconMore(),
            ])
            ->bulkActions([
                BulkAction::make('change-role')
                    ->label('Đổi vai trò')
                    ->icon('manage_accounts')
                    ->permission('users.edit'),
                BulkAction::make('lock')
                    ->label('Khóa')
                    ->icon('lock_reset')
                    ->color('warning')
                    ->confirm('Bạn có chắc muốn khóa những người dùng đã chọn?')
                    ->permission('users.edit'),
                BulkAction::make('delete')
                    ->label('Xóa')
                    ->icon('delete_sweep')
                    ->danger()
                    ->confirm('Bạn có chắc muốn xóa những người dùng đã chọn?')
                    ->permission('users.delete'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginate(15);

        return view('core::admin.users.index', compact('table', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = $this->roleRepository->getAll();

        return view('core::admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['balance'] = $validated['balance'] ?? 0;

        DB::transaction(function () use ($validated) {
            $this->userRepository->createUser($validated);
        });

        return $this->redirectWithSuccess('admin.users.index', 'Tạo người dùng thành công.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load('role');

        return view('core::admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = $this->roleRepository->getAll();

        return view('core::admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($user, $validated) {
            $this->userRepository->updateUser($user, $validated);
        });

        return $this->redirectWithSuccess('admin.users.index', 'Cập nhật người dùng thành công.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return $this->backWithError('Không thể xóa tài khoản đang đăng nhập.');
        }

        $this->userRepository->deleteUser($user);

        return $this->redirectWithSuccess('admin.users.index', 'Xóa người dùng thành công.');
    }

    /**
     * Bulk delete selected users
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $ids = collect($validated['ids'])->reject(fn ($id) => (int) $id === $currentUserId);

        if ($ids->isEmpty()) {
            return $this->backWithError('Không thể xóa tài khoản đang đăng nhập.');
        }

        DB::transaction(function () use ($ids) {
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $this->userRepository->deleteUser($user);
            }
        });

        return $this->redirectWithSuccess('admin.users.index', "Đã xóa {$ids->count()} người dùng.");
    }

    /**
     * Bulk lock/unlock selected users
     */
    public function bulkLock(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $ids = collect($validated['ids'])->reject(fn ($id) => (int) $id === $currentUserId);

        DB::transaction(function () use ($ids) {
            User::whereIn('id', $ids)->update(['is_active' => false]);
        });

        return $this->redirectWithSuccess('admin.users.index', "Đã khóa {$ids->count()} người dùng.");
    }

    /**
     * Bulk change role for selected users
     */
    public function bulkChangeRole(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::transaction(function () use ($validated) {
            User::whereIn('id', $validated['ids'])->update(['role_id' => $validated['role_id']]);
        });

        return $this->redirectWithSuccess('admin.users.index', 'Đã đổi vai trò cho '.count($validated['ids']).' người dùng.');
    }
}
