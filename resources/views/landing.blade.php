<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DokkanHub SAAS POS | دكان هب</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    
    <script>
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <style>
        .landing-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .landing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-secondary);
        }
        .landing-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent-color);
        }
        .landing-brand img {
            height: 40px;
            border-radius: 8px;
        }
        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .hero-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 20px;
            flex-grow: 1;
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .hero-title span {
            color: var(--accent-color);
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin-bottom: 40px;
        }
        .portal-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 35px;
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            text-align: start;
        }
        .portal-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 900px;
            margin: 60px auto 40px auto;
            padding: 0 20px;
        }
        .feature-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: transform 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .feature-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .landing-footer {
            padding: 30px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            background-color: var(--bg-secondary);
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .landing-footer a {
            color: var(--accent-color);
            text-decoration: underline;
        }
    </style>
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="landing-container">
        <!-- Header -->
        <header class="landing-header">
            <div class="landing-brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="Dukkan Logo" onerror="this.src='https://placehold.co/40x40/0d9488/fff?text=D'">
                <span>{{ app()->getLocale() === 'ar' ? 'منصة دكان هب' : 'DokkanHub Platform' }}</span>
            </div>
            
            <div class="header-controls">
                <!-- Theme Switcher -->
                <button id="themeToggle" class="btn-round" title="Toggle Light/Dark Theme">🌓</button>
                
                <!-- Language Toggle -->
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('change_language', 'en') }}" class="btn-round">EN</a>
                @else
                    <a href="{{ route('change_language', 'ar') }}" class="btn-round">ع</a>
                @endif
            </div>
        </header>

        <!-- Hero & Portal -->
        <section class="hero-section">
            <h1 class="hero-title">
                @if(app()->getLocale() === 'ar')
                    أدر تجارتك بذكاء مع <span>دكان هب</span>
                @else
                    Manage Your Business Smartly with <span>DokkanHub</span>
                @endif
            </h1>
            <p class="hero-subtitle">
                @if(app()->getLocale() === 'ar')
                    نظام نقاط البيع وإدارة المخزون المتكامل لجميع المحلات التجارية (سوبر ماركت، ملابس، أحذية، جزارة، والمزيد).
                @else
                    The complete cloud POS & inventory system for all retail stores (supermarkets, clothing, shoes, butchers, and more).
                @endif
            </p>

            <!-- Login Portal Card -->
            <div class="portal-card">
                <h3 class="portal-title">
                    <span>🔑</span>
                    {{ app()->getLocale() === 'ar' ? 'الدخول إلى متجرك' : 'Access Your Store' }}
                </h3>

                @if($errors->has('slug'))
                    <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 12px; margin-bottom: 20px; border-radius: var(--btn-radius); font-size: 0.85rem;">
                        ⚠️ {{ $errors->first('slug') }}
                    </div>
                @endif

                <form action="/redirect-store" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم المتجر (الرابط الفرعي)' : 'Store Name (Subdomain Slug)' }}</label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="text" name="slug" class="form-control" placeholder="e.g. takamul" required value="{{ old('slug') }}" style="text-align: ltr;">
                            <span style="font-weight: 600; color: var(--text-secondary);">.{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost' }}</span>
                        </div>
                        <small style="color: var(--text-secondary); margin-top: 5px; display: block;">
                            {{ app()->getLocale() === 'ar' ? 'أدخل اسم متجرك للانتقال لصفحة الكاشير الخاصة بك.' : 'Enter your store name to access your specific POS terminal.' }}
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--btn-radius); font-weight: 700; margin-top: 15px;">
                        {{ app()->getLocale() === 'ar' ? 'الدخول لنقطة البيع ←' : 'Go to POS Terminal →' }}
                    </button>
                </form>
            </div>

            <!-- Features Presets Showcase -->
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🥩</div>
                    <h4 class="feature-title">{{ app()->getLocale() === 'ar' ? 'محلات الجزارة' : 'Butcher Shops' }}</h4>
                    <p class="feature-desc">{{ app()->getLocale() === 'ar' ? 'يدعم الميزان الإلكتروني وقراءة الباركود المشفر بالوزن والسعر.' : 'Supports scale simulators and parses weight/price encoded barcodes.' }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h4 class="feature-title">{{ app()->getLocale() === 'ar' ? 'سوبر ماركت' : 'Supermarkets' }}</h4>
                    <p class="feature-desc">{{ app()->getLocale() === 'ar' ? 'إدخال سريع للمنتجات بالباركود، إدارة للمخزون والتنبيه بالكميات القليلة.' : 'Quick barcode scanning, bulk product import, and inventory warnings.' }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👕</div>
                    <h4 class="feature-title">{{ app()->getLocale() === 'ar' ? 'محلات الملابس' : 'Clothing Stores' }}</h4>
                    <p class="feature-desc">{{ app()->getLocale() === 'ar' ? 'إخفاء خيارات الأوزان وتسهيل البيع بالقطعة وتتبع الفواتير والديون.' : 'Clean piece-based layout, debt registers, and billing tracking.' }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👟</div>
                    <h4 class="feature-title">{{ app()->getLocale() === 'ar' ? 'محلات الأحذية' : 'Shoe Stores' }}</h4>
                    <p class="feature-desc">{{ app()->getLocale() === 'ar' ? 'واجهة مخصصة سريعة للبيع الفوري وحساب الأرباح والتقارير اليومية.' : 'High performance checkout dashboard with profit & daily sales analytics.' }}</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <p>
                © 2026 DokkanHub POS SAAS. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                | 
                <a href="{{ route('super_admin.login') }}">{{ app()->getLocale() === 'ar' ? 'لوحة تحكم المدير العام' : 'Super Admin Portal' }}</a>
            </p>
        </footer>
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
