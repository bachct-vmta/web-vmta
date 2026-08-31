@extends('core::layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $widgets = app(\Packages\Core\Src\Services\WidgetService::class)->getVisibleWidgets();

    // Series JSON for Chart.js — labels + data arrays.
    $pageviewLabels = $pageview_series->keys()->all();
    $pageviewData = $pageview_series->values()->all();
    $leadLabels = $lead_series->keys()->all();
    $leadData = $lead_series->values()->all();
    $chatbotLabels = $chatbot_series->keys()->all();
    $chatbotData = $chatbot_series->values()->all();

    $leadBreakdownLabels = $lead_breakdown->keys()->all();
    $leadBreakdownData = $lead_breakdown->values()->all();
@endphp

<div class="flex flex-col md:flex-row md:items-end md:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-text-main dark:text-white tracking-tight">Tổng quan</h2>
        <p class="text-text-muted dark:text-slate-400 text-sm mt-1">
            Xin chào {{ auth()->user()->name }} — số liệu cập nhật mỗi phút.
        </p>
    </div>
    <a href="{{ route('admin.dashboard', ['refresh' => 1]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 text-sm">
        <span class="material-symbols-rounded text-base">refresh</span>
        Làm mới
    </a>
</div>

{{-- Metric cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @foreach([
        ['label' => 'Pageview hôm nay', 'value' => $pageview_today, 'icon' => 'visibility', 'bg' => 'bg-blue-50', 'fg' => 'text-blue-600'],
        ['label' => 'Lead 7 ngày', 'value' => $lead_7d, 'icon' => 'inbox', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-600'],
        ['label' => 'Chatbot sessions 7 ngày', 'value' => $chatbot_sessions_7d, 'icon' => 'chat', 'bg' => 'bg-purple-50', 'fg' => 'text-purple-600'],
        ['label' => 'Chatbot messages 7 ngày', 'value' => $chatbot_messages_7d, 'icon' => 'forum', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-600'],
    ] as $card)
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-text-muted dark:text-slate-400 text-sm font-medium">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-text-main dark:text-white mt-1">{{ number_format($card['value']) }}</p>
            </div>
            <div class="p-2.5 {{ $card['bg'] }} rounded-lg">
                <span class="material-symbols-rounded {{ $card['fg'] }}">{{ $card['icon'] }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts row 1 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700">
        <h3 class="font-semibold mb-3">Pageview — 30 ngày gần nhất</h3>
        <div class="relative h-64"><canvas id="chart-pageview"></canvas></div>
    </div>
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700">
        <h3 class="font-semibold mb-3">Lead — 30 ngày gần nhất</h3>
        <div class="relative h-64"><canvas id="chart-lead"></canvas></div>
    </div>
</div>

{{-- Charts row 2 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700">
        <h3 class="font-semibold mb-3">Chatbot messages — 30 ngày gần nhất</h3>
        <div class="relative h-64"><canvas id="chart-chatbot"></canvas></div>
    </div>
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700">
        <h3 class="font-semibold mb-3">Phân bổ lead theo nguồn — 30 ngày</h3>
        @if(count($leadBreakdownData) > 0)
            <div class="relative h-64"><canvas id="chart-lead-breakdown"></canvas></div>
        @else
            <p class="text-text-muted dark:text-slate-400 text-sm">Chưa có dữ liệu lead.</p>
        @endif
    </div>
</div>

{{-- Top pages --}}
<div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700 mb-6">
    <h3 class="font-semibold mb-3">Top 10 trang theo pageview (30 ngày)</h3>
    @if($top_pages->isEmpty())
        <p class="text-text-muted dark:text-slate-400 text-sm">Chưa có dữ liệu.</p>
    @else
        <table class="min-w-full text-sm">
            <thead class="text-left text-text-muted">
                <tr>
                    <th class="py-2">Metric key</th>
                    <th class="py-2 text-right">Pageview</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_pages as $key => $count)
                    <tr class="border-t border-gray-100 dark:border-slate-700">
                        <td class="py-2 font-mono text-xs">{{ $key }}</td>
                        <td class="py-2 text-right font-semibold">{{ number_format($count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<script type="application/json" id="report-chart-data">
{!! json_encode([
    'pageview' => ['labels' => $pageviewLabels, 'data' => $pageviewData],
    'lead' => ['labels' => $leadLabels, 'data' => $leadData],
    'chatbot' => ['labels' => $chatbotLabels, 'data' => $chatbotData],
    'leadBreakdown' => ['labels' => $leadBreakdownLabels, 'data' => $leadBreakdownData],
], JSON_UNESCAPED_UNICODE) !!}
</script>

@vite('packages/Report/resources/js/charts.js')
@endsection
