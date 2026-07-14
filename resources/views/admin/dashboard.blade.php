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

<div class="dashboard-grid">
    <!-- Left Column Wrapper -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
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
                            <div class="chart-bar-container">
                                <div class="chart-bar" style="height: {{ max(4, $heightPercent) }}%;">
                                    <span class="chart-bar-value">{{ floatval($amount) }} ج.م</span>
                                </div>
                            </div>
                            <span class="chart-label">{{ $formattedDate }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Invoices Panel -->
        <div class="panel" style="margin-bottom: 0;">
            <div class="panel-header">
                <h3 class="panel-title">{{ __('messages.recent_orders') }}</h3>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.8rem;">
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
                            <th>{{ __('messages.order_status') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ __('messages.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr style="{{ $order->status === 'refunded' ? 'opacity: 0.65;' : '' }}">
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
                                <td>
                                    @if($order->status === 'completed')
                                        <span class="badge badge-success">{{ __('messages.completed') }}</span>
                                    @else
                                        <span class="badge badge-danger" style="background-color: var(--danger-light); color: var(--danger-color);">{{ __('messages.refunded') }}</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="font-bold" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                                    {{ floatval($order->total_amount) }} {{ __('messages.currency') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير صادرة اليوم بعد.' : 'No invoices issued today yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column Wrapper -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
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

        <!-- Custom Period Report Panel -->
        <div class="panel" style="margin-bottom: 0;">
            <div class="panel-header">
                <h3 class="panel-title">{{ app()->getLocale() === 'ar' ? 'تقرير مبيعات فترة مخصصة' : 'Custom Period Report' }}</h3>
            </div>

            <form action="{{ route('admin.send_range_report') }}" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.82rem;">{{ app()->getLocale() === 'ar' ? 'تاريخ البداية' : 'Start Date' }}</label>
                    <input type="date" name="start_date" id="report_start_date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" style="padding: 8px;" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.82rem;">{{ app()->getLocale() === 'ar' ? 'تاريخ النهاية' : 'End Date' }}</label>
                    <input type="date" name="end_date" id="report_end_date" class="form-control" value="{{ now()->format('Y-m-d') }}" style="padding: 8px;" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 5px;">
                    <button type="submit" class="btn btn-primary" style="flex-grow: 1; padding: 10px; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        ✉️ {{ app()->getLocale() === 'ar' ? 'إرسال للمالك' : 'Email Owner' }}
                    </button>
                    <button type="button" id="btnPrintRangeReport" class="btn btn-secondary" style="padding: 10px 14px; border-color: var(--border-color); display: flex; align-items: center; justify-content: center;" title="{{ app()->getLocale() === 'ar' ? 'معاينة وطباعة A4' : 'A4 Print Preview' }}">
                        🖨️
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnPrintRangeReport = document.getElementById('btnPrintRangeReport');
        if (btnPrintRangeReport) {
            btnPrintRangeReport.addEventListener('click', () => {
                const start = document.getElementById('report_start_date').value;
                const end = document.getElementById('report_end_date').value;
                if (!start || !end) {
                    alert("{{ app()->getLocale() === 'ar' ? 'يرجى تحديد تاريخ البداية والنهاية أولاً.' : 'Please select both start and end dates first.' }}");
                    return;
                }
                const url = `{{ route('admin.print_range_report') }}?start_date=${start}&end_date=${end}`;
                window.open(url, '_blank');
            });
        }
    });
</script>
@endsection
