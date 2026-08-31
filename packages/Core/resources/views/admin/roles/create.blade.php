@extends('core::layouts.admin')

@section('title', 'Thêm vai trò')
@section('page-title', 'Thêm vai trò')

@section('content')
<div class="max-w-4xl mx-auto">
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Thông tin vai trò</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                                      @error('name') border-red-500 @enderror">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') }}"
                               class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                               placeholder="Tự động tạo nếu trống">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('description') }}</textarea>
                </div>
                
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Vai trò mặc định cho người dùng mới</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Permissions -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Phân quyền</h3>
            </div>
            
            <div class="p-6">
                <div class="space-y-6">
                    @foreach($groupedPermissions as $parentFlag => $group)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $group['flag'] }}"
                                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 parent-checkbox"
                                       data-parent="{{ $group['flag'] }}">
                                <span class="font-medium text-gray-900">{{ $group['name'] }}</span>
                            </label>
                        </div>
                        
                        @if(!empty($group['children']))
                        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($group['children'] as $child)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $child['flag'] }}"
                                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 child-checkbox"
                                       data-parent="{{ $group['flag'] }}">
                                <span class="text-sm text-gray-600">{{ $child['name'] }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium">
                Tạo mới
            </button>
            <a href="{{ route('admin.roles.index') }}" class="text-gray-500 hover:text-gray-700">
                Hủy
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.parent-checkbox').forEach(parent => {
    parent.addEventListener('change', function() {
        const parentFlag = this.dataset.parent;
        document.querySelectorAll(`.child-checkbox[data-parent="${parentFlag}"]`).forEach(child => {
            child.checked = this.checked;
        });
    });
});
</script>
@endpush
@endsection
