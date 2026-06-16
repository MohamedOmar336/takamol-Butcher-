@extends('layouts.app')

@section('title', __('messages.customer_profile'))
@section('header_title', __('messages.customer_profile') . ' - ' . $customer->name)

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary" style="padding: 8px 16px;">
        ← {{ app()->getLocale() === 'ar' ? 'العودة للدليل' : 'Back to Directory' }}
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    
    <!-- Left Panel: Customer Stats & Debt Settlement -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Card 1: Customer Profile Details -->
        <div class="panel" style="margin-bottom: 0;">
            <div class="panel-header">
                <h3 class="panel-title">👤 {{ app()->getLocale() === 'ar' ? 'البيانات الأساسية' : 'Primary Details' }}</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.92rem;">
                <div>
                    <strong>{{ __('messages.customer_name') }}:</strong>
                    <div style="margin-top: 4px; font-size: 1.05rem; font-weight: 700;">{{ $customer->name }}</div>
                </div>
                
                <div style="border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <strong>{{ __('messages.customer_phone') }}:</strong>
                    <div style="margin-top: 4px; font-weight: 600;">{{ $customer->phone }}</div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <strong>{{ __('messages.customer_address') }}:</strong>
                    <div style="margin-top: 4px; color: var(--text-secondary);">{{ $customer->address ?? '-' }}</div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <strong>{{ __('messages.notes') }}:</strong>
                    <div style="margin-top: 4px; color: var(--text-muted); font-style: italic;">{{ $customer->notes ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Debt Settlement Form -->
        <div class="panel" style="margin-bottom: 0; border-color: var(--warning-color); background-color: var(--warning-light);">
            <div class="panel-header" style="border-color: rgba(245, 158, 11, 0.3);">
                <h3 class="panel-title" style="color: var(--warning-color);">💰 {{ __('messages.outstanding_debt') }}</h3>
            </div>

            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--danger-color);">
                    {{ floatval($customer->balance) }} <span style="font-size: 1.2rem;">ج.م</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 5px;">
                    {{ __('messages.credit_limit') }}: {{ floatval($customer->credit_limit) }} ج.م
                </div>
            </div>

            @if($customer->balance > 0)
                <form action="{{ route('admin.customers.pay', $customer->id) }}" method="POST">
                    @csrf
                    <!-- Settle debt input -->
                    @if($errors->has('amount'))
                        <div style="color: var(--danger-color); font-size: 0.78rem; margin-bottom: 8px;">
                            ❌ {{ $errors->first('amount') }}
                        </div>
                    @endif
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="color: var(--text-primary);">{{ __('messages.amount_to_pay') }} *</label>
                        <input type="number" name="amount" class="form-control" style="font-weight: 700; font-size: 1.1rem;" step="0.01" min="0.01" max="{{ $customer->balance }}" required>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 10px; font-weight: 700;">
                        💳 {{ __('messages.settle_debt') }}
                    </button>
                </form>
            @else
                <div style="text-align: center; color: var(--success-color); font-weight: 700; font-size: 0.88rem; padding: 10px 0;">
                    ✓ {{ app()->getLocale() === 'ar' ? 'العميل ليس عليه أي ديون مستحقة.' : 'Customer has no outstanding debts.' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Right Panel: Billing Invoice History -->
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">📋 {{ __('messages.payment_history') }}</h3>
            <span class="badge badge-success">{{ app()->getLocale() === 'ar' ? 'إجمالي المشتريات: ' : 'Total Spend: ' }} {{ floatval($customer->total_spent) }} ج.م</span>
        </div>

        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.order_no') }}</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'الكاشير' : 'Cashier' }}</th>
                        <th>{{ __('messages.payment_method') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ __('messages.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-bold" style="color: var(--accent-color);">
                                <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" title="View/Print Receipt">
                                    📄 {{ $order->order_number }}
                                </a>
                            </td>
                            <td>{{ $order->cashier_name ?? ($order->user ? $order->user->name : '-') }}</td>
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
                                {{ floatval($order->total_amount) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                {{ app()->getLocale() === 'ar' ? 'لا يوجد فواتير مسجلة لهذا العميل بعد.' : 'No invoices recorded for this customer yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
