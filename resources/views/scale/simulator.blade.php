@extends('layouts.app')

@section('title', __('messages.scale_simulator'))
@section('header_title', __('messages.scale_simulator'))

@section('content')
<div class="panel" style="max-width: 600px; margin: 0 auto;">
    <div class="panel-header">
        <h2 class="panel-title">{{ app()->getLocale() === 'ar' ? 'محاكي الميزان الإلكتروني (TM-A)' : 'Electronic Scale Simulator (TM-A)' }}</h2>
    </div>

    <div style="background-color: var(--bg-tertiary); padding: 15px; border-radius: var(--btn-radius); font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 25px; border-inline-start: 4px solid var(--accent-color);">
        <p>
            {{ app()->getLocale() === 'ar' 
                ? 'يستخدم هذا المحاكي لمحاكاة الميزان الإلكتروني الذي يطبع ملصق الباركود (EAN-13) المحتوي على كود الصنف والوزن الإجمالي. قم باختيار منتج وادخل الوزن لتوليد الباركود المناسب لاختبار عملية المسح في كاشير المبيعات.' 
                : 'This simulator generates EAN-13 barcodes printed by digital scales (embedding SKU/PLU and weight in grams). Select a product, enter weight, generate barcode, and copy it to test the POS barcode scanner.'
            }}
        </p>
    </div>

    <form id="scaleForm" style="display: flex; flex-direction: column; gap: 20px;">
        <div class="form-group">
            <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اختر المنتج الموزون' : 'Select Weighed Product' }}</label>
            <select id="productId" class="form-control" required>
                <option value="">-- {{ app()->getLocale() === 'ar' ? 'اختر منتج' : 'Select Product' }} --</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" data-sku="{{ $prod->sku }}" data-price="{{ $prod->price }}" data-name="{{ $prod->name }}" data-type="{{ $prod->pricing_type }}">
                        [{{ $prod->sku }}] {{ $prod->name }} - {{ floatval($prod->price) }} ج.م/{{ $prod->pricing_type === 'weight' ? 'كجم' : 'قطعة' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الوزن بالكيلوجرام (مثال: 1.503)' : 'Weight in Kilograms (e.g. 1.503)' }}</label>
            <input type="number" id="weight" class="form-control" step="0.001" min="0.005" max="30.000" placeholder="1.503" required>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px; font-size: 1rem;">
            ⚖️ {{ app()->getLocale() === 'ar' ? 'توليد باركود الميزان' : 'Generate Scale Barcode' }}
        </button>
    </form>

    <!-- Barcode output card -->
    <div id="barcodeResult" class="panel" style="margin-top: 30px; display: none; text-align: center; border-color: var(--success-color); background-color: var(--success-light); display: none;">
        <h3 style="color: var(--success-color); font-weight: 700; margin-bottom: 10px;">
            {{ app()->getLocale() === 'ar' ? 'تم التوليد بنجاح' : 'Generated Successfully' }}
        </h3>
        
        <!-- Output values -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: start; margin-bottom: 20px; font-size: 0.9rem;">
            <div>
                <strong>{{ app()->getLocale() === 'ar' ? 'المنتج: ' : 'Product: ' }}</strong>
                <span id="resProduct">-</span>
            </div>
            <div>
                <strong>{{ app()->getLocale() === 'ar' ? 'الوزن: ' : 'Weight: ' }}</strong>
                <span id="resWeight">-</span> كجم
            </div>
            <div>
                <strong>{{ app()->getLocale() === 'ar' ? 'سعر الكيلو: ' : 'Price/kg: ' }}</strong>
                <span id="resPrice">-</span> ج.م
            </div>
            <div>
                <strong>{{ app()->getLocale() === 'ar' ? 'السعر الإجمالي: ' : 'Total Price: ' }}</strong>
                <span id="resTotal" style="color:var(--danger-color); font-weight: bold;">-</span> ج.م
            </div>
        </div>

        <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: 15px; border-radius: var(--btn-radius); display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <div style="font-family: 'Courier New', monospace; font-size: 1.8rem; letter-spacing: 4px; font-weight: 700;" id="resBarcode">
                2011135015034
            </div>
            <button type="button" class="btn btn-success" id="btnCopyBarcode" style="padding: 6px 16px; font-size: 0.82rem;">
                📋 {{ app()->getLocale() === 'ar' ? 'نسخ الباركود' : 'Copy Barcode' }}
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const scaleForm = document.getElementById('scaleForm');
    const productIdSelect = document.getElementById('productId');
    const weightInput = document.getElementById('weight');

    const barcodeResult = document.getElementById('barcodeResult');
    const resProduct = document.getElementById('resProduct');
    const resWeight = document.getElementById('resWeight');
    const resPrice = document.getElementById('resPrice');
    const resTotal = document.getElementById('resTotal');
    const resBarcode = document.getElementById('resBarcode');
    const btnCopyBarcode = document.getElementById('btnCopyBarcode');

    scaleForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const selectedOption = productIdSelect.options[productIdSelect.selectedIndex];
        if (!selectedOption.value) return;

        const name = selectedOption.getAttribute('data-name');
        const sku = selectedOption.getAttribute('data-sku'); // e.g. 01113
        const price = parseFloat(selectedOption.getAttribute('data-price'));
        const pricingType = selectedOption.getAttribute('data-type');
        const weight = parseFloat(weightInput.value);

        // 1. Calculate price
        const total = Math.round((weight * price + Number.EPSILON) * 100) / 100;

        // 2. Generate EAN-13 Barcode
        // TM-A scale format: '2' (prefix) + SKU(5 digits, e.g. 01113) + '5' (weight flag) + weight_in_grams(5 digits, e.g. 01503 for 1.503 kg) + checksum(1 digit)
        // PLU SKU code should be exactly 5 digits. Pad with zeros if shorter.
        const skuPadded = sku.padStart(5, '0');
        
        // Weight in grams. 1.503 kg = 1503 g. Pad to 5 digits, e.g., '01503'
        const gramsStr = Math.round(weight * 1000).toString().padStart(5, '0');

        // Let's compile the first 12 digits: E.g., '2' + '01113' + '5' + '01503' = '201113501503'
        const code12 = '2' + skuPadded + '5' + gramsStr;

        // Calculate EAN-13 Check Digit
        const checkDigit = calculateEan13CheckDigit(code12);
        const fullBarcode = code12 + checkDigit;

        // 3. Display Results
        resProduct.innerText = name;
        resWeight.innerText = weight.toFixed(3);
        resPrice.innerText = price.toFixed(2);
        resTotal.innerText = total.toFixed(2);
        resBarcode.innerText = fullBarcode;

        barcodeResult.style.display = 'block';
    });

    btnCopyBarcode.addEventListener('click', () => {
        const text = resBarcode.innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            btnCopyBarcode.innerText = "{{ app()->getLocale() === 'ar' ? 'تم النسخ!' : 'Copied!' }}";
            setTimeout(() => {
                btnCopyBarcode.innerText = "📋 {{ app()->getLocale() === 'ar' ? 'نسخ الباركود' : 'Copy Barcode' }}";
            }, 1500);
        });
    });

    // Helper to calculate EAN-13 check digit
    function calculateEan13CheckDigit(code12) {
        let oddSum = 0;
        let evenSum = 0;

        for (let i = 0; i < 12; i++) {
            const digit = parseInt(code12[i]);
            // EAN-13 is 1-indexed. Index 0 is odd (1st digit), Index 1 is even (2nd digit)
            if (i % 2 === 0) {
                oddSum += digit;
            } else {
                evenSum += digit;
            }
        }

        // Even position digits are multiplied by 3
        const totalSum = oddSum + (evenSum * 3);
        const mod = totalSum % 10;
        
        return mod === 0 ? 0 : 10 - mod;
    }
</script>
@endsection
