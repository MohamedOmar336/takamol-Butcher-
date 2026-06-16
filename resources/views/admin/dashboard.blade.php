@extends('layouts.app')

@section('title', __('messages.dashboard'))
@section('header_title', __('messages.admin_panel') . ' - ' . __('messages.dashboard'))

@section('content')
<!-- Core Stats Cards Row -->
<div class="stats-grid">
    <!-- Stat 1: Total Sales Today -->
    <div class="stat-card success">
        <div class="stat-info">
            <span class="stat-label">{{ __('messages.today_sales') }}</span>
            <span class="stat-value">{{ floatval($totalSalesToday) }} <span style="font-size:1rem; font-weight:600;">{{ __('messages.currency') }}</span></span>
        </div>
        <span class="stat-icon">💰</span>
    </div>

    <!-- Stat 2: Total Orders Today -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">{{ __('messages.today_orders') }}</span>
            <span class="stat-value">{{ $totalOrdersToday }}</span>
        </div>
        <span class="stat-icon">🧾</span>
    </div>

    <!-- Stat 3: Low Stock Warnings -->
    <div class="stat-card {{ $lowStockCount > 0 ? 'danger' : '' }}">
        <div class="stat-info">
            <span class="stat-label">{{ __('messages.low_stock_warnings') }}</span>
            <span class="stat-value">{{ $lowStockCount }}</span>
        </div>
        <span class="stat-icon">⚠️</span>
    </div>

    <!-- Stat 4: Customers with Debt -->
    <div class="stat-card {{ $indebtedCustomersCount > 0 ? 'warning' : '' }}">
        <div class="stat-info">
            <span class="stat-label">{{ __('messages.debtors') }}</span>
            <span class="stat-value">{{ $indebtedCustomersCount }}</span>
        </div>
        <span class="stat-icon">👥</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px; align-items: stretch;">
    <!-- Sales chart container -->
    <div class="panel" style="margin-bottom: 0;">
        <div class="panel-header">
            <h3 class="panel-title">{{ __('messages.weekly_sales_chart') }}</h3>
        </div>
        
        @php
            $maxAmount = max(100, ...array_values($chartData));
        @endphp

        <div class="chart-container">
            <div class="custom-chart">
                @foreach($chartData as $date => $amount)
                    @php
                        $heightPercent = ($amount / $maxAmount) * 100;
                        $formattedDate = \Carbon\Carbon::parse($date)->locale(app()->getLocale())->isoFormat('ddd D/M');
                    @endphp
                    <div class="chart-bar-wrapper">
                        <div class="chart-bar" style="height: {{ max(4, $heightPercent) }}%;">
                            <span class="chart-bar-value">{{ floatval($amount) }} ج.م</span>
                        </div>
                        <span class="chart-label">{{ $formattedDate }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick action panel -->
    <div class="panel" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="panel-header">
            <h3 class="panel-title">{{ app()->getLocale() === 'ar' ? 'إجراءات سريعة' : 'Quick Actions' }}</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px; flex-grow: 1; justify-content: center;">
            <!-- Send Report Manual Button -->
            <form action="{{ route('admin.send_report') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    ✉️ {{ __('messages.send_report_now') }}
                </button>
            </form>

            <!-- Access POS Button -->
            @if(auth()->user()->is_admin || auth()->user()->hasPermission('access_pos'))
                <a href="{{ route('pos.index') }}" class="btn btn-success" style="padding: 15px; font-size: 0.95rem;">
                    🛒 {{ __('messages.pos') }}
                </a>
            @endif

            <!-- Scale simulator -->
            <a href="{{ route('scale.simulator') }}" class="btn btn-secondary" style="padding: 15px; font-size: 0.95rem; border-color: var(--border-color);">
                ⚖️ {{ __('messages.scale_simulator') }}
            </a>
        </div>
    </div>
</div>

<!-- Recent Orders Panel -->
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">{{ __('messages.recent_orders') }}</h3>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.8rem;">
            {{ __('messages.view_all') }}
        </a>
    </div>

    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>{{ __('messages.order_no') }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الكاشير' : 'Cashier' }}</th>
                    <th>{{ __('messages.customers') }}</th>
                    <th>{{ __('messages.payment_method') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ __('messages.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td class="font-bold" style="color: var(--accent-color);">
                            <!-- Clickable receipt print trigger link -->
                            <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" title="Print/View Receipt">
                                📄 {{ $order->order_number }}
                            </a>
                        </td>
                        <td>{{ $order->cashier_name ?? ($order->user ? $order->user->name : '-') }}</td>
                        <td>
                            @if($order->customer)
                                <a href="{{ route('admin.customers.show', $order->customer_id) }}" style="font-weight: 600; text-decoration: underline;">
                                    {{ $order->customer->name }}
                                </a>
                            @else
                                <span style="color: var(--text-muted);">{{ app()->getLocale() === 'ar' ? 'عميل كاش (غير مسجل)' : 'Cash Customer' }}</span>
                            @endif
                        </td>
                        <td>
                            @if($order->payment_method === 'cash')
                                <span class="badge badge-success">{{ __('messages.cash') }}</span>
                            @elseif($order->payment_method === 'card')
                                <span class="badge badge-warning" style="background-color: var(--accent-light); color: var(--accent-color);">{{ __('messages.card') }}</span>
                            @elseif($order->payment_method === 'credit')
                                <span class="badge badge-danger">{{ __('messages.credit') }}</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="font-bold" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                            {{ floatval($order->total_amount) }} {{ __('messages.currency') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير صادرة اليوم بعد.' : 'No invoices issued today yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
