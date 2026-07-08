@extends('layouts.app')

@section('title', __('messages.orders_list'))
@section('header_title', __('messages.admin_panel') . ' - ' . __('messages.orders_list'))

@section('content')
<!-- Filter Panel -->
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">{{ app()->getLocale() === 'ar' ? 'البحث والتصفية' : 'Search & Filters' }}</h3>
    </div>
    
    <form action="{{ route('admin.orders.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        <!-- Search bar -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search" class="form-label">{{ app()->getLocale() === 'ar' ? 'البحث العام' : 'General Search' }}</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('messages.search_orders') }}">
        </div>

        <!-- Start Date -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="start_date" class="form-label">{{ __('messages.start_date') }}</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>

        <!-- End Date -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="end_date" class="form-label">{{ __('messages.end_date') }}</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>

        <!-- Payment Method -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="payment_method" class="form-label">{{ __('messages.payment_method') }}</label>
            <select name="payment_method" id="payment_method" class="form-control">
                <option value="">{{ __('messages.all_methods') }}</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>{{ __('messages.cash') }}</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>{{ __('messages.card') }}</option>
                <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>{{ __('messages.credit') }}</option>
            </select>
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="flex-grow: 1;">
                🔍 {{ __('messages.filter') }}
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                🔄
            </a>
        </div>
    </form>
</div>

