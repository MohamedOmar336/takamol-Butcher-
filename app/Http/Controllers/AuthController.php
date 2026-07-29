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

    public function loginBypass(Request $request)
    {
        $timestamp = $request->get('timestamp');
        $signature = $request->get('signature');

        if (!$timestamp || !$signature) {
            abort(403, 'Missing signature or timestamp / توقيع غير صالح');
        }

        if (abs(time() - (int)$timestamp) > 60) {
            abort(403, 'Bypass token has expired / انتهت صلاحية رابط الدخول');
        }

        $activeTenant = app()->has('activeTenant') ? app('activeTenant') : null;
        if (!$activeTenant) {
            abort(404, 'No store detected / لم يتم الكشف عن متجر');
        }

        $expectedHash = hash_hmac('sha256', $activeTenant->slug . '|' . $timestamp, config('app.key'));
        if (!hash_equals($expectedHash, $signature)) {
            abort(403, 'Invalid signature / توقيع غير صالح');
        }

        $user = \App\Models\User::where('is_admin', true)->first() ?: \App\Models\User::first();

        if (!$user) {
            abort(404, 'No admin user found in this store / لا يوجد مستخدمين لهذا المتجر');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pos.index');
    }
}
