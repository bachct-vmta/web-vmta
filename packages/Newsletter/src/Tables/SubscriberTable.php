<?php

namespace Packages\Newsletter\Src\Tables;

use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\BaseTable;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Columns\TextColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Newsletter\Src\Enums\SubscriberStatus;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;

class SubscriberTable extends BaseTable
{
    protected ?string $heading = 'Người đăng ký nhận tin';

    protected int $perPage = 25;

    protected ?string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected function model(): string
    {
        return NewsletterSubscriber::class;
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('email')->label('Email')->searchable()->sortable(),

            BadgeColumn::make('status')
                ->label('Trạng thái')
                ->colors([
                    SubscriberStatus::Pending->value => 'amber',
                    SubscriberStatus::Confirmed->value => 'green',
                    SubscriberStatus::Unsubscribed->value => 'gray',
                ]),

            TextColumn::make('locale')->label('Ngôn ngữ')->sortable(),

            DateColumn::make('confirmed_at')->label('Xác nhận lúc')->since(),

            DateColumn::make('created_at')->label('Đăng ký lúc')->since()->sortable(),
        ];
    }

    protected function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Trạng thái')
                ->options([
                    SubscriberStatus::Pending->value => 'Chờ xác nhận',
                    SubscriberStatus::Confirmed->value => 'Đã xác nhận',
                    SubscriberStatus::Unsubscribed->value => 'Đã hủy',
                ])
                ->placeholder('Tất cả'),
        ];
    }

    protected function actions(): array
    {
        return [
            Action::make('delete')
                ->label('Xóa')
                ->iconDelete()
                ->permission('newsletter.delete'),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Xóa')
                ->icon('delete_sweep')
                ->danger()
                ->confirm('Xác nhận xóa các email đã chọn?')
                ->permission('newsletter.delete'),
        ];
    }
}
