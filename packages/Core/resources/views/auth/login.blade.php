<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - VMTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ media_url((int) setting('site.logo_media_id'), asset('images/home/header/logo-vmta.png')) }}"
                 alt="VMTA" class="h-16 mx-auto">
            <p class="text-gray-500 mt-2">Đăng nhập vào tài khoản của bạn</p>
        </div>
        
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-lg">
            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                @foreach($errors->all() as $error)
                <p class="text-red-600 text-sm">{{ $error }}</p>
                @endforeach
            </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 pr-11 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                        <button type="button" id="toggle-password" aria-label="Hiện mật khẩu"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-600">Ghi nhớ</span>
                    </label>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 rounded-lg font-semibold transition-colors">
                    Đăng nhập
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            this.setAttribute('aria-label', hidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    </script>
</body>
</html>
