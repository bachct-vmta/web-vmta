@extends('core::layouts.admin')

@section('title', __('inquiry::inquiry.admin.detail_heading') . ' #' . $inquiry->id)

@section('content')
<div class="p-6">
    <div class="mb-6">
        <a href="{{ route(admin_route_name('inquiries.index')) }}" class="text-sm text-blue-600 hover:underline">← {{ __('inquiry::inquiry.admin.index_heading') }}</a>
        <h1 class="text-2xl font-semibold mt-2">{{ __('inquiry::inquiry.admin.detail_heading') }} #{{ $inquiry->id }}</h1>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 p-3 text-green-800">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Inquiry details --}}
        <div class="lg:col-span-2 rounded border border-slate-200 bg-white p-6">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_name') }}</dt>
                <dd>{{ $inquiry->name }}</dd>

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_phone') }}</dt>
                <dd><a href="tel:{{ $inquiry->phone }}" class="text-blue-600">{{ $inquiry->phone }}</a></dd>

                @if($inquiry->email)
                    <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_email') }}</dt>
                    <dd>{{ $inquiry->email }}</dd>
                @endif

                @if($inquiry->industry)
                    <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_industry') }}</dt>
                    <dd>{{ $inquiry->industry }}</dd>
                @endif

                @if($inquiry->company_name)
                    <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_company_name') }}</dt>
                    <dd>{{ $inquiry->company_name }}</dd>
                @endif

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_priority') }}</dt>
                <dd>
                    <span class="inline-block text-xs px-2 py-0.5 rounded {{ $inquiry->priority->value === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ __('inquiry::inquiry.priority.'.$inquiry->priority->value) }}
                    </span>
                </dd>

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_source') }}</dt>
                <dd>{{ __('inquiry::inquiry.source.'.$inquiry->source->value) }}</dd>

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.admin.list_status') }}</dt>
                <dd>{{ __('inquiry::inquiry.status.'.$inquiry->status->value) }}</dd>

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_locale') }}</dt>
                <dd>{{ $inquiry->locale }}</dd>

                @if($inquiry->source_ref_type)
                    <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.field_ref') }}</dt>
                    <dd class="text-slate-600">{{ $inquiry->source_ref_type }}#{{ $inquiry->source_ref_id }}</dd>
                @endif

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.admin.list_assignee') }}</dt>
                <dd>{{ $inquiry->assignee?->name ?? __('inquiry::inquiry.admin.unassigned') }}</dd>

                <dt class="font-medium text-slate-500">{{ __('inquiry::inquiry.admin.list_created') }}</dt>
                <dd>{{ $inquiry->created_at?->format('Y-m-d H:i') }}</dd>
            </dl>

            @if($inquiry->message)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium mb-2">{{ __('inquiry::inquiry.field_message') }}</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $inquiry->message }}</p>
                </div>
            @endif

            {{-- Timeline (newest-first) --}}
            <div class="mt-6 pt-6 border-t">
                <h3 class="font-medium mb-3">{{ __('inquiry::inquiry.admin.timeline') }}</h3>
                <ul class="space-y-3">
                    @forelse($inquiry->notes as $note)
                        <li class="text-sm">
                            <div class="flex items-baseline gap-2">
                                <span class="font-medium">{{ $note->user?->name ?? __('inquiry::inquiry.admin.deleted_user') }}</span>
                                <span class="text-xs text-slate-400">{{ $note->created_at?->diffForHumans() }}</span>
                                @if($note->status_from && $note->status_to)
                                    <span class="text-xs text-slate-500">
                                        {{ __('inquiry::inquiry.status.'.$note->status_from) }} → {{ __('inquiry::inquiry.status.'.$note->status_to) }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-slate-700 mt-1 whitespace-pre-line">{{ $note->body }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">—</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Action sidebar --}}
        <div class="space-y-4">
            {{-- Transition status --}}
            @if(count($allowedNext) > 0 && auth()->user()->hasPermission('inquiry.update'))
                <div class="rounded border border-slate-200 bg-white p-4">
                    <h3 class="font-medium mb-2">{{ __('inquiry::inquiry.admin.change_status') }}</h3>
                    <form method="POST" action="{{ route(admin_route_name('inquiries.transition'), $inquiry->id) }}" class="space-y-2">
                        @csrf
                        <select name="status" class="w-full rounded border-slate-300 text-sm">
                            @foreach($allowedNext as $status)
                                <option value="{{ $status->value }}">{{ __('inquiry::inquiry.status.'.$status->value) }}</option>
                            @endforeach
                        </select>
                        <textarea name="note" rows="2" maxlength="2000" placeholder="{{ __('inquiry::inquiry.admin.optional_note') }}" class="w-full rounded border-slate-300 text-sm"></textarea>
                        <button class="w-full rounded bg-blue-600 text-white px-3 py-1 text-sm">{{ __('inquiry::inquiry.submit') }}</button>
                    </form>
                </div>
            @endif

            {{-- Assign coordinator --}}
            @if(auth()->user()->hasPermission('inquiry.assign') && $coordinators->isNotEmpty())
                <div class="rounded border border-slate-200 bg-white p-4">
                    <h3 class="font-medium mb-2">{{ __('inquiry::inquiry.admin.assign_to') }}</h3>
                    <form method="POST" action="{{ route(admin_route_name('inquiries.assign'), $inquiry->id) }}" class="space-y-2">
                        @csrf
                        <select name="assigned_to" class="w-full rounded border-slate-300 text-sm">
                            @foreach($coordinators as $coord)
                                <option value="{{ $coord->id }}" @selected($inquiry->assigned_to === $coord->id)>
                                    {{ $coord->name }} ({{ $coord->email }})
                                </option>
                            @endforeach
                        </select>
                        <button class="w-full rounded bg-emerald-600 text-white px-3 py-1 text-sm">{{ __('inquiry::inquiry.submit') }}</button>
                    </form>
                </div>
            @endif

            {{-- Free-form note --}}
            @if(auth()->user()->hasPermission('inquiry.update'))
                <div class="rounded border border-slate-200 bg-white p-4">
                    <h3 class="font-medium mb-2">{{ __('inquiry::inquiry.admin.add_note') }}</h3>
                    <form method="POST" action="{{ route(admin_route_name('inquiries.note'), $inquiry->id) }}" class="space-y-2">
                        @csrf
                        <textarea name="body" rows="3" required minlength="1" maxlength="5000" class="w-full rounded border-slate-300 text-sm"></textarea>
                        <button class="w-full rounded bg-slate-700 text-white px-3 py-1 text-sm">{{ __('inquiry::inquiry.submit') }}</button>
                    </form>
                </div>
            @endif

            {{-- Delete --}}
            @if(auth()->user()->hasPermission('inquiry.delete'))
                <div class="rounded border border-red-200 bg-red-50 p-4">
                    <form method="POST" action="{{ route(admin_route_name('inquiries.destroy'), $inquiry->id) }}" onsubmit="return confirm('{{ __('inquiry::inquiry.admin.soft_delete_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="w-full rounded bg-red-600 text-white px-3 py-1 text-sm">{{ __('inquiry::inquiry.admin.soft_delete_button') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
