<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login | Dukkan</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    
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
            max-width: 440px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            position: relative;
        }
        .login-logo {
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 12px;
        }
        .login-title {
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .login-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
        .lang-switch-login {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
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
            <!-- Header Controls -->
            <div class="lang-switch-login">
                <button id="themeToggle" class="btn-round" title="Toggle Theme">🌓</button>
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('change_language', 'en') }}" class="btn-round">EN</a>
                @else
                    <a href="{{ route('change_language', 'ar') }}" class="btn-round">ع</a>
                @endif
            </div>

            <!-- Logo -->
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="login-logo" onerror="this.src='https://placehold.co/80x80/0d9488/fff?text=D'">
            <h2 class="login-title">{{ app()->getLocale() === 'ar' ? 'مدير عام دكان' : 'Dukkan Super Admin' }}</h2>
            <p class="login-subtitle">{{ app()->getLocale() === 'ar' ? 'لوحة تحكم إدارة المتاجر المركزية' : 'Central Platform Administration' }}</p>

            @if($errors->any() || session('error'))
                <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 12px; margin-bottom: 20px; border-radius: var(--btn-radius); text-align: start; font-size: 0.85rem;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @if(session('error'))
                            <li>⚠️ {{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>⚠️ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/super-admin/login" method="POST">
                @csrf
                <div class="form-group" style="text-align: start;">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني للمدير' : 'Super Admin Email' }}</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" required value="{{ old('email') }}" style="text-align: ltr;">
                </div>

                <div class="form-group" style="text-align: start;">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required style="text-align: ltr;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; border-radius: var(--btn-radius); font-weight: 700; margin-top: 10px;">
                    {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Sign In as Super Admin' }}
                </button>
            </form>
            
            <div style="margin-top: 20px; font-size: 0.85rem;">
                <a href="{{ route('central.landing') }}" style="color: var(--text-secondary); text-decoration: none;">
                    ← {{ app()->getLocale() === 'ar' ? 'العودة للصفحة الرئيسية' : 'Back to Home' }}
                </a>
            </div>
        </div>
    </div>

    <script>
        const themeBtn = document.getElementById('themeToggle');
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>
</html>
