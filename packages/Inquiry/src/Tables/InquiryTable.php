<?php

namespace Packages\Inquiry\Src\Tables;

use Illuminate\Database\Eloquent\Builder;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\BaseTable;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Columns\TextColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Models\Inquiry;

class InquiryTable extends BaseTable
{
    protected int $perPage = 20;

    protected ?string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected string $headingIcon = 'forward_to_inbox';

    public function __construct()
    {
        // Heading translated lazily so locale is honoured per request.
        $this->heading = __('inquiry::inquiry.admin.index_heading');
        $this->emptyMessage = __('inquiry::inquiry.admin.list_empty');
    }

    public function getTable(): \Packages\Core\Src\Tables\Table
    {
        return parent::getTable()->searchable(false);
    }

    protected function model(): string
    {
        return Inquiry::class;
    }

    /**
     * Eager-load relations, restrict to coordinator's inbox when `mine=1`,
     * and surface urgent leads first. Default ordering replaces BaseTable's
     * created_at desc to honour the `urgentFirst()` scope.
     */
    protected function query(Builder $query): Builder
    {
        $query->with(['assignee', 'sourceRef']);

        $request = request();
        $user = $request->user();
        $hasMineParam = $request->has('mine');
        $isCoordinator = $user
            && $user->hasPermission('inquiry.index')
            && ! $user->hasPermission('inquiry.assign');

        $applyMineScope = $user && (
            $request->boolean('mine')
            || ($isCoordinator && ! $hasMineParam)
        );

        if ($applyMineScope) {
            $query->forCoordinator($user->id);
        }

        if (! request()->filled('sort')) {
            $query->urgentFirst();
        }

        return $query;
    }

    protected function columns(): array
    {
        return [
            AvatarColumn::make('name')
                ->label(__('inquiry::inquiry.field_name'))
                ->secondary('email')
                ->searchable()
                ->sortable(),

            TextColumn::make('phone')
                ->label(__('inquiry::inquiry.field_phone'))
                ->searchable()
                ->default('—'),

            BadgeColumn::make('source')
                ->label(__('inquiry::inquiry.field_source'))
                ->colors([
                    InquirySource::ContactForm->value => 'info',
                    InquirySource::PartnerInquiry->value => 'purple',
                    InquirySource::Emergency->value => 'danger',
                    InquirySource::CatalogQuick->value => 'indigo',
                    InquirySource::ChatbotOverflow->value => 'pink',
                ])
                ->formatStateUsing(fn ($value) => $value
                    ? __('inquiry::inquiry.source.'.$value->value)
                    : ''),

            BadgeColumn::make('status')
                ->label(__('inquiry::inquiry.admin.list_status'))
                ->colors([
                    InquiryStatus::New->value => 'info',
                    InquiryStatus::Contacted->value => 'warning',
                    InquiryStatus::Qualified->value => 'purple',
                    InquiryStatus::Closed->value => 'success',
                    InquiryStatus::Cancelled->value => 'gray',
                ])
                ->formatStateUsing(fn ($value) => $value
                    ? __('inquiry::inquiry.status.'.$value->value)
                    : ''),

            DateColumn::make('created_at')
                ->label(__('inquiry::inquiry.admin.list_created'))
                ->since()
                ->sortable(),
        ];
    }

    protected function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label(__('inquiry::inquiry.admin.list_status'))
                ->options($this->enumOptions(InquiryStatus::cases(), 'status'))
                ->placeholder(__('inquiry::inquiry.admin.filter_all_statuses')),

            SelectFilter::make('source')
                ->label(__('inquiry::inquiry.field_source'))
                ->options($this->enumOptions(InquirySource::cases(), 'source'))
                ->placeholder(__('inquiry::inquiry.admin.filter_all_sources')),
        ];
    }

    protected function actions(): array
    {
        return [
            Action::make('view')
                ->label(__('inquiry::inquiry.admin.detail_heading'))
                ->iconView()
                ->route(admin_route_name('inquiries.show'))
                ->permission('inquiry.view'),

            Action::make('delete')
                ->label(__('inquiry::inquiry.admin.soft_delete_button'))
                ->iconDelete()
                ->route(admin_route_name('inquiries.destroy'))
                ->method('DELETE')
                ->danger()
                ->confirm(__('inquiry::inquiry.admin.soft_delete_confirm'))
                ->permission('inquiry.delete'),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label(__('inquiry::inquiry.admin.bulk_delete_label'))
                ->icon('delete_sweep')
                ->danger()
                ->confirm(__('inquiry::inquiry.admin.bulk_delete_confirm'))
                ->permission('inquiry.delete'),
        ];
    }

    /**
     * @param  array<int, \BackedEnum>  $cases
     * @return array<string, string>
     */
    private function enumOptions(array $cases, string $langGroup): array
    {
        $options = [];
        foreach ($cases as $case) {
            $options[$case->value] = __('inquiry::inquiry.'.$langGroup.'.'.$case->value);
        }

        return $options;
    }
}
