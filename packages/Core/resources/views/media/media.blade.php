@extends(($popup ?? false) ? 'core-media::master' : 'core::layouts.admin')

@section('title', trans('core-media::media.title', [], 'Media Library'))

@section('header-action')
    {{-- Upload Dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-lg shadow-primary/20 transition-all active:scale-95">
            <span class="material-symbols-rounded text-[18px]">cloud_upload</span>
            <span>{{ trans('core-media::media.upload') }}</span>
            <span class="material-symbols-rounded text-[16px]">arrow_drop_down</span>
        </button>
        
        <div x-show="open" @click.away="open = false" x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-gray-100 dark:border-slate-700 py-1 z-50">
            <button @click="open = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-button-upload">
                <span class="material-symbols-rounded text-primary">upload_file</span>
                {{ trans('core-media::media.upload_from_local') }}
            </button>
            <button @click="open = false; openModal('modal-upload-url')" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                <span class="material-symbols-rounded text-primary">link</span>
                {{ trans('core-media::media.upload_from_url') }}
            </button>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    {{-- GLightbox for file preview --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/css/glightbox.min.css">
    {{-- jQuery contextMenu for right-click menu --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-contextmenu@2.9.2/dist/jquery.contextMenu.min.css">
    <style>
        /* Media Manager specific styles */
        .media-grid-container { height: calc(100vh - 220px); }
        
        /* Loading spinner */
        #media-loading {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        .dark #media-loading { background: rgba(0,0,0,0.8); }
        #media-loading.active { display: flex; }
        #media-loading::after {
            content: "";
            width: 40px;
            height: 40px;
            border: 3px solid;
            border-color: #464ee7 transparent #464ee7 transparent;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { 
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Selected state */
        .media-item.selected {
            @apply ring-2 ring-primary ring-offset-2 dark:ring-offset-slate-900;
        }
        .media-item.selected .item-check {
            @apply block;
        }
        
        /* Thumbnail image styling */
        .media-thumbnail-img {
            @apply absolute inset-0 w-full h-full object-cover transition-transform duration-500;
        }
        .group:hover .media-thumbnail-img {
            @apply scale-105;
        }
        
        /* File type icons */
        .file-icon-pdf { @apply text-red-500; }
        .file-icon-doc { @apply text-blue-500; }
        .file-icon-xls { @apply text-green-500; }
        .file-icon-video { @apply text-purple-500; }
        .file-icon-default { @apply text-slate-400; }
        
        /* Drag and drop highlight */
        .media-grid.drag-over {
            @apply bg-primary/5 border-2 border-dashed border-primary/50 rounded-xl;
        }
    </style>
@endpush

@section('content')
<div id="media-container"
     class="relative {{ ($popup ?? false) ? '' : '-m-6 lg:-m-8' }}"
     data-breadcrumbs-count="1"
     data-upload="{{ route('admin.media.file.uploadFile') }}"
     data-ajax="{{ route('admin.media.loadMedia') }}"
     x-data="{ showDetail: false, selectedItem: null }">
    
    <input type="hidden" name="nkd-csrf-token" value="{{ csrf_token() }}">
    
    {{-- Loading Overlay --}}
    <div id="media-loading"></div>
    
    {{-- Workspace (Filters + Grid + Detail Panel) --}}
    <div class="flex {{ ($popup ?? false) ? 'h-screen' : 'h-[calc(100vh-80px)]' }}">
        
        {{-- Center Canvas --}}
        <div class="flex-1 flex flex-col min-w-0 bg-background-light dark:bg-background-dark relative overflow-hidden">
            
            {{-- Filter Bar --}}
            <div class="px-6 lg:px-8 py-4 flex flex-wrap gap-4 justify-between items-center shrink-0 border-b border-gray-100 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
                {{-- Filter Tabs --}}
                <div class="flex gap-2 p-1 bg-gray-100 dark:bg-slate-800 rounded-xl">
                    <button class="px-4 py-1.5 rounded-lg text-sm font-medium bg-white dark:bg-slate-700 text-primary shadow-sm transition-colors js-media-change-filter active" data-type="filter" data-value="everything">
                        {{ trans('core-media::media.everything') }}
                    </button>
                    <button class="px-4 py-1.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50 transition-colors js-media-change-filter" data-type="filter" data-value="image">
                        {{ trans('core-media::media.image') }}
                    </button>
                    <button class="px-4 py-1.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50 transition-colors js-media-change-filter" data-type="filter" data-value="video">
                        {{ trans('core-media::media.video') }}
                    </button>
                    <button class="px-4 py-1.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50 transition-colors js-media-change-filter" data-type="filter" data-value="document">
                        {{ trans('core-media::media.document') }}
                    </button>
                </div>
                
                {{-- Search Input --}}
                <div class="flex-1 max-w-xs mx-4">
                    <div class="relative group">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg">search</span>
                        <input type="text" 
                               name="search"
                               class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/50 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 transition-all js-search-input" 
                               placeholder="{{ trans('core-media::media.search_file_and_folder') }}">
                    </div>
                </div>
                
                {{-- Right Actions --}}
                <div class="flex items-center gap-4">
                    {{-- View Toggle (All/Trash) --}}
                    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-slate-800 rounded-lg">
                        <button class="px-3 py-1 rounded text-xs font-medium bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-sm js-media-change-filter active" data-type="view_in" data-value="all_media">
                            <span class="material-symbols-rounded text-[14px] align-middle mr-1">folder</span>
                            {{ trans('core-media::media.all_media') }}
                        </button>
                        <button class="px-3 py-1 rounded text-xs font-medium text-slate-500 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-600/50 js-media-change-filter" data-type="view_in" data-value="trash">
                            <span class="material-symbols-rounded text-[14px] align-middle mr-1">delete</span>
                            {{ trans('core-media::media.trash') }}
                        </button>
                    </div>
                    
                    {{-- Sort Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-primary transition-colors">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider mr-1">{{ trans('core-media::media.sort') }}:</span>
                            <span class="js-sort-label">{{ trans('core-media::media.file_name_desc') }}</span>
                            <span class="material-symbols-rounded text-base">arrow_drop_down</span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-gray-100 dark:border-slate-700 py-1 z-50">
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter" data-type="sort_by" data-value="name-asc">{{ trans('core-media::media.file_name_asc') }}</button>
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter active" data-type="sort_by" data-value="name-desc">{{ trans('core-media::media.file_name_desc') }}</button>
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter" data-type="sort_by" data-value="created_at-asc">{{ trans('core-media::media.uploaded_date_asc') }}</button>
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter" data-type="sort_by" data-value="created_at-desc">{{ trans('core-media::media.uploaded_date_desc') }}</button>
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter" data-type="sort_by" data-value="size-asc">{{ trans('core-media::media.size_asc') }}</button>
                            <button @click="open = false" class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 js-media-change-filter" data-type="sort_by" data-value="size-desc">{{ trans('core-media::media.size_desc') }}</button>
                        </div>
                    </div>
                    
                    {{-- Create Folder --}}
                    <button class="flex items-center justify-center size-9 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors js-create-folder-action" title="{{ trans('core-media::media.create_folder') }}">
                        <span class="material-symbols-rounded">create_new_folder</span>
                    </button>
                    
                    {{-- Refresh --}}
                    <button class="flex items-center justify-center size-9 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors js-change-action" data-type="refresh" title="Refresh">
                        <span class="material-symbols-rounded">refresh</span>
                    </button>
                </div>
            </div>
            
            {{-- Breadcrumbs --}}
            <div class="px-6 lg:px-8 py-3 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 bg-surface-light dark:bg-surface-dark border-b border-gray-100 dark:border-slate-700">
                <a href="#" data-folder="0" class="hover:text-primary transition-colors flex items-center gap-1 js-change-folder">
                    <span class="material-symbols-rounded text-[18px]">home</span>
                    {{ trans('core-media::media.all_media') }}
                </a>
                <span class="js-breadcrumb-items"></span>
            </div>
            
            {{-- Scrollable Grid Area --}}
            <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 media-grid">
                    {{-- Upload Card (always first) --}}
                    <div class="aspect-[4/5] rounded-xl border-2 border-dashed border-primary/30 bg-primary/5 hover:bg-primary/10 flex flex-col items-center justify-center gap-3 cursor-pointer transition-colors group relative overflow-hidden js-button-upload">
                        <div class="size-12 rounded-full bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300">
                            <span class="material-symbols-rounded text-2xl">cloud_upload</span>
                        </div>
                        <div class="text-center px-4 z-10">
                            <p class="text-sm font-semibold text-primary mb-1">{{ trans('core-media::media.upload') }}</p>
                            <p class="text-xs text-slate-500">Drag & drop</p>
                        </div>
                    </div>
                    
                    {{-- Media items will be injected here by JS --}}
                </div>
                
                {{-- Pagination Info --}}
                <div class="flex justify-center py-6">
                    <p class="text-xs text-slate-400 js-media-count"></p>
                </div>
            </div>
        </div>
        
        {{-- Detail Panel --}}
        <aside id="detail-panel" 
               x-show="showDetail" 
               x-cloak
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="translate-x-full opacity-0"
               x-transition:enter-end="translate-x-0 opacity-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0 opacity-100"
               x-transition:leave-end="translate-x-full opacity-0"
               class="w-[360px] bg-surface-light dark:bg-surface-dark border-l border-gray-100 dark:border-slate-700 flex flex-col shrink-0 shadow-xl z-20 overflow-y-auto">
            <div class="p-6 pb-4 border-b border-gray-100 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Asset Details</h2>
                    <button @click="showDetail = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors js-close-detail">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                
                {{-- Preview --}}
                <div class="aspect-video w-full rounded-lg overflow-hidden bg-gray-100 dark:bg-slate-800 relative shadow-inner border border-gray-200 dark:border-slate-700">
                    <div class="w-full h-full flex items-center justify-center js-detail-preview">
                        <span class="material-symbols-rounded text-4xl text-slate-300">image</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-6 js-detail-content">
                {{-- Content injected by JS --}}
            </div>
        </aside>
    </div>
    
    {{-- Hidden File Input --}}
    <input type="file" multiple class="hidden" id="media-file-input">
</div>

{{-- Modals --}}
@include('core-media::components.modals')

{{-- Item Templates --}}
<script type="text/x-custom-template" id="folder-template">
    <div class="group relative bg-surface-light dark:bg-surface-dark rounded-xl shadow-soft hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer border border-gray-100 dark:border-slate-700 hover:border-primary/30 media-item" data-id="__id__" data-type="folder" data-name="__name__">
        <input type="checkbox" class="hidden js-item-checkbox" data-id="__id__">
        <div class="aspect-[4/3] w-full overflow-hidden rounded-t-xl bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 flex items-center justify-center relative">
            <span class="material-symbols-rounded text-5xl text-amber-500">folder</span>
        </div>
        <div class="p-3">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">__name__</p>
            <div class="flex justify-between items-center mt-1">
                <span class="text-xs text-slate-400">Folder</span>
            </div>
        </div>
    </div>
</script>

<script type="text/x-custom-template" id="file-template">
    <div class="group relative bg-surface-light dark:bg-surface-dark rounded-xl shadow-soft hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer border border-gray-100 dark:border-slate-700 hover:border-primary/30 media-item" data-id="__id__" data-type="file" data-name="__name__" data-url="__url__" data-mime="__mime__" data-size="__size__" data-alt="__alt__">
        <input type="checkbox" class="hidden js-item-checkbox" data-id="__id__">
        <div class="absolute top-2 left-2 z-10 hidden group-hover:block">
            <label class="size-6 rounded bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center cursor-pointer">
                <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary">
            </label>
        </div>
        <div class="aspect-[4/3] w-full overflow-hidden rounded-t-xl bg-gray-100 dark:bg-slate-800 relative">
            __thumbnail__
            <div class="absolute top-2 right-2 bg-black/50 text-white text-[10px] px-2 py-0.5 rounded backdrop-blur-sm uppercase">__ext__</div>
        </div>
        <div class="p-3">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">__name__</p>
            <div class="flex justify-between items-center mt-1">
                <span class="text-xs text-slate-400">__size_formatted__</span>
            </div>
        </div>
    </div>
</script>

<script type="text/x-custom-template" id="detail-template">
    {{-- File Info Header --}}
    <div class="space-y-1">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white break-all">__name__</h3>
        <p class="text-xs text-slate-500 flex items-center gap-1">
            <span class="material-symbols-rounded text-sm">schedule</span>
            Uploaded on __date__
        </p>
    </div>
    
    {{-- Meta Info Grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700/50">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Dimensions</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">__dimensions__</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700/50">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Size</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">__size__</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700/50">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Type</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">__ext__</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700/50">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">MIME</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate" title="__mime__">__mime__</p>
        </div>
    </div>
    
    {{-- Full URL --}}
    <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Full URL</label>
        <div class="flex gap-2">
            <input type="text" class="flex-1 rounded-lg border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300 py-2" value="__url__" readonly id="file-url-input">
            <button class="px-3 py-2 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary transition-colors js-copy-url" title="Copy URL">
                <span class="material-symbols-rounded text-lg">content_copy</span>
            </button>
        </div>
    </div>
    
    {{-- Alt Text --}}
    <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Alt Text</label>
        <textarea class="w-full rounded-lg border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-sm focus:border-primary focus:ring-primary focus:bg-white dark:focus:bg-slate-900 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 resize-none transition-colors js-alt-input" placeholder="Describe the image for accessibility..." rows="2">__alt__</textarea>
    </div>
    
    <div class="h-px w-full bg-gray-100 dark:bg-slate-700"></div>
    
    {{-- Action Buttons --}}
    <div class="flex flex-col gap-3 pb-4">
        @if($popup ?? false)
        <button type="button"
                class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg text-sm font-semibold shadow-md shadow-emerald-600/20 transition-all active:scale-[0.98] js-insert-to-editor">
            <span class="material-symbols-rounded text-lg">add_photo_alternate</span>
            Insert to Editor
        </button>
        @endif
        <button class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white py-2.5 rounded-lg text-sm font-semibold shadow-md shadow-primary/20 transition-all active:scale-[0.98] js-save-changes">
            <span class="material-symbols-rounded text-lg">save</span>
            Save Changes
        </button>
        
        <div class="grid grid-cols-3 gap-2">
            <button class="flex flex-col items-center justify-center gap-1 p-3 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-all js-crop-action" title="Crop Image">
                <span class="material-symbols-rounded text-lg text-slate-600 dark:text-slate-300">crop</span>
                <span class="text-[10px] font-medium text-slate-500">Crop</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-1 p-3 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-all js-rename-action" title="Rename">
                <span class="material-symbols-rounded text-lg text-slate-600 dark:text-slate-300">edit</span>
                <span class="text-[10px] font-medium text-slate-500">Rename</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-1 p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all js-delete-action" title="Delete">
                <span class="material-symbols-rounded text-lg text-red-500">delete</span>
                <span class="text-[10px] font-medium text-red-500">Delete</span>
            </button>
        </div>
    </div>
</script>

<script type="text/x-custom-template" id="folder-detail-template">
    {{-- Folder Info Header --}}
    <div class="space-y-1">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white break-all">__name__</h3>
        <p class="text-xs text-slate-500 flex items-center gap-1">
            <span class="material-symbols-rounded text-sm">schedule</span>
            Created on __date__
        </p>
    </div>
    
    {{-- Folder Info --}}
    <div class="p-4 rounded-lg bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20">
        <div class="flex items-center gap-3">
            <span class="material-symbols-rounded text-3xl text-amber-500">folder</span>
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Folder</p>
                <p class="text-xs text-slate-500">Contains files and subfolders</p>
            </div>
        </div>
    </div>
    
    <div class="h-px w-full bg-gray-100 dark:bg-slate-700"></div>
    
    {{-- Action Buttons --}}
    <div class="flex flex-col gap-3 pb-4">
        <div class="grid grid-cols-2 gap-3">
            <button class="flex flex-col items-center justify-center gap-1 p-4 bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-all js-rename-action" title="Rename">
                <span class="material-symbols-rounded text-xl text-slate-600 dark:text-slate-300">edit</span>
                <span class="text-xs font-medium text-slate-500">Rename</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-1 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-all js-delete-action" title="Delete">
                <span class="material-symbols-rounded text-xl text-red-500">delete</span>
                <span class="text-xs font-medium text-red-500">Delete</span>
            </button>
    </div>
</script>

<script type="text/x-custom-template" id="trash-detail-template">
    {{-- Trash Item Info --}}
    <div class="space-y-1">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white break-all">__name__</h3>
        <p class="text-xs text-slate-500 flex items-center gap-1">
            <span class="material-symbols-rounded text-sm">schedule</span>
            Deleted on __date__
        </p>
    </div>
    
    {{-- Trash Warning --}}
    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20">
        <div class="flex items-center gap-3">
            <span class="material-symbols-rounded text-3xl text-red-500">delete_forever</span>
            <div>
                <p class="text-sm font-medium text-red-600 dark:text-red-400">In Trash</p>
                <p class="text-xs text-slate-500">Will be permanently deleted</p>
            </div>
        </div>
    </div>
    
    <div class="h-px w-full bg-gray-100 dark:bg-slate-700"></div>
    
    {{-- Trash Action Buttons --}}
    <div class="flex flex-col gap-3 pb-4">
        <button class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg text-sm font-semibold shadow-md shadow-emerald-600/20 transition-all active:scale-[0.98] js-restore-action">
            <span class="material-symbols-rounded text-lg">restore</span>
            Restore
        </button>
        <button class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold shadow-md shadow-red-600/20 transition-all active:scale-[0.98] js-delete-permanently-action">
            <span class="material-symbols-rounded text-lg">delete_forever</span>
            Delete Permanently
        </button>
    </div>
</script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-cropper@1.0.1/dist/jquery-cropper.min.js"></script>
    {{-- GLightbox for file preview --}}
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/js/glightbox.min.js"></script>
    {{-- jQuery contextMenu for right-click menu --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery-contextmenu@2.9.2/dist/jquery.contextMenu.min.js"></script>
    <script>
        window.MediaRoutes = {
            loadMedia:    '{{ route('admin.media.loadMedia') }}',
            updateName:   '{{ route('admin.media.update') }}',
            trash:        '{{ route('admin.media.destroy') }}',
            restore:      '{{ route('admin.media.restore') }}',
            destroyFinal: '{{ route('admin.media.destroyFinally') }}',
            createFolder: '{{ route('admin.media.folder.create') }}',
            upload:       '{{ route('admin.media.file.uploadFile') }}',
            updateCrop:   '{{ route('admin.media.file.updateCropImage') }}',
            updateData:   '{{ route('admin.media.file.updateData') }}',
        };
        // Path-only (no host) so the browser resolves it against the current host,
        // matching whatever vhost the admin is on (avoids APP_URL/vhost mismatch
        // producing cross-origin image requests).
        window.MediaBaseUrl = '{{ '/'.ltrim(config('file-manager.base_path', '/uploads'), '/') }}';
    </script>
    <script src="{{ asset('assets/core/media/js/CKMedia.js') }}?v={{ filemtime(public_path('assets/core/media/js/CKMedia.js')) }}"></script>
    <script>
        // Initialize GLightbox for media preview
        window.mediaLightbox = GLightbox({
            selector: '.media-preview-trigger',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });
        
        // Preview function for files
        window.previewMediaFile = function(url, type, name) {
            let slideData = { href: url, title: name };
            
            if (type.startsWith('video/')) {
                slideData.type = 'video';
                slideData.source = 'local';
            } else if (type.startsWith('audio/')) {
                slideData.type = 'inline';
                slideData.content = `<div class="p-8 bg-white dark:bg-slate-800 rounded-xl"><audio controls class="w-full"><source src="${url}" type="${type}">Your browser does not support audio.</audio><p class="mt-4 text-center text-slate-600 dark:text-slate-300">${name}</p></div>`;
            } else {
                slideData.type = 'image';
            }
            
            const lightbox = GLightbox({ elements: [slideData] });
            lightbox.open();
        };
        
        // Document preview function
        window.previewDocument = function(url, name) {
            const provider = '{{ document_preview_provider() }}';
            let previewUrl;
            
            if (provider === 'google') {
                previewUrl = 'https://docs.google.com/gview?embedded=true&url=' + encodeURIComponent(url);
            } else {
                previewUrl = 'https://view.officeapps.live.com/op/view.aspx?src=' + encodeURIComponent(url);
            }
            
            openModal('modal-document-preview');
            document.getElementById('document-preview-frame').src = previewUrl;
            document.getElementById('document-preview-title').textContent = name;
        };
    </script>
@endpush
