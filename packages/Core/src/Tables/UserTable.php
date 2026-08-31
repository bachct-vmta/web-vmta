<?php

namespace Packages\Core\Src\Tables;

use Illuminate\Database\Eloquent\Builder;
use Packages\Core\Src\Models\Role;
use Packages\Core\Src\Models\User;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;

/**
 * Reusable User Table definition
 *
 * Usage in Controller:
 * public function index(UserTable $table)
 * {
 *     return view('core::admin.users.index', ['table' => $table]);
 * }
 */
class UserTable extends BaseTable
{
    protected ?string $heading = 'Quản lý người dùng';

    protected int $perPage = 15;

    protected ?string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    /**
     * Define the model
     */
    protected function model(): string
    {
        return User::class;
    }

    /**
     * Customize base query
     */
    protected function query(Builder $query): Builder
    {
        return $query->with('role');
    }

    /**
     * Define columns
     */
    protected function columns(): array
    {
        return [
            // Avatar + Name + Email in one column
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

            // Balance with currency
            NumericColumn::make('balance')
                ->label('Số dư')
                ->money('đ')
                ->alignRight()
                ->sortable(),

            // Status with labels
            BooleanColumn::make('is_active')
                ->label('Hoạt động')
                ->labels('Hoạt động', 'Bị khóa'),

            // Relative date
            DateColumn::make('created_at')
                ->label('Ngày tạo')
                ->since()
                ->sortable(),
        ];
    }

    /**
     * Define filters
     */
    protected function filters(): array
    {
        return [
            SelectFilter::make('role_id')
                ->label('Vai trò')
                ->options(Role::pluck('name', 'id'))
                ->placeholder('Tất cả vai trò'),

            SelectFilter::make('is_active')
                ->label('Trạng thái')
                ->options([
                    '1' => 'Hoạt động',
                    '0' => 'Bị khóa',
                ])
                ->placeholder('Trạng thái'),
        ];
    }

    /**
     * Define row actions
     */
    protected function actions(): array
    {
        return [
            Action::make('edit')
                ->label('Sửa')
                ->iconEdit()
                ->route('admin.users.edit')
                ->permission('users.edit'),

            Action::make('more')
                ->label('Thêm')
                ->iconMore(),
        ];
    }

    /**
     * Define bulk actions
     */
    protected function bulkActions(): array
    {
        return [
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
                ->confirm('Bạn có chắc muốn xóa những người dùng đã chọn? Hành động này không thể hoàn tác.')
                ->permission('users.delete'),
        ];
    }
}
