<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ app()->getLocale() === 'ar' ? 'المدير العام دكان' : 'Dukkan Super Admin' }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    
    <!-- Inline script to prevent theme flashing -->
    <script>
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="brand" style="position: relative; width: 100%;">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" onerror="this.src='https://placehold.co/40x40/0d9488/fff?text=D'">
                <span class="brand-name">{{ app()->getLocale() === 'ar' ? 'المدير العام' : 'Super Admin' }}</span>
                <!-- Mobile close button -->
                <button id="sidebarClose" class="mobile-close-btn" style="display: none; position: absolute; top: -5px; left: -5px; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-secondary);" title="Close Sidebar">✕</button>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-item {{ Route::is('super_admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('super_admin.dashboard') }}">
                        <span class="nav-icon">🏢</span>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'إدارة المتاجر' : 'Manage Stores' }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('central.landing') }}" target="_blank">
                        <span class="nav-icon">🌐</span>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'الرئيسية العامة' : 'Central Landing' }}</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <form action="{{ route('super_admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <span class="logout-icon">🚪</span>
                        <span class="logout-text">{{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Sidebar Overlay Backdrop (Mobile only) -->
        <div id="sidebarBackdrop" class="sidebar-backdrop" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;"></div>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Header -->
            <header class="app-header">
                <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
                    <!-- Hamburger Menu Toggle Button (Mobile only) -->
                    <button id="sidebarToggle" class="mobile-toggle-btn" style="display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-primary); padding: 0;" title="Toggle Sidebar">
                        ☰
                    </button>
                    <h1 class="header-title" style="margin: 0;">@yield('header_title')</h1>
                </div>

                <div class="header-actions">
                    <!-- Language Toggle -->
                    @if(app()->getLocale() === 'ar')
                        <a href="{{ route('change_language', 'en') }}" class="btn-round" title="Switch to English">EN</a>
                    @else
                        <a href="{{ route('change_language', 'ar') }}" class="btn-round" title="التغيير للعربية">ع</a>
                    @endif

                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="btn-round" title="Toggle Light/Dark Theme">🌓</button>

                    <!-- User Profile Info -->
                    <div class="user-profile-widget">
                        <div class="user-info-text">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">
                                {{ app()->getLocale() === 'ar' ? 'المدير العام للمنصة' : 'Platform Owner' }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Body Contents -->
            <main class="app-body">
                @if(session('success'))
                    <div class="panel" style="background-color: var(--success-light); border-color: var(--success-color); color: var(--success-color); padding: 15px 20px; margin-bottom: 20px; border-radius: var(--btn-radius);">
                        <strong>{{ session('success') }}</strong>
                    </div>
                @endif

                @if(session('error'))
                    <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 15px 20px; margin-bottom: 20px; border-radius: var(--btn-radius);">
                        <strong>{{ session('error') }}</strong>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Theme Switcher & Sidebar JavaScript logic -->
    <script>
        const themeBtn = document.getElementById('themeToggle');
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Sidebar Toggle JavaScript logic for Mobile Responsive
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebar = document.querySelector('.app-sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        function openSidebar() {
            if (sidebar) sidebar.classList.add('active');
            if (backdrop) {
                backdrop.style.display = 'block';
                setTimeout(() => backdrop.classList.add('active'), 10);
            }
        }
        
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('active');
            if (backdrop) {
                backdrop.classList.remove('active');
                setTimeout(() => {
                    if (!backdrop.classList.contains('active')) {
                        backdrop.style.display = 'none';
                    }
                }, 300);
            }
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                openSidebar();
            });
        }
        
        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }
        
        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }
    </script>
    @yield('scripts')
</body>
</html>
