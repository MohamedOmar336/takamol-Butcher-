@php
    $tenantLogo = (isset($activeTenant) && isset($activeTenant->settings['logo'])) 
        ? asset($activeTenant->settings['logo']) 
        : asset('images/logo.jpg');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $activeTenant->name ?? __('messages.app_name') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpeg" href="{{ $tenantLogo }}">
    
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
                <img src="{{ $tenantLogo }}" alt="Logo">
                <span class="brand-name">{{ $activeTenant->name ?? __('messages.app_name') }}</span>
                <!-- Mobile close button -->
                <button id="sidebarClose" class="mobile-close-btn" style="display: none; position: absolute; top: -5px; left: -5px; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-secondary);" title="Close Sidebar">✕</button>
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
                    <li class="nav-item {{ Route::is('admin.orders.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.orders.index') }}">
                            <span class="nav-icon">🧾</span>
                            <span class="nav-text">{{ __('messages.orders_list') }}</span>
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
                    <li class="nav-item {{ Route::is('admin.drivers.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.drivers.index') }}">
                            <span class="nav-icon">🛵</span>
                            <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'الطيارين' : 'Drivers' }}</span>
                        </a>
                    </li>
                @endif

                @if(isset($activeTenant) && in_array($activeTenant->store_type, ['butcher', 'supermarket']))
                <li class="nav-item {{ Route::is('scale.simulator') ? 'active' : '' }}">
                    <a href="{{ route('scale.simulator') }}">
                        <span class="nav-icon">⚖️</span>
                        <span class="nav-text">{{ __('messages.scale_simulator') }}</span>
                    </a>
                </li>
                @endif
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
                    <!-- Desktop sidebar collapse button -->
                    <button id="desktopSidebarToggle" class="desktop-toggle-btn" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-secondary); padding: 0; display: flex; align-items: center; justify-content: center;" title="Collapse/Expand Sidebar">
                        ◀
                    </button>
                    <h1 class="header-title" style="margin: 0;">@yield('header_title')</h1>
                </div>

                <div class="header-actions">
                    <!-- Real-Time Ping & Network Indicator Badge -->
                    <div id="pingIndicator" class="ping-badge" title="{{ app()->getLocale() === 'ar' ? 'حالة الاتصال والبنج للشبكة' : 'Network Connection & Ping Status' }}" style="display: flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 50px; background-color: var(--bg-tertiary); border: 1px solid var(--border-color); font-size: 0.82rem; font-weight: 700; user-select: none;">
                        <span id="pingLed" class="ping-led online" style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981; box-shadow: 0 0 8px #10b981; display: inline-block; transition: all 0.3s ease;"></span>
                        <span id="pingText" style="color: var(--text-primary);">{{ app()->getLocale() === 'ar' ? 'جاري الفحص...' : 'Checking...' }}</span>
                        <span id="pingMs" style="font-size: 0.75rem; color: var(--text-secondary); font-family: monospace;">-- ms</span>
                    </div>

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

        // Collapsible Sidebar Desktop logic
        const appLayout = document.querySelector('.app-layout');
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

        function updateToggleIcon(collapsed) {
            if (desktopSidebarToggle) {
                if (collapsed) {
                    desktopSidebarToggle.innerText = isRtl ? '◀' : '▶';
                } else {
                    desktopSidebarToggle.innerText = isRtl ? '▶' : '◀';
                }
            }
        }

        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            if (appLayout) appLayout.classList.add('sidebar-collapsed');
            updateToggleIcon(true);
        } else {
            updateToggleIcon(false);
        }

        if (desktopSidebarToggle) {
            desktopSidebarToggle.addEventListener('click', () => {
                if (appLayout) {
                    const collapsed = appLayout.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebar-collapsed', collapsed ? 'true' : 'false');
                    updateToggleIcon(collapsed);
                }
            });
        }

        // Real-Time Ping & Network Status Checker
        function checkPing() {
            const pingLed = document.getElementById('pingLed');
            const pingText = document.getElementById('pingText');
            const pingMs = document.getElementById('pingMs');
            if (!pingLed || !pingText || !pingMs) return;

            if (!navigator.onLine) {
                pingLed.style.backgroundColor = '#ef4444';
                pingLed.style.boxShadow = '0 0 8px #ef4444';
                pingText.innerText = "{{ app()->getLocale() === 'ar' ? 'غير متصل' : 'Offline' }}";
                pingMs.innerText = '-- ms';
                return;
            }

            const startTime = performance.now();
            fetch("{{ route('change_language', app()->getLocale()) }}", { method: 'HEAD', cache: 'no-store' })
                .then(() => {
                    const latency = Math.round(performance.now() - startTime);
                    pingMs.innerText = `${latency} ms`;

                    if (latency < 200) {
                        pingLed.style.backgroundColor = '#10b981';
                        pingLed.style.boxShadow = '0 0 8px #10b981';
                        pingText.innerText = "{{ app()->getLocale() === 'ar' ? 'متصل' : 'Online' }}";
                    } else {
                        pingLed.style.backgroundColor = '#f59e0b';
                        pingLed.style.boxShadow = '0 0 8px #f59e0b';
                        pingText.innerText = "{{ app()->getLocale() === 'ar' ? 'بطيء' : 'Slow' }}";
                    }
                })
                .catch(() => {
                    pingLed.style.backgroundColor = '#ef4444';
                    pingLed.style.boxShadow = '0 0 8px #ef4444';
                    pingText.innerText = "{{ app()->getLocale() === 'ar' ? 'غير متصل' : 'Offline' }}";
                    pingMs.innerText = '-- ms';
                });
        }

        window.addEventListener('online', checkPing);
        window.addEventListener('offline', checkPing);
        checkPing();
        setInterval(checkPing, 8000);
    </script>
    @yield('scripts')
</body>
</html>
