<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11pt;
            color: #000;
            background-color: #fff;
            padding: 10px;
            width: 80mm;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .logo {
            display: block;
            margin: 0 auto 10px;
            max-height: 50px;
            object-fit: contain;
            border-radius: 4px;
        }
        
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 9pt;
            color: #555;
            margin-bottom: 15px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            margin-bottom: 3px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            font-size: 8.5pt;
        }

        .items-table td {
            padding: 5px 0;
            font-size: 9pt;
            vertical-align: top;
        }

        .item-details {
            font-size: 7.5pt;
            color: #444;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
            margin-bottom: 4px;
        }

        .total-row.grand-total {
            font-size: 12pt;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 6px;
        }

        .footer {
            margin-top: 20px;
            font-size: 8.5pt;
            text-align: center;
        }

        .qr-placeholder {
            display: flex;
            justify-content: center;
            margin: 15px 0 10px;
        }

        .qr-placeholder svg {
            width: 90px;
            height: 90px;
        }

        @media print {
            body {
                width: 80mm;
                padding: 5px;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Logo -->
    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="logo">

    <!-- Brand Header -->
    <div class="text-center">
        <h2 class="title">{{ __('messages.app_name') }}</h2>
        <p class="subtitle">{{ app()->getLocale() === 'ar' ? 'شركة المواد الغذائية المحدودة' : 'Foodstuffs Co. Ltd.' }}</p>
    </div>

    <div class="divider"></div>

    <!-- Metadata info -->
    <div class="info-row">
        <span>{{ __('messages.order_no') }}:</span>
        <span class="font-bold">{{ $order->order_number }}</span>
    </div>
    <div class="info-row">
        <span>{{ __('messages.date') }}:</span>
        <span>{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
    </div>
    <div class="info-row">
        <span>{{ __('messages.cashier') }}:</span>
        <span>{{ $order->cashier_name ?? ($order->user ? $order->user->name : '-') }}</span>
    </div>

    @if($order->customer)
        <div class="info-row">
            <span>{{ __('messages.customers') }}:</span>
            <span class="font-bold">{{ $order->customer->name }} ({{ $order->customer->phone }})</span>
        </div>
    @endif

    <div class="divider"></div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 55%;" class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                    {{ app()->getLocale() === 'ar' ? 'الصنف' : 'Item' }}
                </th>
                <th style="width: 20%;" class="text-center">
                    {{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}
                </th>
                <th style="width: 25%;" class="{{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">
                    {{ app()->getLocale() === 'ar' ? 'المجموع' : 'Total' }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="font-bold">{{ app()->getLocale() === 'ar' ? $item->product->name_ar : $item->product->name_en }}</div>
                        <div class="item-details">
                            {{ floatval($item->unit_price) }} {{ __('messages.currency') }} / {{ $item->product->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece') }}
                        </div>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        {{ floatval($item->quantity) }}
                    </td>
                    <td class="{{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}" style="vertical-align: middle;">
                        {{ floatval($item->subtotal) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Financial totals -->
    <div class="total-row">
        <span>{{ __('messages.subtotal') }}:</span>
        <span>{{ floatval($order->total_amount + $order->discount_amount) }} {{ __('messages.currency') }}</span>
    </div>
    @if($order->discount_amount > 0)
        <div class="total-row" style="color: red;">
            <span>{{ __('messages.discount') }}:</span>
            <span>-{{ floatval($order->discount_amount) }} {{ __('messages.currency') }}</span>
        </div>
    @endif

    <div class="total-row grand-total">
        <span>{{ __('messages.total') }}:</span>
        <span>{{ floatval($order->total_amount) }} {{ __('messages.currency') }}</span>
    </div>

    <div class="divider"></div>

    <!-- Payment details -->
    <div class="info-row">
        <span>{{ __('messages.payment_method') }}:</span>
        <span class="font-bold">
            @if($order->payment_method === 'cash')
                {{ __('messages.cash') }}
            @elseif($order->payment_method === 'card')
                {{ __('messages.card') }}
            @elseif($order->payment_method === 'credit')
                {{ __('messages.credit') }}
            @endif
        </span>
    </div>

    <!-- Static Barcode / QR Simulation for Thermal Paper -->
    <div class="qr-placeholder text-center">
        <!-- SVG mock QR code representation -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <rect width="100" height="100" fill="none"/>
            <!-- Outline border -->
            <path d="M0,0 h25 v5 h-20 v20 h-5 z" fill="#000"/>
            <path d="M100,0 h-25 v5 h20 v20 h5 z" fill="#000"/>
            <path d="M0,100 h25 v-5 h-20 v-20 h-5 z" fill="#000"/>
            <path d="M100,100 h-25 v-5 h20 v-20 h5 z" fill="#000"/>
            <!-- Top-Left Finder Pattern -->
            <rect x="10" y="10" width="25" height="25" fill="#000"/>
            <rect x="15" y="15" width="15" height="15" fill="#fff"/>
            <rect x="18" y="18" width="9" height="9" fill="#000"/>
            <!-- Top-Right Finder Pattern -->
            <rect x="65" y="10" width="25" height="25" fill="#000"/>
            <rect x="70" y="15" width="15" height="15" fill="#fff"/>
            <rect x="73" y="18" width="9" height="9" fill="#000"/>
            <!-- Bottom-Left Finder Pattern -->
            <rect x="10" y="65" width="25" height="25" fill="#000"/>
            <rect x="15" y="70" width="15" height="15" fill="#fff"/>
            <rect x="18" y="73" width="9" height="9" fill="#000"/>
            <!-- Small Bottom-Right Alignment pattern -->
            <rect x="75" y="75" width="10" height="10" fill="#000"/>
            <rect x="77" y="77" width="6" height="6" fill="#fff"/>
            <rect x="79" y="79" width="2" height="2" fill="#000"/>
            <!-- QR Data Bits -->
            <rect x="42" y="15" width="5" height="5" fill="#000"/>
            <rect x="48" y="22" width="5" height="5" fill="#000"/>
            <rect x="55" y="12" width="5" height="5" fill="#000"/>
            <rect x="45" y="35" width="5" height="5" fill="#000"/>
            <rect x="52" y="32" width="5" height="5" fill="#000"/>
            <rect x="12" y="45" width="5" height="5" fill="#000"/>
            <rect x="22" y="52" width="5" height="5" fill="#000"/>
            <rect x="35" y="45" width="5" height="5" fill="#000"/>
            <rect x="32" y="52" width="5" height="5" fill="#000"/>
            <rect x="48" y="48" width="5" height="5" fill="#000"/>
            <rect x="55" y="42" width="5" height="5" fill="#000"/>
            <rect x="42" y="65" width="5" height="5" fill="#000"/>
            <rect x="55" y="68" width="5" height="5" fill="#000"/>
            <rect x="48" y="75" width="5" height="5" fill="#000"/>
            <rect x="68" y="42" width="5" height="5" fill="#000"/>
            <rect x="75" y="48" width="5" height="5" fill="#000"/>
            <rect x="72" y="55" width="5" height="5" fill="#000"/>
            <rect x="85" y="42" width="5" height="5" fill="#000"/>
            <rect x="82" y="62" width="5" height="5" fill="#000"/>
        </svg>
    </div>

    <!-- Thank you note -->
    <div class="footer">
        <p>{{ app()->getLocale() === 'ar' ? 'شكراً لزيارتكم!' : 'Thank you for your visit!' }}</p>
        <p style="margin-top: 5px; font-size: 7.5pt; color: #555;">Takamul Systems POS</p>
    </div>

    <!-- Trigger print dialog automatically -->
    <script>
        window.addEventListener('load', () => {
            window.print();
            // Automatically close window after print dialog is closed/completed
            setTimeout(() => {
                window.close();
            }, 1000);
        });
    </script>
</body>
</html>
