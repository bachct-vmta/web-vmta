@extends('core::layouts.admin')

@php
    $tr = $lead->specialty?->translate($lead->locale ?: 'vi')
        ?? $lead->specialty?->translations->first();
@endphp

@section('title', __('catalog::catalog.specialty_lead.detail').' #'.$lead->id)
@section('page-title', __('catalog::catalog.specialty_lead.detail').' #'.$lead->id)

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route(admin_route_name('specialty_leads.index')) }}"
           class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            {{ __('catalog::catalog.specialty_lead.index') }}
        </a>
        <span class="text-xs rounded-full px-3 py-1 {{ $lead->status->badgeClasses() }}">
            {{ $lead->status->label() }}
        </span>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
            <span class="material-symbols-rounded text-base">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Card: Contact info --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-rounded text-primary text-xl">person</span>
            {{ __('catalog::catalog.fields.name') }}
        </h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.fields.name') }}</dt>
                <dd class="font-medium text-slate-800">{{ $lead->name }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.fields.phone') }}</dt>
                <dd class="font-mono text-slate-800">
                    <a href="tel:{{ $lead->phone }}" class="hover:text-primary">{{ $lead->phone }}</a>
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.fields.email') }}</dt>
                <dd class="text-slate-800">
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="hover:text-primary">{{ $lead->email }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.specialty_lead.fields.specialty') }}</dt>
                <dd class="text-slate-800">{{ $tr?->name ?? '#'.$lead->specialty_id }}</dd>
            </div>
        </dl>
    </div>

    {{-- Card: Demand + Message --}}
    @if($lead->demand || $lead->message)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-rounded text-primary text-xl">chat</span>
            {{ __('catalog::catalog.specialty_lead.detail') }}
        </h2>
        <div class="space-y-4 text-sm">
            @if($lead->demand)
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Demand</div>
                <div class="rounded-lg bg-slate-50 p-3 text-slate-800">{{ $lead->demand }}</div>
            </div>
            @endif
            @if($lead->message)
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Message</div>
                <div class="rounded-lg bg-slate-50 p-3 text-slate-800 whitespace-pre-line">{{ $lead->message }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Card: Status update --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-rounded text-primary text-xl">flag</span>
            {{ __('catalog::catalog.fields.status') }}
        </h2>
        <form method="POST" action="{{ route(admin_route_name('specialty_leads.update_status'), $lead->id) }}"
              class="flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <label class="block text-xs uppercase tracking-wide text-slate-400 mb-1">{{ __('catalog::catalog.fields.status') }}</label>
                <select name="status"
                        class="w-full rounded-lg border-slate-200 bg-white text-slate-700 focus:border-primary focus:ring-primary text-sm">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($lead->status->value === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                <span class="material-symbols-rounded text-lg">save</span>
                {{ __('catalog::catalog.actions.save') }}
            </button>
        </form>
    </div>

    {{-- Card: Meta --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-rounded text-primary text-xl">info</span>
            Meta
        </h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.specialty_lead.fields.created_at') }}</dt>
                <dd class="text-slate-800">{{ optional($lead->created_at)->format('Y-m-d H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.specialty_lead.fields.locale') }}</dt>
                <dd class="font-mono text-slate-800">{{ $lead->locale ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.specialty_lead.fields.ip_address') }}</dt>
                <dd class="font-mono text-slate-800">{{ $lead->ip_address ?: '—' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('catalog::catalog.specialty_lead.fields.user_agent') }}</dt>
                <dd class="font-mono text-xs text-slate-500 break-all">{{ $lead->user_agent ?: '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
