<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ __('messages.app_name') }}</title>
    
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
            <div class="brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
                <span class="brand-name">{{ __('messages.app_name') }}</span>
            </div>

            <ul class="sidebar-nav">
                @if(auth()->user()->is_admin || auth()->user()->hasPermission('access_pos'))
                    <li class="nav-item {{ Route::is('pos.index') ? 'active' : '' }}">
                        <a href="{{ route('pos.index') }}">
                            <span class="nav-icon">🛒</span>
                            <span class="nav-text">{{ __('messages.pos') }}</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->is_admin || auth()->user()->hasPermission('view_reports'))
                    <li class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">{{ __('messages.dashboard') }}</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->is_admin || auth()->user()->hasPermission('manage_inventory'))
                    <li class="nav-item {{ Route::is('admin.products.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.index') }}">
                            <span class="nav-icon">🥩</span>
                            <span class="nav-text">{{ __('messages.products') }}</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->is_admin || auth()->user()->hasPermission('access_pos'))
                    <li class="nav-item {{ Route::is('admin.customers.index') || Route::is('admin.customers.show') ? 'active' : '' }}">
                        <a href="{{ route('admin.customers.index') }}">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">{{ __('messages.customers') }}</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->is_admin || auth()->user()->hasPermission('manage_users'))
                    <li class="nav-item {{ Route::is('admin.users') ? 'active' : '' }}">
                        <a href="{{ route('admin.users') }}">
                            <span class="nav-icon">🔑</span>
                            <span class="nav-text">{{ __('messages.users') }}</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item {{ Route::is('scale.simulator') ? 'active' : '' }}">
                    <a href="{{ route('scale.simulator') }}">
                        <span class="nav-icon">⚖️</span>
                        <span class="nav-text">{{ __('messages.scale_simulator') }}</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <span class="logout-icon">🚪</span>
                        <span class="logout-text">{{ __('messages.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Header -->
            <header class="app-header">
                <div class="header-left">
                    <h1 class="header-title">@yield('header_title')</h1>
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
                                {{ auth()->user()->is_admin ? __('messages.is_super_admin') : '' }}
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

    <!-- Theme Switcher JavaScript logic -->
    <script>
        const themeBtn = document.getElementById('themeToggle');
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
    @yield('scripts')
</body>
</html>
