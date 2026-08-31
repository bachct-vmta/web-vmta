<?php

namespace Packages\Core\Src\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Core\Src\Http\Controllers\BaseController;

class LoginController extends BaseController
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('core::auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                ]);
            }

            // Redirect based on role
            if ($user->isSuperUser() || ($user->role && $user->role->slug === 'admin')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
