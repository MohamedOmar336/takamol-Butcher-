@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تقرير مبيعات الأوزان واللحوم' : 'Weighed Products Sales Report')
@section('header_title', app()->getLocale() === 'ar' ? 'تقرير مبيعات الأصناف الموزونة بالكيلوجرام' : 'Weighed Products & Kilos Sales Report')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Filter & Date Quick Selectors -->
    <div class="panel" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.reports.weighed_products') }}" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'تاريخ البداية' : 'Start Date' }}</label>
                <input type="date" name="start_date" id="inputStartDate" class="form-control" value="{{ $startDate }}" required>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'تاريخ النهاية' : 'End Date' }}</label>
                <input type="date" name="end_date" id="inputEndDate" class="form-control" value="{{ $endDate }}" required>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">
                    🔍 {{ app()->getLocale() === 'ar' ? 'تصفية التقرير' : 'Filter' }}
                </button>
            </div>
        </form>

        <!-- Quick Preset Buttons -->
        <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; border-top: 1px dashed var(--border-color); padding-top: 12px;">
            <span style="font-size: 0.88rem; color: var(--text-secondary); align-self: center; font-weight: 600;">
                ⚡ {{ app()->getLocale() === 'ar' ? 'اختصار الفترة:' : 'Quick Select:' }}
            </span>

            @php
                $lastMonthStart = \Carbon\Carbon::now('Africa/Cairo')->subMonth()->startOfMonth()->format('Y-m-d');
                $lastMonthEnd = \Carbon\Carbon::now('Africa/Cairo')->subMonth()->endOfMonth()->format('Y-m-d');

                $thisMonthStart = \Carbon\Carbon::now('Africa/Cairo')->startOfMonth()->format('Y-m-d');
                $thisMonthEnd = \Carbon\Carbon::now('Africa/Cairo')->format('Y-m-d');

                $last30DaysStart = \Carbon\Carbon::now('Africa/Cairo')->subDays(30)->format('Y-m-d');
                $todayStr = \Carbon\Carbon::now('Africa/Cairo')->format('Y-m-d');
            @endphp

            <a href="{{ route('admin.reports.weighed_products', ['start_date' => $lastMonthStart, 'end_date' => $lastMonthEnd]) }}" 
               class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; font-weight: 700; {{ $startDate === $lastMonthStart && $endDate === $lastMonthEnd ? 'border-color: var(--accent-color); color: var(--accent-color); background-color: var(--accent-light);' : '' }}">
                📅 {{ app()->getLocale() === 'ar' ? 'الشهر الماضي' : 'Last Month' }} ({{ \Carbon\Carbon::parse($lastMonthStart)->translatedFormat('F Y') }})
            </a>

            <a href="{{ route('admin.reports.weighed_products', ['start_date' => $thisMonthStart, 'end_date' => $thisMonthEnd]) }}" 
               class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; font-weight: 700; {{ $startDate === $thisMonthStart && $endDate === $thisMonthEnd ? 'border-color: var(--accent-color); color: var(--accent-color); background-color: var(--accent-light);' : '' }}">
                📅 {{ app()->getLocale() === 'ar' ? 'الشهر الحالي' : 'This Month' }}
            </a>

            <a href="{{ route('admin.reports.weighed_products', ['start_date' => $last30DaysStart, 'end_date' => $todayStr]) }}" 
               class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; font-weight: 700; {{ $startDate === $last30DaysStart && $endDate === $todayStr ? 'border-color: var(--accent-color); color: var(--accent-color); background-color: var(--accent-light);' : '' }}">
                🗓️ {{ app()->getLocale() === 'ar' ? 'آخر 30 يوم' : 'Last 30 Days' }}
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <!-- Total Kilograms Sold -->
        <div class="panel" style="padding: 20px; border-inline-start: 5px solid var(--accent-color); display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 2.8rem;">⚖️</div>
            <div>
                <div style="font-size: 0.88rem; color: var(--text-secondary); font-weight: 600;">
                    {{ app()->getLocale() === 'ar' ? 'إجمالي الكيلوجرامات المباعة' : 'Total Kilograms Sold' }}
                </div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--accent-color);">
                    {{ number_format($totalKgSold, 3) }} <span style="font-size: 1rem;">{{ __('messages.kg') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="panel" style="padding: 20px; border-inline-start: 5px solid var(--success-color); display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 2.8rem;">💵</div>
            <div>
                <div style="font-size: 0.88rem; color: var(--text-secondary); font-weight: 600;">
                    {{ app()->getLocale() === 'ar' ? 'إجمالي إيرادات اللحوم الموزونة' : 'Total Weighed Revenue' }}
                </div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--success-color);">
                    {{ number_format($totalRevenue, 2) }} <span style="font-size: 1rem;">{{ __('messages.currency') }}</span>
                </div>
            </div>
        </div>

        <!-- Distinct Orders Count -->
        <div class="panel" style="padding: 20px; border-inline-start: 5px solid #2563eb; display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 2.8rem;">🧾</div>
            <div>
                <div style="font-size: 0.88rem; color: var(--text-secondary); font-weight: 600;">
                    {{ app()->getLocale() === 'ar' ? 'عدد فواتير الأوزان' : 'Weighed Orders Count' }}
                </div>
                <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb;">
                    {{ number_format($totalWeighedOrders) }} <span style="font-size: 1rem;">{{ app()->getLocale() === 'ar' ? 'فاتورة' : 'orders' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table of Weighed Products -->
    <div class="panel" style="padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 800; font-size: 1.15rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>🥩</span>
                {{ app()->getLocale() === 'ar' ? 'تفاصيل الأصناف الموزونة والمبيعات بالكيلو' : 'Itemized Kilos & Revenue Breakdown' }}
            </h3>
            <span class="badge badge-success" style="font-size: 0.9rem; padding: 6px 12px;">
                {{ $weighedSales->count() }} {{ app()->getLocale() === 'ar' ? 'صنف موزون' : 'weighed items' }}
            </span>
        </div>

        @if($weighedSales->isEmpty())
            <div style="text-align: center; padding: 50px; color: var(--text-secondary);">
                <div style="font-size: 3.5rem; margin-bottom: 10px;">⚖️</div>
                {{ app()->getLocale() === 'ar' ? 'لا توجد مبيعات للأصناف الموزونة في الفترة المحددة.' : 'No weighed product sales recorded for the selected date range.' }}
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="app-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.88rem;">
                            <th style="padding: 12px; text-align: start;">#</th>
                            <th style="padding: 12px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'باركود PLU' : 'PLU Barcode' }}</th>
                            <th style="padding: 12px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'اسم صنف اللحوم / المنتج' : 'Product Name' }}</th>
                            <th style="padding: 12px; text-align: end;">{{ app()->getLocale() === 'ar' ? 'سعر الكيلو' : 'Price / kg' }}</th>
                            <th style="padding: 12px; text-align: center;">{{ app()->getLocale() === 'ar' ? 'إجمالي الوزن المباع' : 'Total Kilos Sold' }}</th>
                            <th style="padding: 12px; text-align: center;">{{ app()->getLocale() === 'ar' ? 'عدد الطلبات' : 'Orders Count' }}</th>
                            <th style="padding: 12px; text-align: end;">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيراد' : 'Total Revenue' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weighedSales as $item)
                            <tr style="border-bottom: 1px solid var(--border-color); font-size: 0.95rem;">
                                <td style="padding: 14px; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="padding: 14px; font-family: monospace; font-weight: 700; color: var(--text-secondary);">
                                    {{ $item->sku }}
                                </td>
                                <td style="padding: 14px; font-weight: 800; color: var(--text-primary);">
                                    🥩 {{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}
                                </td>
                                <td style="padding: 14px; text-align: end; font-weight: 600;">
                                    {{ number_format($item->unit_price, 2) }} {{ __('messages.currency') }}
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <span style="font-weight: 800; font-size: 1.05rem; color: var(--accent-color); background-color: var(--accent-light); padding: 4px 10px; border-radius: 6px; display: inline-block;">
                                        ⚖️ {{ number_format($item->total_weight_kg, 3) }} {{ __('messages.kg') }}
                                    </span>
                                </td>
                                <td style="padding: 14px; text-align: center; font-weight: 700;">
                                    {{ number_format($item->orders_count) }}
                                </td>
                                <td style="padding: 14px; text-align: end; font-weight: 800; color: var(--success-color); font-size: 1.05rem;">
                                    {{ number_format($item->total_revenue, 2) }} {{ __('messages.currency') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--border-color); background-color: var(--bg-tertiary); font-weight: 800;">
                            <td colspan="4" style="padding: 16px; text-align: start; font-size: 1rem;">
                                {{ app()->getLocale() === 'ar' ? 'الإجمالي العام لمبيعات الكيلوجرامات:' : 'Total Weighed Kilos Summary:' }}
                            </td>
                            <td style="padding: 16px; text-align: center; color: var(--accent-color); font-size: 1.15rem;">
                                ⚖️ {{ number_format($totalKgSold, 3) }} {{ __('messages.kg') }}
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                {{ number_format($totalWeighedOrders) }}
                            </td>
                            <td style="padding: 16px; text-align: end; color: var(--success-color); font-size: 1.15rem;">
                                {{ number_format($totalRevenue, 2) }} {{ __('messages.currency') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
