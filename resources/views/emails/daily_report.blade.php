<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المبيعات اليومي</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; color: #0f172a; padding: 20px; direction: rtl; text-align: right;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
            <h2 style="margin: 0; color: #7c3aed; font-size: 1.6rem; font-weight: bold;">{{ __('messages.app_name') }}</h2>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">تقرير مبيعات اليوم المستند على النظام | Daily Sales Summary</p>
        </div>

        <!-- Date indicator -->
        <div style="background-color: #f5f3ff; border: 1px solid #ddd6fe; color: #7c3aed; padding: 12px 18px; border-radius: 8px; font-weight: bold; margin-bottom: 25px; font-size: 1.05rem;">
            📅 تاريخ التقرير: {{ $stats['date'] }}
        </div>

        <!-- Core Financials -->
        <h3 style="color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">📊 الأداء المالي اليومي</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
            <tr>
                <td style="padding: 10px 0; color: #64748b; border-bottom: 1px solid #f1f5f9;">إجمالي المبيعات (الصافي):</td>
                <td style="padding: 10px 0; font-weight: bold; color: #10b981; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 1.15rem;">
                    {{ floatval($stats['total_sales']) }} ج.م
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #64748b; border-bottom: 1px solid #f1f5f9;">عدد الفواتير المصادرة:</td>
                <td style="padding: 10px 0; font-weight: bold; text-align: left; border-bottom: 1px solid #f1f5f9;">
                    {{ $stats['total_orders'] }} فاتورة
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #64748b; border-bottom: 1px solid #f1f5f9;">الخصومات الممنوحة:</td>
                <td style="padding: 10px 0; font-weight: bold; color: #ef4444; text-align: left; border-bottom: 1px solid #f1f5f9;">
                    {{ floatval($stats['total_discounts']) }} ج.م
                </td>
            </tr>
        </table>

        <!-- Payment Breakdown -->
        <h3 style="color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">💳 طرق الدفع والتسوية</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
            <tr>
                <td style="padding: 8px 0; color: #64748b;">💵 الدفع النقدي (كاش):</td>
                <td style="padding: 8px 0; font-weight: bold; text-align: left;">{{ floatval($stats['cash_sales']) }} ج.م</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b;">💳 الدفع بالبطاقة (شبكة/فيزا):</td>
                <td style="padding: 8px 0; font-weight: bold; text-align: left;">{{ floatval($stats['card_sales']) }} ج.م</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b;">📝 البيع بالآجل (شُكك/دين):</td>
                <td style="padding: 8px 0; font-weight: bold; color: #ef4444; text-align: left;">{{ floatval($stats['credit_sales']) }} ج.م</td>
            </tr>
        </table>

        <!-- Top Selling Products -->
        <h3 style="color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">🥩 الأصناف الأكثر مبيعاً اليوم</h3>
        @if(count($stats['top_products']) > 0)
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: #f1f5f9;">
                        <th style="padding: 8px; text-align: right; border-bottom: 2px solid #cbd5e1;">الصنف</th>
                        <th style="padding: 8px; text-align: center; border-bottom: 2px solid #cbd5e1;">الكمية المباعة</th>
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #cbd5e1;">الإيراد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['top_products'] as $item)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">
                                <strong>{{ $item['name_ar'] }}</strong><br>
                                <span style="font-size:0.75rem; color:#64748b;">PLU: {{ $item['sku'] }}</span>
                            </td>
                            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #e2e8f0;">
                                {{ floatval($item['qty']) }} {{ $item['type'] === 'weight' ? 'كجم' : 'قطع' }}
                            </td>
                            <td style="padding: 8px; text-align: left; border-bottom: 1px solid #e2e8f0; font-weight: bold;">
                                {{ floatval($item['total']) }} ج.م
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #64748b; font-style: italic; margin-bottom: 25px;">لا توجد تفاصيل مبيعات أصناف اليوم.</p>
        @endif

        <!-- Low Stock Warnings -->
        @if(count($stats['low_stock']) > 0)
            <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                <h4 style="color: #ef4444; margin: 0 0 10px; font-weight: bold; font-size: 1rem;">⚠️ تنبيهات نقص المخزون (تحت 5 كجم/قطع)</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    @foreach($stats['low_stock'] as $p)
                        <tr>
                            <td style="padding: 5px 0; color: #7f1d1d;">🥩 {{ $p['name_ar'] }} (PLU: {{ $p['sku'] }})</td>
                            <td style="padding: 5px 0; font-weight: bold; text-align: left; color: #b91c1c;">
                                المتبقي: {{ floatval($p['stock']) }} {{ $p['type'] === 'weight' ? 'كجم' : 'قطع' }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 0.8rem; color: #94a3b8;">
            <p>تم إرسال هذا التقرير تلقائياً من نظام التكامل للمبيعات.</p>
            <p style="margin-top: 4px;">{{ date('Y-m-d H:i:s') }} | Africa/Cairo Time</p>
        </div>
        
    </div>

</body>
</html>
