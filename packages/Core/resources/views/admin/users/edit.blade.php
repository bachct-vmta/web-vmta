@extends('core::layouts.admin')

@section('title', 'Sửa người dùng')
@section('page-title', 'Sửa người dùng: ' . $user->name)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Họ tên <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                <input type="password" name="password" placeholder="Để trống nếu không đổi"
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                              @error('password') border-red-500 @enderror">
                @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Tối thiểu 8 ký tự, bao gồm chữ cái và số</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới"
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vai trò</label>
                <select name="role_id"
                        class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">-- Không có --</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Số dư</label>
                <input type="number" name="balance" value="{{ old('balance', $user->balance) }}" step="0.01"
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Kích hoạt</span>
                </label>
            </div>
            
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium">
                    Cập nhật
                </button>
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