<!-- Orders Table Panel -->
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">{{ __('messages.all_orders') }}</h3>
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
                    <th style="text-align: center;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr style="{{ $order->status === 'refunded' ? 'opacity: 0.65; background-color: rgba(0,0,0,0.02);' : '' }}">
                        <td class="font-bold" style="color: var(--accent-color);">
                            <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" title="{{ __('messages.view_invoice') }}">
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
                                <span style="color: var(--text-muted);">{{ app()->getLocale() === 'ar' ? 'عميل كاش' : 'Cash Customer' }}</span>
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
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <button class="btn btn-secondary btn-sm btn-view-items" 
                                        style="padding: 6px 12px; font-size: 0.8rem;"
                                        data-order-no="{{ $order->order_number }}"
                                        data-customer="{{ $order->customer ? $order->customer->name : (app()->getLocale() === 'ar' ? 'عميل كاش' : 'Cash Customer') }}"
                                        data-date="{{ $order->created_at->format('Y-m-d H:i') }}"
                                        data-discount="{{ floatval($order->discount_amount) }}"
                                        data-total="{{ floatval($order->total_amount) }}"
                                        data-items="{{ json_encode($order->items->map(function($item) {
                                            return [
                                                'name' => app()->getLocale() === 'ar' ? ($item->product ? $item->product->name_ar : 'صنف محذوف') : ($item->product ? $item->product->name_en : 'Deleted Product'),
                                                'price' => floatval($item->unit_price),
                                                'quantity' => floatval($item->quantity),
                                                'subtotal' => floatval($item->subtotal),
                                                'unit' => $item->product ? ($item->product->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece')) : ''
                                            ];
                                        })) }}">
                                    👁️ {{ __('messages.view_items') }}
                                </button>
                                <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--border-color);">
                                    🖨️ {{ __('messages.print') }}
                                </a>
                                @if(auth()->user()->is_admin && $order->status === 'completed')
                                    <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('{{ __('messages.refund_confirm') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 12px; font-size: 0.8rem; border: none; background-color: var(--danger-color); color: white;">
                                            ↩️ {{ __('messages.refund') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                            {{ app()->getLocale() === 'ar' ? 'لم يتم العثور على أي فواتير تطابق شروط البحث.' : 'No invoices matched the search criteria.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="app-pagination" style="margin-top: 20px;">
        {{ $orders->links() }}
    </div>
</div>

<!-- View Order Items Modal -->
<div id="invoiceItemsModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 650px;">
        <div class="modal-header">
            <h3 id="modalInvoiceNumber" style="margin: 0; font-size: 1.25rem;">{{ __('messages.view_invoice') }}</h3>
            <button id="btnCloseModal" class="btn-close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; font-size: 0.9rem; padding: 10px; background-color: var(--bg-color); border-radius: var(--btn-radius);">
                <div>
                    <strong>{{ __('messages.customers') }}:</strong> <span id="modalCustomerName"></span>
                </div>
                <div style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                    <strong>{{ __('messages.date') }}:</strong> <span id="modalInvoiceDate"></span>
                </div>
            </div>

            <table class="app-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>{{ app()->getLocale() === 'ar' ? 'اسم المنتج' : 'Product' }}</th>
                        <th style="text-align: center;">{{ __('messages.qty') }}</th>
                        <th style="text-align: right;">{{ __('messages.unit_price') }}</th>
                        <th style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ __('messages.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody id="modalItemsTableBody">
                    <!-- Javascript injected rows -->
                </tbody>
            </table>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 15px; font-size: 0.95rem;">
                <div id="modalDiscountRow" style="display: none;">
                    {{ __('messages.discount') }}: <span id="modalDiscountValue" class="font-bold"></span> {{ __('messages.currency') }}
                </div>
                <div style="font-size: 1.2rem; color: var(--accent-color);">
                    <strong>{{ __('messages.total') }}:</strong> <span id="modalTotalValue" class="font-bold"></span> {{ __('messages.currency') }}
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
            <button id="btnCloseModalFooter" class="btn btn-secondary">{{ __('messages.close') }}</button>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--panel-radius);
        box-shadow: var(--shadow-lg);
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        transform: translateY(20px);
        transition: transform 0.25s ease;
        display: flex;
        flex-direction: column;
    }
    .modal-overlay.active .modal-card {
        transform: translateY(0);
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-body {
        padding: 20px;
        overflow-y: auto;
    }
    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        background-color: rgba(0, 0, 0, 0.02);
    }
    .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-color);
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .btn-close:hover {
        opacity: 1;
    }
    
    /* Pagination style overrides for clean integration */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        list-style: none;
        padding: 0;
    }
    .pagination li a, .pagination li span {
        display: inline-block;
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--btn-radius);
        text-decoration: none;
        color: var(--text-color);
        background-color: var(--panel-bg);
        transition: all 0.2s;
    }
    .pagination li.active span {
        background-color: var(--accent-color);
        color: #fff;
        border-color: var(--accent-color);
    }
    .pagination li.disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .pagination li a:hover:not(.disabled) {
        background-color: var(--accent-light);
        color: var(--accent-color);
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalOverlay = document.getElementById('invoiceItemsModal');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnCloseModalFooter = document.getElementById('btnCloseModalFooter');
        
        const modalInvoiceNumber = document.getElementById('modalInvoiceNumber');
        const modalCustomerName = document.getElementById('modalCustomerName');
        const modalInvoiceDate = document.getElementById('modalInvoiceDate');
        const modalItemsTableBody = document.getElementById('modalItemsTableBody');
        const modalDiscountRow = document.getElementById('modalDiscountRow');
        const modalDiscountValue = document.getElementById('modalDiscountValue');
        const modalTotalValue = document.getElementById('modalTotalValue');

        // Open Modal Handler
        document.querySelectorAll('.btn-view-items').forEach(btn => {
            btn.addEventListener('click', () => {
                const orderNo = btn.getAttribute('data-order-no');
                const customer = btn.getAttribute('data-customer');
                const date = btn.getAttribute('data-date');
                const discount = parseFloat(btn.getAttribute('data-discount'));
                const total = parseFloat(btn.getAttribute('data-total'));
                const items = JSON.parse(btn.getAttribute('data-items'));

                // Set headers
                modalInvoiceNumber.innerText = `{{ __('messages.view_invoice') }} - ${orderNo}`;
                modalCustomerName.innerText = customer;
                modalInvoiceDate.innerText = date;

                // Populate Table
                modalItemsTableBody.innerHTML = '';
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    
                    // Format quantities correctly (3 decimal places for weighed items, 0 for piece items)
                    const isWeighed = item.unit === "{{ __('messages.kg') }}";
                    const formattedQty = isWeighed ? item.quantity.toFixed(3) : item.quantity.toFixed(0);
                    
                    tr.innerHTML = `
                        <td class="font-bold">${item.name}</td>
                        <td style="text-align: center;">${formattedQty} <span style="font-size: 0.8rem; color: var(--text-muted);">${item.unit}</span></td>
                        <td style="text-align: right;">${item.price.toFixed(2)} {{ __('messages.currency') }}</td>
                        <td class="font-bold" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">${item.subtotal.toFixed(2)} {{ __('messages.currency') }}</td>
                    `;
                    modalItemsTableBody.appendChild(tr);
                });

                // Set totals
                if (discount > 0) {
                    modalDiscountValue.innerText = discount.toFixed(2);
                    modalDiscountRow.style.display = 'block';
                } else {
                    modalDiscountRow.style.display = 'none';
                }
                modalTotalValue.innerText = total.toFixed(2);

                // Show modal
                modalOverlay.classList.add('active');
            });
        });

        // Close Modal Handlers
        const closeModal = () => {
            modalOverlay.classList.remove('active');
        };

        btnCloseModal.addEventListener('click', closeModal);
        btnCloseModalFooter.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });
    });
</script>
@endsection
