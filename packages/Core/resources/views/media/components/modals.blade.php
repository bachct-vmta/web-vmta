{{-- Media Manager Modals --}}

{{-- Create Folder Modal --}}
<div id="modal-create-folder" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.folder.create') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-create-folder')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ trans('core-media::media.create_folder') }}</h3>
                    <button onclick="closeModal('modal-create-folder')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="create-folder-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ trans('core-media::media.folder_name') }}</label>
                        <input type="text" name="name" class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary" placeholder="{{ trans('core-media::media.folder_name') }}" required>
                        <input type="hidden" name="parent_id" value="0">
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-lg font-medium transition-all">
                        {{ trans('core-media::media.create') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Rename Modal --}}
<div id="modal-rename" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.update') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-rename')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ trans('core-media::media.rename') }}</h3>
                    <button onclick="closeModal('modal-rename')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="rename-form">
                    <input type="hidden" name="id">
                    <input type="hidden" name="is_folder">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ trans('core-media::media.new_name') }}</label>
                        <input type="text" name="name" class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary" required>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-lg font-medium transition-all">
                        {{ trans('core-media::media.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="modal-delete" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.destroy') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-delete')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 size-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-red-600">delete</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ trans('core-media::media.move_to_trash') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-6">{{ trans('core-media::media.confirm_trash') }}</p>
                <div class="flex gap-3">
                    <button onclick="closeModal('modal-delete')" class="flex-1 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                        {{ trans('core-media::media.close') }}
                    </button>
                    <button id="confirm-delete-btn" class="flex-1 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-all">
                        {{ trans('core-media::media.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Empty Trash Modal --}}
<div id="modal-empty-trash" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.emptyAllTrash') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-empty-trash')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 size-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-red-600">delete_forever</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ trans('core-media::media.empty_trash_title') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-6">{{ trans('core-media::media.empty_trash_title_confirm') }}</p>
                <div class="flex gap-3">
                    <button onclick="closeModal('modal-empty-trash')" class="flex-1 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                        {{ trans('core-media::media.close') }}
                    </button>
                    <button id="confirm-empty-trash-btn" class="flex-1 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-all">
                        {{ trans('core-media::media.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload From URL Modal --}}
<div id="modal-upload-url" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.file.uploadMultiple') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-upload-url')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ trans('core-media::media.download_link') }}</h3>
                    <button onclick="closeModal('modal-upload-url')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="upload-url-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">URL</label>
                        <textarea name="url" rows="3" class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-primary focus:ring-primary" placeholder="https://example.com/image.jpg"></textarea>
                        <p class="text-xs text-slate-400 mt-1">{{ trans('core-media::media.download_explain') }}</p>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-lg font-medium transition-all">
                        {{ trans('core-media::media.upload') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Crop Image Modal --}}
<div id="modal-crop" class="fixed inset-0 z-50 hidden overflow-y-auto" data-action="{{ route('admin.media.file.updateCropImage') }}">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-crop')"></div>
        <div class="relative w-full max-w-4xl rounded-2xl bg-white dark:bg-surface-dark shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ trans('core-media::media.crop') }}</h3>
                    <button onclick="closeModal('modal-crop')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="crop-form">
                    <input type="hidden" name="image_id">
                    <input type="hidden" name="crop_data">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-2">
                            <div id="crop-image" class="bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden min-h-[300px]"></div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ trans('core-media::media.cropper.width') }}</label>
                                <input type="text" name="dataWidth" id="dataWidth" class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ trans('core-media::media.cropper.height') }}</label>
                                <input type="text" name="dataHeight" id="dataHeight" class="w-full rounded-lg border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="aspectRatio" id="aspectRatio" class="rounded border-slate-300 text-primary focus:ring-primary">
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ trans('core-media::media.cropper.aspect_ratio') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeModal('modal-crop')" class="flex-1 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                            {{ trans('core-media::media.cropper.close') }}
                        </button>
                        <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-medium transition-all">
                            {{ trans('core-media::media.cropper.confirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Document Preview Modal --}}
<div id="modal-document-preview" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modal-document-preview')"></div>
        <div class="relative w-full max-w-5xl h-[85vh] rounded-2xl bg-white dark:bg-surface-dark shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-slate-700">
                <h3 id="document-preview-title" class="text-lg font-bold text-slate-900 dark:text-white truncate">Document Preview</h3>
                <button onclick="closeModal('modal-document-preview'); document.getElementById('document-preview-frame').src = '';" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <iframe id="document-preview-frame" class="w-full h-[calc(100%-60px)]" frameborder="0"></iframe>
        </div>
    </div>
</div>

{{-- Modal helpers --}}
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
