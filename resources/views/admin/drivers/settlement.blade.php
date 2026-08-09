@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تقرير تقفيل طيارين الدليفري' : 'End-of-Day Driver Settlement')
@section('header_title', app()->getLocale() === 'ar' ? 'تقفيل حساب المبيعات والطيارين اليومي' : 'End-of-Day Driver Settlement')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Filter Bar -->
    <div class="panel" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.drivers.settlement') }}" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'تاريخ التقفيل' : 'Settlement Date' }}</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'تصفية حسب الطيار' : 'Filter by Driver' }}</label>
                <select name="driver_id" class="form-control">
                    <option value="">-- {{ app()->getLocale() === 'ar' ? 'جميع الطيارين' : 'All Drivers' }} --</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ (string)$selectedDriverId === (string)$d->id ? 'selected' : '' }}>
                            🛵 {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">
                🔍 {{ app()->getLocale() === 'ar' ? 'عرض التقرير' : 'Filter' }}
            </button>
        </form>
    </div>

    <!-- Drivers Summary Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        @forelse($driverSummaries as $summary)
            <div class="panel" style="padding: 20px; border-inline-start: 4px solid var(--accent-color);">
                <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 15px; display: flex; justify-content: space-between;">
                    <span>🛵 {{ $summary->driver->name ?? 'غير محدد' }}</span>
                    <span class="badge badge-success">{{ $summary->total_orders }} {{ app()->getLocale() === 'ar' ? 'طلب' : 'orders' }}</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.95rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">{{ app()->getLocale() === 'ar' ? 'إجمالي النقدية المحصلة:' : 'Total Cash Collected:' }}</span>
                        <strong style="color: var(--success-color);">{{ number_format($summary->total_collected, 2) }} {{ __('messages.currency') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">{{ app()->getLocale() === 'ar' ? 'إجمالي رسوم التوصيل:' : 'Total Delivery Fees:' }}</span>
                        <strong style="color: var(--accent-color);">{{ number_format($summary->total_delivery_fees, 2) }} {{ __('messages.currency') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 8px; margin-top: 4px;">
                        <span style="font-weight: 700;">{{ app()->getLocale() === 'ar' ? 'الصافي للمحل بعد الخصم:' : 'Net Store Income:' }}</span>
                        <strong style="color: var(--text-primary); font-size: 1.05rem;">
                            {{ number_format($summary->total_collected - $summary->total_delivery_fees, 2) }} {{ __('messages.currency') }}
                        </strong>
                    </div>
                </div>
            </div>
        @empty
            <div class="panel" style="padding: 30px; text-align: center; color: var(--text-secondary); grid-column: 1 / -1;">
                {{ app()->getLocale() === 'ar' ? 'لا يوجد طلبات توصيل مسجلة لهذا التاريخ.' : 'No delivery orders recorded for this date.' }}
            </div>
        @endforelse
    </div>

    <!-- Detailed Orders Table -->
    <div class="panel" style="padding: 25px;">
        <h3 style="font-weight: 700; margin-bottom: 15px;">📋 {{ app()->getLocale() === 'ar' ? 'تفاصيل طلبات التوصيل' : 'Delivery Orders Detail' }}</h3>
        @if($orders->isEmpty())
            <div style="text-align: center; padding: 30px; color: var(--text-secondary);">
                {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات للعرض.' : 'No data to display.' }}
            </div>
        @else
            <table class="app-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary);">
                        <th style="padding: 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Order #' }}</th>
                        <th style="padding: 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</th>
                        <th style="padding: 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Customer' }}</th>
                        <th style="padding: 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'الطيار' : 'Driver' }}</th>
                        <th style="padding: 10px; text-align: end;">{{ app()->getLocale() === 'ar' ? 'المبلغ المحصل' : 'Total Collected' }}</th>
                        <th style="padding: 10px; text-align: end;">{{ app()->getLocale() === 'ar' ? 'خدمة التوصيل' : 'Delivery Fee' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $ord)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px; font-weight: 700;">{{ $ord->order_number }}</td>
                            <td style="padding: 12px;">{{ $ord->created_at->format('H:i A') }}</td>
                            <td style="padding: 12px;">{{ $ord->customer->name ?? ($ord->delivery_address ?: '-') }}</td>
                            <td style="padding: 12px; font-weight: 700; color: var(--accent-color);">
                                🛵 {{ $ord->driver->name ?? $ord->driver_name }}
                            </td>
                            <td style="padding: 12px; text-align: end; font-weight: 700; color: var(--success-color);">
                                {{ number_format($ord->total_amount, 2) }} {{ __('messages.currency') }}
                            </td>
                            <td style="padding: 12px; text-align: end; font-weight: 700;">
                                {{ number_format($ord->delivery_fee, 2) }} {{ __('messages.currency') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection
