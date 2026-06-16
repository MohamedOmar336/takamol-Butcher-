@extends('layouts.app')

@section('title', __('messages.pos'))
@section('header_title', __('messages.pos') . ' - ' . __('messages.cashier'))

@section('styles')
<style>
    /* Styling overrides or additions for POS */
    .pos-scan-bar input {
        border-color: var(--accent-color);
        background-color: var(--accent-light);
    }
    .pos-scan-bar input::placeholder {
        color: var(--text-secondary);
        opacity: 0.7;
    }
    .product-badge-type {
        font-size: 0.68rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        background-color: var(--accent-light);
        color: var(--accent-color);
    }
</style>
@endsection

@section('content')
<div class="pos-layout">
    
    <!-- Left Column: Search, Categories, Products Grid -->
    <div class="pos-main">
        <!-- Scanners and search bars -->
        <div class="pos-scan-bar">
            <div class="form-group" style="margin-bottom: 0; position: relative;">
                <input type="text" id="usbScannerInput" class="form-control" placeholder="{{ __('messages.scan_scale_code') }}" autocomplete="off" autofocus>
                <span style="position: absolute; left: 15px; top: 12px; font-size: 1.1rem; pointer-events: none;">🏷️</span>
            </div>
            <div class="form-group" style="margin-bottom: 0; position: relative;">
                <input type="text" id="productSearchInput" class="form-control" placeholder="{{ __('messages.search_product') }}" autocomplete="off">
                <span style="position: absolute; left: 15px; top: 12px; font-size: 1.1rem; pointer-events: none;">🔍</span>
            </div>
        </div>

        <!-- Categories horizontal tabs -->
        <ul class="categories-tabs">
            <li class="category-tab active" data-category-id="all">
                {{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}
            </li>
            @foreach($categories as $category)
                <li class="category-tab" data-category-id="{{ $category->id }}">
                    {{ app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en }}
                </li>
            @endforeach
        </ul>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            @foreach($products as $product)
                <div class="product-card {{ $product->stock < 5 ? 'low-stock' : '' }}" 
                     data-id="{{ $product->id }}"
                     data-category-id="{{ $product->category_id }}"
                     data-name="{{ $product->name }}"
                     data-sku="{{ $product->sku }}"
                     data-price="{{ $product->price }}"
                     data-pricing-type="{{ $product->pricing_type }}"
                     data-stock="{{ $product->stock }}">
                    
                    <span class="product-card-stock">
                        {{ floatval($product->stock) }} {{ $product->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece') }}
                    </span>

                    <div>
                        <div class="product-card-title">{{ $product->name }}</div>
                        <div class="product-card-sku">PLU: {{ $product->sku }}</div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <span class="product-badge-type">
                            {{ $product->pricing_type === 'weight' ? __('messages.pricing_by_weight') : __('messages.pricing_by_piece') }}
                        </span>
                        <span class="product-card-price">
                            {{ floatval($product->price) }} <span class="product-card-unit">{{ __('messages.currency') }}</span>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Column: Active Cart -->
    <div class="pos-sidebar">
        <div class="cart-header">
            <span>{{ __('messages.cart') }}</span>
            <span id="cartCountBadge" class="badge badge-success">0</span>
        </div>

        <!-- Cart items list (Scrollable) -->
        <div class="cart-items" id="cartItemsContainer">
            <!-- Empty state -->
            <div class="cart-empty-state" id="cartEmptyState">
                <span>🛒</span>
                <p>{{ __('messages.empty_cart') }}</p>
            </div>
        </div>

        <!-- Customer Linking Area -->
        <div class="cart-customer-section">
            <div id="linkedCustomerContainer" style="display: none;">
                <div class="linked-customer-badge">
                    <span>👤 <strong id="linkedCustomerName">-</strong> (<span id="linkedCustomerPhone">-</span>)</span>
                    <button type="button" id="btnRemoveCustomer">✕</button>
                </div>
                <!-- Customer debt ledger preview -->
                <div id="customerDebtPreview" style="font-size: 0.72rem; color: var(--danger-color); font-weight: bold; margin-top: 4px; display: none;">
                    {{ app()->getLocale() === 'ar' ? 'المديونية الحالية: ' : 'Current Debt: ' }} <span id="customerDebtAmount">0.00</span> {{ __('messages.currency') }}
                    / {{ app()->getLocale() === 'ar' ? 'الحد الائتماني: ' : 'Credit Limit: ' }} <span id="customerDebtLimit">0.00</span> {{ __('messages.currency') }}
                </div>
            </div>

            <div id="customerSearchContainer" style="display: flex; gap: 8px;">
                <div style="position: relative; flex-grow: 1;">
                    <input type="text" id="customerSearchInput" class="form-control" style="padding: 8px 12px; font-size: 0.85rem;" placeholder="{{ __('messages.search_customer_placeholder') }}" autocomplete="off">
                    <ul class="customer-search-results" id="customerSearchResults"></ul>
                </div>
                <button type="button" id="btnOpenAddCustomerModal" class="btn btn-secondary" style="padding: 8px 12px;" title="{{ __('messages.quick_add_customer') }}">➕</button>
            </div>
        </div>

        <!-- Cart Totals & Payments -->
        <div class="cart-pricing-section">
            <div class="pricing-row">
                <span>{{ __('messages.subtotal') }}</span>
                <span id="cartSubtotal">0.00 {{ __('messages.currency') }}</span>
            </div>
            
            <div class="pricing-row">
                <span>{{ __('messages.discount') }}</span>
                <input type="number" id="discountInput" class="form-control" style="width: 100px; padding: 4px 8px; text-align: center; font-size: 0.85rem;" value="0" min="0" step="1">
            </div>

            <div class="pricing-row total">
                <span>{{ __('messages.total') }}</span>
                <span id="cartTotal">0.00 {{ __('messages.currency') }}</span>
            </div>

            <!-- Payment Method Grid -->
            <label class="form-label" style="margin-bottom: 4px; margin-top: 10px;">{{ __('messages.payment_method') }}</label>
            <div class="payment-grid">
                <button type="button" class="payment-btn active" data-method="cash">
                    <span style="font-size: 1.2rem;">💵</span>
                    <span>{{ __('messages.cash') }}</span>
                </button>
                <button type="button" class="payment-btn" data-method="card">
                    <span style="font-size: 1.2rem;">💳</span>
                    <span>{{ __('messages.card') }}</span>
                </button>
                <button type="button" class="payment-btn" data-method="credit">
                    <span style="font-size: 1.2rem;">📝</span>
                    <span>{{ __('messages.credit') }}</span>
                </button>
            </div>

            <button type="button" id="btnCheckout" class="checkout-btn">
                {{ __('messages.checkout') }}
            </button>
        </div>
    </div>
</div>

<!-- Modal: Quick Add Customer -->
<div class="modal-backdrop" id="addCustomerModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.quick_add_customer') }}</span>
            <button class="modal-close" id="btnCloseAddCustomerModal">×</button>
        </div>
        <form id="quickAddCustomerForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_name') }} *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_phone') }} *</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_address') }}</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.credit_limit') }} ({{ __('messages.currency') }})</label>
                    <input type="number" name="credit_limit" class="form-control" value="1000" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelAddCustomerModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Weight Input (For weighed items clicked manually) -->
<div class="modal-backdrop" id="weightInputModal">
    <div class="modal-card" style="max-width: 380px;">
        <div class="modal-header">
            <span id="weightModalTitle">{{ app()->getLocale() === 'ar' ? 'إدخال وزن اللحوم' : 'Enter Meat Weight' }}</span>
            <button class="modal-close" id="btnCloseWeightModal">×</button>
        </div>
        <form id="weightModalForm">
            <div class="modal-body">
                <div style="text-align: center; margin-bottom: 15px;">
                    <h3 id="weightModalProductName" style="font-weight: 700;">-</h3>
                    <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 4px;">
                        {{ app()->getLocale() === 'ar' ? 'سعر الكيلو: ' : 'Price/kg: ' }} <span id="weightModalProductPrice">0.00</span> {{ __('messages.currency') }}
                    </p>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الوزن بالكيلوجرام (مثال: 1.250)' : 'Weight in kg (e.g. 1.250)' }} *</label>
                    <input type="number" id="manualWeightInput" class="form-control" style="font-size: 1.5rem; text-align: center; font-weight: 700;" step="0.001" min="0.005" required autofocus>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">{{ __('messages.add') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Cart Data Structure
    let cart = [];
    let linkedCustomer = null;
    let activePaymentMethod = 'cash';
    let currentSelectedProduct = null; // For weight input modal

    // DOM Elements
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');
    const categoryTabs = document.querySelectorAll('.category-tab');
    const productSearchInput = document.getElementById('productSearchInput');
    const usbScannerInput = document.getElementById('usbScannerInput');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartEmptyState = document.getElementById('cartEmptyState');
    const cartCountBadge = document.getElementById('cartCountBadge');
    
    const cartSubtotal = document.getElementById('cartSubtotal');
    const discountInput = document.getElementById('discountInput');
    const cartTotal = document.getElementById('cartTotal');
    const paymentBtns = document.querySelectorAll('.payment-btn');
    const btnCheckout = document.getElementById('btnCheckout');

    // Customer Elements
    const customerSearchInput = document.getElementById('customerSearchInput');
    const customerSearchResults = document.getElementById('customerSearchResults');
    const linkedCustomerContainer = document.getElementById('linkedCustomerContainer');
    const customerSearchContainer = document.getElementById('customerSearchContainer');
    const linkedCustomerName = document.getElementById('linkedCustomerName');
    const linkedCustomerPhone = document.getElementById('linkedCustomerPhone');
    const btnRemoveCustomer = document.getElementById('btnRemoveCustomer');
    const customerDebtPreview = document.getElementById('customerDebtPreview');
    const customerDebtAmount = document.getElementById('customerDebtAmount');
    const customerDebtLimit = document.getElementById('customerDebtLimit');

    // Modals
    const addCustomerModal = document.getElementById('addCustomerModal');
    const btnOpenAddCustomerModal = document.getElementById('btnOpenAddCustomerModal');
    const btnCloseAddCustomerModal = document.getElementById('btnCloseAddCustomerModal');
    const btnCancelAddCustomerModal = document.getElementById('btnCancelAddCustomerModal');
    const quickAddCustomerForm = document.getElementById('quickAddCustomerForm');

    const weightInputModal = document.getElementById('weightInputModal');
    const btnCloseWeightModal = document.getElementById('btnCloseWeightModal');
    const weightModalForm = document.getElementById('weightModalForm');
    const weightModalProductName = document.getElementById('weightModalProductName');
    const weightModalProductPrice = document.getElementById('weightModalProductPrice');
    const manualWeightInput = document.getElementById('manualWeightInput');

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Synthesize beep sound for barcode scanner confirmation
    function playBeep() {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const osc = context.createOscillator();
            const gain = context.createGain();
            osc.connect(gain);
            gain.connect(context.destination);
            osc.frequency.value = 1000; // 1kHz frequency
            gain.gain.setValueAtTime(0.08, context.currentTime); // Volume
            osc.start();
            osc.stop(context.currentTime + 0.12); // Duration 120ms
        } catch (e) {
            console.error('Audio synth error: ', e);
        }
    }

    // INTERCEPT KEYBOARD SCANNER FOR USB HAND-SCANNER SIMULATION
    // Scanner types characters fast and finishes with 'Enter'.
    let scanBuffer = '';
    let lastKeyTime = Date.now();

    window.addEventListener('keydown', (e) => {
        // Intercept scanner codes only if we're not inside standard forms
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && activeEl !== usbScannerInput) {
            return; // ignore if user is typing normally in search or customer inputs
        }

        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 150) {
            scanBuffer = ''; // Reset buffer if typing slow (human keyboard)
        }
        lastKeyTime = currentTime;

        if (e.key === 'Enter') {
            if (scanBuffer.length >= 10 && scanBuffer.startsWith('2')) {
                // Detected a barcode scan
                e.preventDefault();
                processBarcode(scanBuffer);
                scanBuffer = '';
            }
        } else if (e.key >= '0' && e.key <= '9') {
            scanBuffer += e.key;
        }
    });

    // Also handle manual submission on the scan field
    usbScannerInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = usbScannerInput.value.trim();
            if (val) {
                processBarcode(val);
            }
        }
    });

    // Process Barcode Scan via AJAX
    function processBarcode(barcode) {
        usbScannerInput.value = ''; // Clear immediately
        usbScannerInput.blur();
        
        fetch(`{{ route('pos.scan') }}?barcode=${barcode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                playBeep();
                // Add product to cart with exact weight
                addToCart(data.product, data.scanned_weight);
            } else {
                alert(data.message);
            }
            usbScannerInput.focus();
        })
        .catch(err => {
            console.error(err);
            alert('Error scanning barcode.');
            usbScannerInput.focus();
        });
    }

    // 1. FILTER PRODUCTS BY CATEGORY
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            categoryTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const categoryId = tab.getAttribute('data-category-id');
            productCards.forEach(card => {
                if (categoryId === 'all' || card.getAttribute('data-category-id') === categoryId) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // 2. SEARCH PRODUCTS BY INPUT
    productSearchInput.addEventListener('input', () => {
        const query = productSearchInput.value.trim().toLowerCase();
        productCards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const sku = card.getAttribute('data-sku').toLowerCase();
            if (name.includes(query) || sku.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // 3. PRODUCT CLICK EVENTS
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const product = {
                id: parseInt(card.getAttribute('data-id')),
                sku: card.getAttribute('data-sku'),
                name: card.getAttribute('data-name'),
                price: parseFloat(card.getAttribute('data-price')),
                pricing_type: card.getAttribute('data-pricing-type'),
                stock: parseFloat(card.getAttribute('data-stock'))
            };

            if (product.pricing_type === 'weight') {
                // Weighed items require entering the weight
                currentSelectedProduct = product;
                weightModalProductName.innerText = product.name;
                weightModalProductPrice.innerText = product.price.toFixed(2);
                manualWeightInput.value = '';
                weightInputModal.classList.add('active');
                setTimeout(() => manualWeightInput.focus(), 150);
            } else {
                // Piece items add +1
                addToCart(product, 1.000);
            }
        });
    });

    // Close Weight Modal
    btnCloseWeightModal.addEventListener('click', () => {
        weightInputModal.classList.remove('active');
    });
    
    // Add manual weight
    weightModalForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const weight = parseFloat(manualWeightInput.value);
        if (weight > 0 && currentSelectedProduct) {
            addToCart(currentSelectedProduct, weight);
            weightInputModal.classList.remove('active');
        }
    });

    // 4. CART CORE LOGIC
    function addToCart(product, quantity) {
        // Check if product is already in cart
        const existingIndex = cart.findIndex(item => item.product_id === product.id);

        if (existingIndex > -1) {
            if (product.pricing_type === 'weight') {
                // If it is weighed, append the weight
                cart[existingIndex].quantity = parseFloat((cart[existingIndex].quantity + quantity).toFixed(3));
            } else {
                // Increments count by quantity
                cart[existingIndex].quantity = parseFloat((cart[existingIndex].quantity + quantity).toFixed(3));
            }
        } else {
            // New cart item
            cart.push({
                product_id: product.id,
                sku: product.sku,
                name: product.name,
                price: product.price,
                pricing_type: product.pricing_type,
                quantity: quantity
            });
        }

        renderCart();
    }

    function updateCartItemQty(productId, qty) {
        const itemIndex = cart.findIndex(item => item.product_id === productId);
        if (itemIndex > -1) {
            cart[itemIndex].quantity = parseFloat(qty);
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1);
            }
            renderCart();
        }
    }

    function removeCartItem(productId) {
        cart = cart.filter(item => item.product_id !== productId);
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            cartEmptyState.style.display = 'flex';
            cartItemsContainer.querySelectorAll('.cart-item').forEach(el => el.remove());
            cartCountBadge.innerText = '0';
            updateTotals(0.00);
            return;
        }

        cartEmptyState.style.display = 'none';
        
        // Remove existing item elements
        cartItemsContainer.querySelectorAll('.cart-item').forEach(el => el.remove());

        let subtotal = 0.00;

        cart.forEach(item => {
            const itemSubtotal = roundPrice(item.quantity * item.price);
            subtotal += itemSubtotal;

            const cartItemEl = document.createElement('div');
            cartItemEl.className = 'cart-item';
            cartItemEl.innerHTML = `
                <div class="cart-item-info">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-price">${item.price.toFixed(2)} ${item.pricing_type === 'weight' ? 'ج.م/كجم' : 'ج.م/قطعة'}</span>
                </div>
                <div class="cart-item-qty-control">
                    <button type="button" class="btn-qty-minus" data-id="${item.product_id}">-</button>
                    <input type="number" class="cart-item-qty-input" data-id="${item.product_id}" step="${item.pricing_type === 'weight' ? '0.05' : '1'}" min="0.001" value="${item.quantity}">
                    <button type="button" class="btn-qty-plus" data-id="${item.product_id}">+</button>
                </div>
                <div class="cart-item-subtotal">${itemSubtotal.toFixed(2)} ج.م</div>
                <button type="button" class="cart-item-remove" data-id="${item.product_id}">🗑️</button>
            `;
            cartItemsContainer.appendChild(cartItemEl);
        });

        cartCountBadge.innerText = cart.length;

        // Bind events on dynamically created buttons
        cartItemsContainer.querySelectorAll('.btn-qty-minus').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'));
                const item = cart.find(i => i.product_id === id);
                const step = item.pricing_type === 'weight' ? 0.100 : 1.000;
                updateCartItemQty(id, Math.max(0, item.quantity - step).toFixed(3));
            });
        });

        cartItemsContainer.querySelectorAll('.btn-qty-plus').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'));
                const item = cart.find(i => i.product_id === id);
                const step = item.pricing_type === 'weight' ? 0.100 : 1.000;
                updateCartItemQty(id, (item.quantity + step).toFixed(3));
            });
        });

        cartItemsContainer.querySelectorAll('.cart-item-qty-input').forEach(input => {
            input.addEventListener('change', () => {
                const id = parseInt(input.getAttribute('data-id'));
                updateCartItemQty(id, parseFloat(input.value) || 1);
            });
        });

        cartItemsContainer.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'));
                removeCartItem(id);
            });
        });

        updateTotals(subtotal);
    }

    function updateTotals(subtotal) {
        cartSubtotal.innerText = `${subtotal.toFixed(2)} ج.م`;
        const discount = parseFloat(discountInput.value) || 0.00;
        const total = Math.max(0.00, subtotal - discount);
        cartTotal.innerText = `${total.toFixed(2)} ج.م`;
    }

    discountInput.addEventListener('input', () => {
        let subtotal = 0.00;
        cart.forEach(item => {
            subtotal += roundPrice(item.quantity * item.price);
        });
        updateTotals(subtotal);
    });

    function roundPrice(num) {
        return Math.round((num + Number.EPSILON) * 100) / 100;
    }

    // 5. CUSTOMER SEARCH & SELECTION LOGIC
    let customerSearchTimeout = null;

    customerSearchInput.addEventListener('input', () => {
        clearTimeout(customerSearchTimeout);
        const q = customerSearchInput.value.trim();

        if (q.length < 2) {
            customerSearchResults.style.display = 'none';
            return;
        }

        customerSearchTimeout = setTimeout(() => {
            fetch(`{{ route('pos.customers.search') }}?q=${q}`)
                .then(res => res.json())
                .then(data => {
                    customerSearchResults.innerHTML = '';
                    if (data.length === 0) {
                        customerSearchResults.style.display = 'none';
                        return;
                    }

                    data.forEach(cust => {
                        const li = document.createElement('li');
                        li.className = 'customer-search-result-item';
                        li.innerHTML = `<span>${cust.name}</span> <span style="color:var(--text-secondary);">${cust.phone}</span>`;
                        li.addEventListener('click', () => {
                            linkCustomer(cust);
                        });
                        customerSearchResults.appendChild(li);
                    });

                    customerSearchResults.style.display = 'block';
                })
                .catch(err => console.error(err));
        }, 200);
    });

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        if (e.target !== customerSearchInput) {
            customerSearchResults.style.display = 'none';
        }
    });

    function linkCustomer(customer) {
        linkedCustomer = customer;
        linkedCustomerName.innerText = customer.name;
        linkedCustomerPhone.innerText = customer.phone;

        linkedCustomerContainer.style.display = 'block';
        customerSearchContainer.style.display = 'none';
        customerSearchResults.style.display = 'none';
        customerSearchInput.value = '';

        // Update debt ledger values
        customerDebtAmount.innerText = parseFloat(customer.balance).toFixed(2);
        customerDebtLimit.innerText = parseFloat(customer.credit_limit).toFixed(2);
        customerDebtPreview.style.display = 'block';
    }

    btnRemoveCustomer.addEventListener('click', () => {
        linkedCustomer = null;
        linkedCustomerContainer.style.display = 'none';
        customerSearchContainer.style.display = 'flex';
        customerDebtPreview.style.display = 'none';
    });

    // 6. PAYMENT BUTTONS SELECTOR
    paymentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activePaymentMethod = btn.getAttribute('data-method');
        });
    });

    // 7. QUICK CUSTOMER ADD MODAL
    btnOpenAddCustomerModal.addEventListener('click', () => {
        addCustomerModal.classList.add('active');
    });

    btnCloseAddCustomerModal.addEventListener('click', closeAddCustomerModal);
    btnCancelAddCustomerModal.addEventListener('click', closeAddCustomerModal);

    function closeAddCustomerModal() {
        addCustomerModal.classList.remove('active');
        quickAddCustomerForm.reset();
    }

    quickAddCustomerForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(quickAddCustomerForm);
        const payload = {
            name: formData.get('name'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            credit_limit: formData.get('credit_limit')
        };

        fetch("{{ route('pos.customers.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                linkCustomer(data.customer);
                closeAddCustomerModal();
            } else {
                alert(data.message || 'Error creating customer');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error creating customer. Check unique phone number.');
        });
    });

    // 8. CHECKOUT ORDER LOGIC
    btnCheckout.addEventListener('click', () => {
        if (cart.length === 0) {
            alert(
                "{{ app()->getLocale() === 'ar' ? 'سلة البيع فارغة، يرجى إضافة منتجات أولاً.' : 'Cart is empty. Please add products.' }}"
            );
            return;
        }

        const payload = {
            payment_method: activePaymentMethod,
            discount_amount: parseFloat(discountInput.value) || 0.00,
            customer_id: linkedCustomer ? linkedCustomer.id : null,
            cart: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            }))
        };

        btnCheckout.disabled = true;
        btnCheckout.innerText = "{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}";

        fetch("{{ route('pos.checkout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Open Receipt Print window
                const printUrl = `{{ url('/pos/receipt') }}/${data.order_id}`;
                const printWindow = window.open(printUrl, '_blank', 'width=600,height=800');
                
                // Reset Cart state
                cart = [];
                linkedCustomer = null;
                discountInput.value = 0;
                btnRemoveCustomer.click(); // resets customer views
                renderCart();

                alert(data.message);
            } else {
                alert(data.message || 'Checkout failed');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Checkout failed due to server error.');
        })
        .finally(() => {
            btnCheckout.disabled = false;
            btnCheckout.innerText = "{{ __('messages.checkout') }}";
            usbScannerInput.focus();
        });
    });

    // Autofocus scanner input on page load
    window.addEventListener('load', () => {
        usbScannerInput.focus();
    });
</script>
@endsection
