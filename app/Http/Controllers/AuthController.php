<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('pos.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect based on user permissions
            $user = Auth::user();
            if ($user->is_admin || $user->hasPermission('access_pos')) {
                return redirect()->intended(route('pos.index'));
            }
            if ($user->hasPermission('view_reports') || $user->hasPermission('manage_inventory')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->route('pos.index');
        }

        return back()->withErrors([
            'email' => app()->getLocale() === 'ar' 
                ? 'بيانات الدخول المدخلة غير مطابقة لسجلاتنا.' 
                : 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function changeLanguage($locale)
    {
        if (in_array($locale, ['en', 'ar'])) {
            session()->put('locale', $locale);
        }
        return redirect()->back();
    }
}
