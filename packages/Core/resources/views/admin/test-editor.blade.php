@extends('core::layouts.admin')

@section('title', 'Test CKEditor')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">CKEditor 5 Integration Test</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kiểm tra tính năng editor, upload ảnh, và Media Manager popup.</p>
    </div>

    {{-- Editor Form --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm">
        <form method="POST" action="#" class="p-6 space-y-6" id="test-editor-form">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Tiêu đề</label>
                <input type="text" name="title" value="Bài viết test CKEditor"
                       class="w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-slate-900 dark:text-white">
            </div>

            {{-- CKEditor Component --}}
            <x-core::ckeditor 
                name="content"
                label="Nội dung bài viết"
                :value="'<h2>Xin chào!</h2><p>Đây là nội dung mẫu để test CKEditor 5. Hãy thử:</p><ul><li>Thêm heading, bold, italic</li><li>Drag & drop ảnh vào editor</li><li>Nhấn nút <strong>Media Gallery</strong> bên dưới</li><li>Chèn bảng, code block, link</li></ul>'"
                rows="20"
            />

            {{-- Submit --}}
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-slate-700">
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-medium shadow-lg shadow-primary/20 transition-all active:scale-95">
                    Submit Test
                </button>
                <span class="text-sm text-slate-500 dark:text-slate-400">Form sẽ log content vào console</span>
            </div>
        </form>
    </div>

    {{-- Output Preview --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Preview Output</h2>
        <div id="preview-output" class="prose dark:prose-invert max-w-none min-h-[100px] text-sm text-slate-600 dark:text-slate-400 italic">
            Submit form để xem HTML output...
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('test-editor-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const content = this.querySelector('[name="content"]').value;
        const preview = document.getElementById('preview-output');
        preview.innerHTML = content;
        preview.classList.remove('italic');
        console.log('[Test CKEditor] Content:', content);
    });
</script>
@endpush
@endsection
