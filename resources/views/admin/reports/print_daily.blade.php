<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'تقرير المبيعات اليومي - ' . $dateStr : 'Daily Sales Report - ' . $dateStr }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Cairo:wght@300;400;700;900&display=swap');

        :root {
            --primary-color: #4f46e5;
            --text-color: #1f2937;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', sans-serif" : "'Outfit', sans-serif" }};
        }

        body {
            font-family: var(--font-family);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
            background-color: #ffffff;
        }

        /* Container designed for A4 size */
        .print-container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header section */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .report-title-container h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #111827;
        }

        .report-title-container p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .report-meta {
            text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};
        }

        .report-meta div {
            margin-bottom: 4px;
        }

        .report-meta strong {
            color: #4b5563;
        }

        /* Summary Stats Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-card-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .stat-card-value {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        /* Payment breakdown panel */
        .payment-breakdown {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 35px;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
        }

        .payment-item {
            text-align: center;
        }

        .payment-item-title {
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .payment-item-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        /* Section titles */
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 15px 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        /* Table styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .report-table th {
            background-color: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            padding: 10px 12px;
            font-weight: 700;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            color: #374151;
        }

        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            color: #4b5563;
        }

        .report-table tr:last-child td {
            border-bottom: none;
        }

        /* Custom alignment for tables */
        .text-right {
            text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }} !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* Print optimization */
        @media print {
            body {
                padding: 0;
                background-color: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                max-width: 100%;
                width: 100%;
            }
            .stat-card {
                background-color: #ffffff !important;
                border: 1px solid #9ca3af !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .payment-breakdown {
                background-color: #ffffff !important;
                border: 1px solid #9ca3af !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Control bar */
        .control-bar {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .control-btn {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .control-btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .control-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <!-- Control bar (hidden when printed) -->
    <div class="print-container no-print">
        <div class="control-bar">
            <span>{{ app()->getLocale() === 'ar' ? 'جاهز للطباعة على ورقة A4' : 'Ready to print on A4 sheet' }}</span>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="control-btn">
                    🖨️ {{ app()->getLocale() === 'ar' ? 'بدء الطباعة' : 'Print Now' }}
                </button>
                <button onclick="window.close()" class="control-btn control-btn-secondary">
                    ❌ {{ app()->getLocale() === 'ar' ? 'إغلاق النافذة' : 'Close' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Print Canvas -->
    <div class="print-container">
        
        <!-- Header -->
        <div class="report-header">
            <div class="report-title-container">
                <h1>{{ app()->getLocale() === 'ar' ? 'تقرير المبيعات اليومي' : 'Daily Sales Report' }}</h1>
                <p>{{ app()->getLocale() === 'ar' ? 'ملحمة التكامل لتقطيع وتصنيف اللحوم' : 'Al-Takamul Butcher' }}</p>
            </div>
            
            <div class="report-meta">
                <div><strong>{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</strong> {{ $dateStr }}</div>
                <div><strong>{{ app()->getLocale() === 'ar' ? 'وقت التصدير:' : 'Export Time:' }}</strong> {{ now()->setTimezone('Africa/Cairo')->format('Y-m-d H:i:s') }}</div>
                <div><strong>{{ app()->getLocale() === 'ar' ? 'الجهة:' : 'Sender:' }}</strong> {{ auth()->user()->name }}</div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">{{ app()->getLocale() === 'ar' ? 'إجمالي المبيعات' : 'Total Revenue' }}</div>
                <div class="stat-card-value" style="color: var(--primary-color);">{{ floatval($totalSales) }} ج.م</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">{{ app()->getLocale() === 'ar' ? 'عدد الفواتير' : 'Invoices Count' }}</div>
                <div class="stat-card-value">{{ $totalOrders }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">{{ app()->getLocale() === 'ar' ? 'إجمالي الخصومات' : 'Total Discounts' }}</div>
                <div class="stat-card-value" style="color: #dc2626;">{{ floatval($totalDiscounts) }} ج.م</div>
            </div>
        </div>

        <!-- Payment Breakdown -->
        <h3 class="section-title">📊 {{ app()->getLocale() === 'ar' ? 'تفصيل طرق الدفع' : 'Payment Methods Breakdown' }}</h3>
        <div class="payment-breakdown">
            <div class="payment-item">
                <div class="payment-item-title">💵 {{ app()->getLocale() === 'ar' ? 'نقدي (كاش)' : 'Cash' }}</div>
                <div class="payment-item-value">{{ floatval($cashSales) }} ج.م</div>
            </div>
            <div class="payment-item">
                <div class="payment-item-title">💳 {{ app()->getLocale() === 'ar' ? 'شبكة (بطاقة)' : 'Card' }}</div>
                <div class="payment-item-value">{{ floatval($cardSales) }} ج.m</div>
            </div>
            <div class="payment-item">
                <div class="payment-item-title">📝 {{ app()->getLocale() === 'ar' ? 'آجل (شكك)' : 'Credit' }}</div>
                <div class="payment-item-value">{{ floatval($creditSales) }} ج.م</div>
            </div>
        </div>

        <!-- Top Products Table -->
        <h3 class="section-title">🏆 {{ app()->getLocale() === 'ar' ? 'المنتجات الأكثر مبيعاً اليوم' : 'Top Products Sold Today' }}</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'اسم الصنف' : 'Product Name' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الباركود/PLU' : 'SKU' }}</th>
                    <th class="text-center">{{ app()->getLocale() === 'ar' ? 'الكمية/الوزن المباع' : 'Qty/Weight Sold' }}</th>
                    <th class="text-right">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيراد' : 'Total Revenue' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: 600;">
                            {{ app()->getLocale() === 'ar' ? ($item->product ? $item->product->name_ar : 'صنف محذوف') : ($item->product ? $item->product->name_en : 'Deleted Product') }}
                        </td>
                        <td>{{ $item->product ? $item->product->sku : '-' }}</td>
                        <td class="text-center">
                            {{ floatval($item->total_qty) }}
                            <span style="font-size: 0.8rem; color: #6b7280;">
                                {{ $item->product ? ($item->product->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece')) : '' }}
                            </span>
                        </td>
                        <td class="text-right" style="font-weight: 700;">{{ floatval($item->total_subtotal) }} ج.م</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color: #9ca3af; padding: 20px;">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد مبيعات مسجلة اليوم.' : 'No sales recorded today.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Low Stock warning section -->
        <h3 class="section-title">⚠️ {{ app()->getLocale() === 'ar' ? 'تنبيهات انخفاض المخزون (أقل من 5)' : 'Low Stock Warning (Below 5)' }}</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>{{ app()->getLocale() === 'ar' ? 'اسم الصنف' : 'Product Name' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الباركود/PLU' : 'SKU' }}</th>
                    <th class="text-center">{{ app()->getLocale() === 'ar' ? 'المخزون المتبقي' : 'Remaining Stock' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockProducts as $p)
                    <tr>
                        <td style="font-weight: 600; color: #b91c1c;">
                            {{ app()->getLocale() === 'ar' ? $p->name_ar : $p->name_en }}
                        </td>
                        <td>{{ $p->sku }}</td>
                        <td class="text-center" style="font-weight: 700; color: #b91c1c;">
                            {{ floatval($p->stock) }}
                            <span style="font-size: 0.8rem; color: #6b7280;">
                                {{ $p->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center" style="color: #10b981; font-weight: 600; padding: 15px;">
                            ✓ {{ app()->getLocale() === 'ar' ? 'جميع مستويات المخزون ممتازة ولا توجد أصناف منخفضة!' : 'All stock levels are excellent!' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Area -->
        <div style="margin-top: 50px; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border-color); padding-top: 30px;">
            <div style="text-align: center; width: 200px;">
                <p style="margin: 0; font-size: 13px; color: #6b7280;">{{ app()->getLocale() === 'ar' ? 'توقيع الكاشير/المسؤول' : 'Cashier/Responsible Signature' }}</p>
                <div style="margin-top: 35px; border-bottom: 1px solid #9ca3af; width: 100%;"></div>
            </div>
            <div style="text-align: center; width: 200px;">
                <p style="margin: 0; font-size: 13px; color: #6b7280;">{{ app()->getLocale() === 'ar' ? 'اعتماد المدير' : 'Manager Approval' }}</p>
                <div style="margin-top: 35px; border-bottom: 1px solid #9ca3af; width: 100%;"></div>
            </div>
        </div>

    </div>

    <!-- Automatically open print dialog -->
    <script>
        window.addEventListener('load', () => {
            // Short delay to allow fonts and layouts to render
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
