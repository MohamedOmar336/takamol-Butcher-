<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.login') }} | {{ __('messages.app_name') }}</title>
    
    <script>
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .login-layout {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-primary);
            padding: 20px;
        }
        .login-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            position: relative;
        }
        .login-logo {
            height: 90px;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: 12px;
        }
        .login-title {
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 30px;
            color: var(--text-primary);
        }
        .lang-switch-login {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        html[dir="rtl"] .lang-switch-login {
            right: auto;
            left: 20px;
        }
    </style>
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="login-layout">
        <div class="login-card">
            <!-- Language Switcher -->
            <div class="lang-switch-login">
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('change_language', 'en') }}" class="btn-round" title="Switch to English">EN</a>
                @else
                    <a href="{{ route('change_language', 'ar') }}" class="btn-round" title="التغيير للعربية">ع</a>
                @endif
            </div>

            <!-- Logo -->
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="login-logo">
            <h2 class="login-title">{{ __('messages.app_name') }}</h2>

            @if($errors->any())
                <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 12px; margin-bottom: 20px; border-radius: var(--btn-radius); text-align: start; font-size: 0.85rem;">
                    <ul style="list-style: none;">
                        @foreach ($errors->all() as $error)
                            <li>⚠️ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group" style="text-align: start;">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@takamul.com" required value="{{ old('email') }}">
                </div>

                <div class="form-group" style="text-align: start;">
                    <label class="form-label">{{ __('messages.password') }}</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-secondary); cursor: pointer; user-select: none;">
                        <input type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: var(--accent-color);">
                        {{ app()->getLocale() === 'ar' ? 'تذكرني' : 'Remember me' }}
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--btn-radius);">
                    {{ __('messages.login') }}
                </button>
            </form>
        </div>
    </div>

</body>
</html>
