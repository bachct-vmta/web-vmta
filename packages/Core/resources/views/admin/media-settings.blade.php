@extends('core::layouts.admin')

@section('title', trans('core-media::media.settings_title', [], 'Media Settings'))

@section('content')
<div class="container-xl py-4">
    <div class="page-header mb-6">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title text-lg font-bold text-slate-800 dark:text-white">
                    {{ trans('core-media::media.settings_title', [], 'Media Settings') }}
                </h2>
                <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ trans('core-media::media.settings_description', [], 'Configure media upload settings, file types, and preview options.') }}
                </div>
            </div>
        </div>
    </div>

    <form id="media-settings-form" action="{{ route('admin.media.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Upload Settings Card --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-100 dark:border-slate-700 p-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary">cloud_upload</span>
                    {{ trans('core-media::media.upload_settings', [], 'Upload Settings') }}
                </h3>
                
                <div class="space-y-4">
                    {{-- Max File Size --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ trans('core-media::media.max_file_size', [], 'Max File Size (MB)') }}
                        </label>
                        <input type="number" 
                               name="max_file_size" 
                               value="{{ $settings['media_max_file_size_mb'] ?? 10 }}"
                               min="1" 
                               max="1024" 
                               step="0.1"
                               class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary">
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.max_file_size_help', [], 'Maximum file size allowed for uploads (1-1024 MB)') }}</p>
                    </div>
                    
                    {{-- Default Upload Folder --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ trans('core-media::media.default_folder', [], 'Default Upload Folder') }}
                        </label>
                        <input type="text" 
                               name="default_upload_folder" 
                               value="{{ $settings['media_default_upload_folder'] ?? 'uploads' }}"
                               class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary">
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.default_folder_help', [], 'Folder name in public directory') }}</p>
                    </div>
                    
                    {{-- Allowed MIME Types --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ trans('core-media::media.allowed_types', [], 'Allowed File Types') }}
                        </label>
                        <textarea name="allowed_mime_types" 
                                  rows="3"
                                  class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary font-mono text-sm">{{ $settings['media_allowed_mime_types'] ?? 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,zip' }}</textarea>
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.allowed_types_help', [], 'Comma-separated list of file extensions') }}</p>
                    </div>
                </div>
            </div>
            
            {{-- Chunk Upload Card --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-100 dark:border-slate-700 p-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary">upload_file</span>
                    {{ trans('core-media::media.chunk_upload', [], 'Chunk Upload') }}
                </h3>
                
                <div class="space-y-4">
                    {{-- Enable Chunk Upload --}}
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                        <div>
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ trans('core-media::media.enable_chunk', [], 'Enable Chunk Upload') }}
                            </label>
                            <p class="text-xs text-slate-400 mt-0.5">{{ trans('core-media::media.enable_chunk_help', [], 'Split large files into smaller chunks') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="chunk_enabled" 
                                   value="1"
                                   {{ ($settings['media_chunk_enabled'] ?? false) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/25 dark:peer-focus:ring-primary/25 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    
                    {{-- Chunk Size --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ trans('core-media::media.chunk_size', [], 'Chunk Size (KB)') }}
                        </label>
                        <input type="number" 
                               name="chunk_size" 
                               value="{{ $settings['media_chunk_size_kb'] ?? 1024 }}"
                               min="128" 
                               max="10240" 
                               step="128"
                               class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary">
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.chunk_size_help', [], 'Size of each chunk (128-10240 KB)') }}</p>
                    </div>
                </div>
            </div>
            
            {{-- Document Preview Card --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-100 dark:border-slate-700 p-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary">description</span>
                    {{ trans('core-media::media.document_preview', [], 'Document Preview') }}
                </h3>
                
                <div class="space-y-4">
                    {{-- Enable Document Preview --}}
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                        <div>
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ trans('core-media::media.enable_preview', [], 'Enable Document Preview') }}
                            </label>
                            <p class="text-xs text-slate-400 mt-0.5">{{ trans('core-media::media.enable_preview_help', [], 'Preview PDF, DOC, XLS, PPT files') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="document_preview_enabled" 
                                   value="1"
                                   {{ ($settings['media_document_preview_enabled'] ?? true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/25 dark:peer-focus:ring-primary/25 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    
                    {{-- Preview Provider --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ trans('core-media::media.preview_provider', [], 'Preview Provider') }}
                        </label>
                        <select name="document_preview_provider" 
                                class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary">
                            <option value="microsoft" {{ ($settings['media_document_preview_provider'] ?? 'microsoft') === 'microsoft' ? 'selected' : '' }}>
                                Microsoft Office Online
                            </option>
                            <option value="google" {{ ($settings['media_document_preview_provider'] ?? '') === 'google' ? 'selected' : '' }}>
                                Google Docs Viewer
                            </option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.preview_provider_help', [], 'External service for document preview') }}</p>
                    </div>
                </div>
            </div>
            
            {{-- Google Drive Storage Card --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-soft border border-gray-100 dark:border-slate-700 p-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary">cloud</span>
                    Google Drive Storage
                </h3>
                
                <div class="space-y-4">
                    {{-- Current Storage Driver --}}
                    <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Storage Driver Hiện Tại
                                </label>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Cấu hình qua biến môi trường <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">MEDIA_STORAGE_DRIVER</code>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ ($currentDriver ?? 'local') === 'google' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                {{ ($currentDriver ?? 'local') === 'google' ? 'Google Drive' : 'Local' }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Google Drive Connection Status --}}
                    <div class="border border-slate-200 dark:border-slate-600 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                            Kết nối Google Drive
                        </h4>
                        
                        @if($googleDriveConnected ?? false)
                            <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg mb-3">
                                <span class="material-symbols-rounded text-green-600 dark:text-green-400">check_circle</span>
                                <div>
                                    <p class="text-sm font-medium text-green-700 dark:text-green-400">Đã kết nối</p>
                                    <p class="text-xs text-green-600 dark:text-green-500">{{ $googleDriveEmail ?? '' }}</p>
                                </div>
                            </div>
                            
                            <form action="{{ route(admin_route_name('media.google-drive.disconnect')) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Bạn có chắc muốn ngắt kết nối Google Drive?')"
                                        class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <span class="material-symbols-rounded text-base">link_off</span>
                                    Ngắt kết nối
                                </button>
                            </form>
                        @else
                            <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg mb-3">
                                <span class="material-symbols-rounded text-amber-600 dark:text-amber-400">warning</span>
                                <div>
                                    <p class="text-sm font-medium text-amber-700 dark:text-amber-400">Chưa kết nối</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-500">Kết nối để sử dụng Google Drive làm storage</p>
                                </div>
                            </div>
                            
                            <a href="{{ route(admin_route_name('media.google-drive.redirect')) }}" 
                               class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <span class="material-symbols-rounded text-base">add_link</span>
                                Kết nối Google Drive
                            </a>
                        @endif
                        
                        <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Lưu ý:</strong> Để sử dụng Google Drive, bạn cần cấu hình các biến môi trường sau trong file <code>.env</code>:
                            </p>
                            <ul class="text-xs text-slate-500 dark:text-slate-400 mt-2 space-y-1 font-mono">
                                <li>• MEDIA_STORAGE_DRIVER=google</li>
                                <li>• GOOGLE_DRIVE_CLIENT_ID=...</li>
                                <li>• GOOGLE_DRIVE_CLIENT_SECRET=...</li>
                                <li>• GOOGLE_DRIVE_FOLDER_ID=... (optional)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Save Button --}}
        <div class="flex justify-end mt-6">
            <button type="submit" 
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-lg shadow-primary/20 transition-all active:scale-95">
                <span class="material-symbols-rounded text-lg">save</span>
                {{ trans('core-media::media.save_settings', [], 'Save Settings') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('media-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-rounded text-lg animate-spin">sync</span> Saving...';
        
        try {
            const response = await fetch(form.action, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData.entries()))
            });
            
            const data = await response.json();
            
            if (data.success) {
                toastr.success(data.message || 'Settings saved successfully');
            } else {
                toastr.error(data.message || 'Failed to save settings');
            }
        } catch (error) {
            toastr.error('An error occurred while saving settings');
            console.error(error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
</script>
@endpush
