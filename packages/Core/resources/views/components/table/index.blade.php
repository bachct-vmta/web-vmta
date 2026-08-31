{{-- Modern Card-based Table Component --}}
@props(['table', 'records'])

@php
    $hasFilters = count($table->getFilters()) > 0;
    $hasBulkActions = count($table->getBulkActions()) > 0;
    $hasActions = count($table->getActions()) > 0;
@endphp

<div class="core-table-wrapper" x-data="coreTableHandler()">
    {{-- Header Card with Search and Filters --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- Heading --}}
            @if($table->getHeading())
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2 whitespace-nowrap shrink-0">
                <span class="material-symbols-rounded text-primary">{{ $table->getHeadingIcon() }}</span>
                {{ $table->getHeading() }}
            </h1>
            @endif

            {{-- Search and Filters --}}
            <form method="GET" class="flex flex-wrap items-center gap-3">
                {{-- Search Input --}}
                @if($table->isSearchable())
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-rounded text-[20px]">search</span>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Tìm kiếm..."
                           class="pl-10 pr-4 py-2.5 w-full md:w-64 bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 rounded-xl focus:ring-2 focus:ring-primary transition-all text-sm dark:text-white">
                </div>
                @endif

                {{-- Filters Group --}}
                @if($hasFilters)
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 p-1 rounded-xl ring-1 ring-slate-200 dark:ring-slate-700">
                    @foreach($table->getFilters() as $index => $filter)
                        {!! $filter->render() !!}
                        @if(!$loop->last)
                        <div class="w-px h-4 bg-slate-200 dark:bg-slate-700"></div>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Filter Button --}}
                <button type="submit" class="bg-primary hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 active:scale-95">
                    <span class="material-symbols-rounded text-[20px]">filter_list</span>
                    Lọc
                </button>

                {{-- Reset Filters --}}
                @if(request()->hasAny(['search', ...array_map(fn($f) => $f->getName(), $table->getFilters())]))
                <a href="{{ url()->current() }}" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <span class="material-symbols-rounded">close</span>
                </a>
                @endif

                {{-- Theme Toggle (optional slot) --}}
                {{ $headerActions ?? '' }}
            </form>
        </div>
    </div>

    {{-- Column Headers (Desktop only) --}}
    @php
        $columnCount = count($table->getColumns());
        $gridColumns = ($hasBulkActions ? '48px ' : '') . '2fr ' . str_repeat('1fr ', $columnCount - 1) . ($hasActions ? '120px' : '');
    @endphp
    <div class="hidden md:grid gap-4 px-8 mb-4 text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 items-center"
         style="grid-template-columns: {{ $gridColumns }}">
        
        @if($hasBulkActions)
        <div class="flex items-center justify-center">
            <input type="checkbox" 
                   class="custom-checkbox w-5 h-5 rounded-full border-slate-300 text-primary focus:ring-primary cursor-pointer" 
                   id="selectAll"
                   @change="toggleAll($event)">
        </div>
        @endif

        @foreach($table->getColumns() as $column)
        <div class="{{ $column->getAlignmentClass() }}">
            @if($column->isSortable())
            <a href="{{ $table->getSortUrl($column) }}" class="inline-flex items-center gap-1 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                {{ $column->getLabel() }}
                @if($table->isColumnSorted($column))
                <span class="material-symbols-rounded text-[16px] {{ $table->getCurrentSortDirection() === 'asc' ? '' : 'rotate-180' }}">keyboard_arrow_up</span>
                @endif
            </a>
            @else
            {{ $column->getLabel() }}
            @endif
        </div>
        @endforeach

        @if($hasActions)
        <div class="text-right">Thao tác</div>
        @endif
    </div>

    {{-- Data Rows (Card-based) --}}
    <div class="space-y-4" id="userList">
        @forelse($records as $record)
        <div class="user-card bg-white dark:bg-slate-800 rounded-2xl p-4 md:px-8 md:py-5 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all group"
             :class="{'border-primary ring-1 ring-primary/20 bg-blue-50/50 dark:bg-blue-900/10': isSelected({{ $record->getKey() }})}">
            
            <div class="grid items-center gap-4"
                 style="grid-template-columns: {{ $gridColumns }}">
                
                {{-- Checkbox --}}
                @if($hasBulkActions)
                <div class="flex items-center justify-center">
                    <input type="checkbox" 
                           class="custom-checkbox user-select w-5 h-5 rounded-full border-slate-300 text-primary focus:ring-primary cursor-pointer"
                           value="{{ $record->getKey() }}"
                           @change="toggleSelect({{ $record->getKey() }})">
                </div>
                @endif

                {{-- Columns --}}
                @foreach($table->getColumns() as $index => $column)
                <div class="{{ $index > 0 ? 'hidden md:block' : '' }} {{ $column->getAlignmentClass() }} min-w-0 truncate">
                    {!! $column->render($record) !!}
                </div>
                @endforeach

                {{-- Actions --}}
                @if($hasActions)
                <div class="flex justify-end gap-2">
                    @foreach($table->getActions() as $action)
                    {!! $action->render($record) !!}
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @empty
        {{-- Empty State --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                    <span class="material-symbols-rounded text-slate-400 text-3xl">inbox</span>
                </div>
                <div>
                    <p class="text-slate-600 dark:text-slate-400 font-medium">{{ $table->getEmptyMessage() }}</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Không có dữ liệu phù hợp với bộ lọc</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination Info (always shown) --}}
    @if($records->count() > 0)
    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 px-4">
        <div class="text-sm text-slate-500 dark:text-slate-400">
            Hiển thị <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $records->firstItem() }}-{{ $records->lastItem() }}</span> 
            trên <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $records->total() }}</span> kết quả
        </div>
        
        {{-- Pagination buttons (only when multiple pages) --}}
        @if($records->hasPages())
        <div class="flex items-center gap-2">
            {{-- Previous --}}
            @if($records->onFirstPage())
            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                <span class="material-symbols-rounded">chevron_left</span>
            </span>
            @else
            <a href="{{ $records->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-rounded">chevron_left</span>
            </a>
            @endif

            {{-- Page Numbers --}}
            @foreach($records->getUrlRange(1, $records->lastPage()) as $page => $url)
                @if($page == $records->currentPage())
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold shadow-lg shadow-blue-500/30">{{ $page }}</button>
                @elseif($page <= 3 || $page == $records->lastPage() || abs($page - $records->currentPage()) <= 1)
                <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-semibold">{{ $page }}</a>
                @elseif($page == 4 && $records->currentPage() > 5)
                <div class="px-1 text-slate-400">...</div>
                @elseif($page == $records->lastPage() - 1 && $records->currentPage() < $records->lastPage() - 3)
                <div class="px-1 text-slate-400">...</div>
                @endif
            @endforeach

            {{-- Next --}}
            @if($records->hasMorePages())
            <a href="{{ $records->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-rounded">chevron_right</span>
            </a>
            @else
            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                <span class="material-symbols-rounded">chevron_right</span>
            </span>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Floating Bulk Actions Bar --}}
    @if($hasBulkActions)
    <div class="action-bar fixed bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-6 py-4 rounded-2xl shadow-2xl border border-slate-800 dark:border-slate-200 z-50 transition-all duration-300"
         x-show="selectedCount > 0"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-4 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-4 opacity-0 scale-95"
         x-cloak>
        
        <div class="flex items-center gap-3 pr-4 border-r border-slate-700 dark:border-slate-200">
            <span class="flex items-center justify-center w-6 h-6 bg-primary text-white text-[11px] font-bold rounded-full" x-text="selectedCount">0</span>
            <span class="text-sm font-medium whitespace-nowrap">Đã chọn</span>
        </div>
        
        <div class="flex items-center gap-1.5 ml-2">
            @foreach($table->getBulkActions() as $bulkAction)
            <button type="button" 
                    @click="executeBulkAction('{{ $bulkAction->getName() }}', '{{ $bulkAction->getConfirmMessage() }}')"
                    class="flex items-center gap-2 px-4 py-2 hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl transition-colors text-sm font-semibold {{ $bulkAction->isDanger() ? 'text-red-500 hover:bg-red-500/10 dark:hover:bg-red-50' : '' }}">
                @if($bulkAction->getIcon())
                <span class="material-symbols-outlined text-[20px]">{!! $bulkAction->getIcon() !!}</span>
                @endif
                {{ $bulkAction->getLabel() }}
            </button>
            @endforeach
        </div>
        
        <button class="ml-4 p-2 hover:bg-slate-800 dark:hover:bg-slate-100 rounded-lg transition-colors" @click="clearSelection()">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>
    @endif
