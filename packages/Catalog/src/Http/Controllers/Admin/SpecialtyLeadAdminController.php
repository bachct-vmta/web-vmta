<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Packages\Catalog\Src\Enums\SpecialtyLeadStatus;
use Packages\Catalog\Src\Models\SpecialtyLead;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Columns\TextColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class SpecialtyLeadAdminController extends BaseController
{
    public function index(): View
    {
        // Build specialty options for the filter — keyed by id, labelled with
        // the locale-appropriate translation.
        $specialtyOptions = Specialty::with('translations')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($s) => [
                $s->id => $s->translations->firstWhere('locale', app()->getLocale())?->name
                    ?? $s->translations->first()?->name
                    ?? '#'.$s->id,
            ])
            ->toArray();

        $table = Table::make(SpecialtyLead::with('specialty.translations'))
            ->heading(__('catalog::catalog.specialty_lead.index'))
            ->searchable(true, ['name', 'phone', 'email'])
            ->columns([
                AvatarColumn::make('name')
                    ->label(__('catalog::catalog.fields.name'))
                    ->secondary('phone'),
                TextColumn::make('email')
                    ->label(__('catalog::catalog.fields.email'))
                    ->formatStateUsing(fn ($v) => $v ?: '—'),
                TextColumn::make('specialty')
                    ->label(__('catalog::catalog.specialty_lead.fields.specialty'))
                    ->formatStateUsing(function ($v, $record) {
                        $tr = $record->specialty?->translate($record->locale ?: 'vi')
                            ?? $record->specialty?->translations->first();

                        return $tr?->name ?? '#'.$record->specialty_id;
                    }),
                BadgeColumn::make('status')
                    ->label(__('catalog::catalog.fields.status'))
                    ->colors([
                        'new' => 'blue',
                        'contacted' => 'amber',
                        'closed' => 'gray',
                    ])
                    ->formatStateUsing(fn ($v) => $v
                        ? (SpecialtyLeadStatus::tryFrom($v instanceof \BackedEnum ? $v->value : $v)?->label() ?? $v)
                        : ''),
                DateColumn::make('created_at')
                    ->label(__('catalog::catalog.specialty_lead.fields.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('catalog::catalog.fields.status'))
                    ->options(SpecialtyLeadStatus::options())
                    ->placeholder(__('catalog::catalog.specialty_lead.filters.all_statuses')),
                SelectFilter::make('specialty_id')
                    ->label(__('catalog::catalog.specialty_lead.fields.specialty'))
                    ->options($specialtyOptions)
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('catalog::catalog.specialty_lead.detail'))
                    ->icon('visibility')
                    ->route(admin_route_name('specialty_leads.show'))
                    ->permission('catalog.index'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginate(20);

        return view('catalog::admin.specialty-leads.index', compact('table'));
    }

    public function show(int $lead): View
    {
        $model = SpecialtyLead::with('specialty.translations')->findOrFail($lead);

        return view('catalog::admin.specialty-leads.show', [
            'lead' => $model,
            'statusOptions' => SpecialtyLeadStatus::options(),
        ]);
    }

    public function updateStatus(Request $request, int $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_column(SpecialtyLeadStatus::cases(), 'value'))],
        ]);

        $model = SpecialtyLead::findOrFail($lead);
        $model->update(['status' => $validated['status']]);

        return $this->redirectWithSuccess(
            admin_route_name('specialty_leads.show'),
            __('catalog::catalog.specialty_lead.updated'),
            ['lead' => $model->id],
        );
    }
}