</div>

{{-- Alpine.js Handler --}}
<script>
function coreTableHandler() {
    return {
        selected: [],
        selectedCount: 0,
        
        toggleAll(event) {
            const checkboxes = document.querySelectorAll('.user-select');
            if (event.target.checked) {
                this.selected = Array.from(checkboxes).map(cb => parseInt(cb.value));
                checkboxes.forEach(cb => cb.checked = true);
            } else {
                this.selected = [];
                checkboxes.forEach(cb => cb.checked = false);
            }
            this.selectedCount = this.selected.length;
        },
        
        toggleSelect(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(id);
            }
            this.selectedCount = this.selected.length;
            
            // Update selectAll checkbox
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-select');
            if (selectAll) {
                selectAll.checked = this.selected.length === checkboxes.length;
            }
        },
        
        isSelected(id) {
            return this.selected.includes(id);
        },
        
        clearSelection() {
            this.selected = [];
            this.selectedCount = 0;
            document.querySelectorAll('.user-select').forEach(cb => cb.checked = false);
            const selectAll = document.getElementById('selectAll');
            if (selectAll) selectAll.checked = false;
        },
        
        executeBulkAction(action, confirmMessage) {
            if (confirmMessage && !confirm(confirmMessage)) {
                return;
            }
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.pathname + '/bulk-' + action;
            
            // CSRF Token
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(csrf);
            
            // Method
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'POST';
            form.appendChild(method);
            
            // Selected IDs
            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

{{-- Component Styles --}}
<style>
[x-cloak] { display: none !important; }

.custom-checkbox {
    @apply w-5 h-5 rounded-full border-slate-300 text-primary focus:ring-primary focus:ring-offset-0 transition-all cursor-pointer bg-white dark:bg-slate-700 dark:border-slate-600;
}

.user-card:has(input[type="checkbox"]:checked) {
    @apply border-primary ring-1 ring-primary/20 bg-blue-50/50 dark:bg-blue-900/10;
}
</style>
