@extends('layouts.app')

@section('title', __('messages.customers'))
@section('header_title', __('messages.customers_directory'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <!-- Search filter -->
    <form action="{{ route('admin.customers.index') }}" method="GET" style="display: flex; gap: 10px; width: 60%; max-width: 500px;">
        <input type="text" name="q" class="form-control" placeholder="{{ __('messages.search_customer_placeholder') }}" value="{{ $q }}">
        <button type="submit" class="btn btn-secondary">🔍</button>
        @if($q)
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">🔄</a>
        @endif
    </form>

    <button type="button" id="btnOpenAddCustomerModal" class="btn btn-primary">
        ➕ {{ __('messages.add_customer') }}
    </button>
</div>

<!-- Table listing -->
<div class="panel">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>{{ __('messages.customer_name') }}</th>
                    <th>{{ __('messages.customer_phone') }}</th>
                    <th>{{ __('messages.customer_address') }}</th>
                    <th>{{ __('messages.credit_limit') }}</th>
                    <th>{{ __('messages.current_debt') }}</th>
                    <th style="text-align: center;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $cust)
                    <tr>
                        <td class="font-bold">
                            <!-- Link to customer ledger profile page -->
                            <a href="{{ route('admin.customers.show', $cust->id) }}" style="color: var(--accent-color); text-decoration: underline;">
                                👤 {{ $cust->name }}
                            </a>
                        </td>
                        <td>{{ $cust->phone }}</td>
                        <td>{{ $cust->address ?? '-' }}</td>
                        <td>{{ floatval($cust->credit_limit) }} ج.م</td>
                        <td class="font-bold {{ $cust->balance > 0 ? 'text-danger' : '' }}" style="color: {{ $cust->balance > 0 ? 'var(--danger-color)' : 'inherit' }}">
                            {{ floatval($cust->balance) }} ج.م
                        </td>
                        <td style="text-align: center; display: flex; justify-content: center; gap: 8px;">
                            <a href="{{ route('admin.customers.show', $cust->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.75rem;">
                                👁️ {{ app()->getLocale() === 'ar' ? 'كشف الحساب' : 'Ledger' }}
                            </a>
                            
                            <button type="button" class="btn btn-secondary btn-edit-customer" style="padding: 6px 12px; font-size: 0.75rem;"
                                    data-id="{{ $cust->id }}"
                                    data-name="{{ $cust->name }}"
                                    data-phone="{{ $cust->phone }}"
                                    data-address="{{ $cust->address }}"
                                    data-credit-limit="{{ $cust->credit_limit }}"
                                    data-notes="{{ $cust->notes }}">
                                ✏️ {{ __('messages.edit') }}
                            </button>

                            <form action="{{ route('admin.customers.destroy', $cust->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا العميل؟ سيتم حذف سجل فواتيره أيضاً!' : 'Are you sure you want to delete this customer? This will delete their invoice history!' }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.75rem;">
                                    🗑️ {{ __('messages.delete') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ app()->getLocale() === 'ar' ? 'لا يوجد عملاء مسجلين حالياً.' : 'No customers registered yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="app-pagination" style="margin-top: 20px;">
        {{ $customers->appends(['q' => $q])->links() }}
    </div>
</div>

<!-- Modal: Add Customer -->
<div class="modal-backdrop" id="addCustomerModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.add_customer') }}</span>
            <button class="modal-close" id="btnCloseAddCustomerModal">×</button>
        </div>
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_name') }} *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.customer_phone') }} *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.credit_limit') }} (ج.م) *</label>
                        <input type="number" name="credit_limit" class="form-control" value="1000" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_address') }}</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelAddCustomerModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Customer -->
<div class="modal-backdrop" id="editCustomerModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.edit_customer') }}</span>
            <button class="modal-close" id="btnCloseEditCustomerModal">×</button>
        </div>
        <form id="editCustomerForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.customer_name') }} *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.customer_phone') }} *</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.credit_limit') }} (ج.م) *</label>
                        <input type="number" name="credit_limit" id="edit_credit_limit" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label{{ __('messages.customer_address') }}"></label>
                    <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.notes') }}</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelEditCustomerModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const addCustomerModal = document.getElementById('addCustomerModal');
    const btnOpenAddCustomerModal = document.getElementById('btnOpenAddCustomerModal');
    const btnCloseAddCustomerModal = document.getElementById('btnCloseAddCustomerModal');
    const btnCancelAddCustomerModal = document.getElementById('btnCancelAddCustomerModal');

    const editCustomerModal = document.getElementById('editCustomerModal');
    const editCustomerForm = document.getElementById('editCustomerForm');
    const btnCloseEditCustomerModal = document.getElementById('btnCloseEditCustomerModal');
    const btnCancelEditCustomerModal = document.getElementById('btnCancelEditCustomerModal');

    // Add modal triggers
    btnOpenAddCustomerModal.addEventListener('click', () => {
        addCustomerModal.classList.add('active');
    });
    btnCloseAddCustomerModal.addEventListener('click', closeAddModal);
    btnCancelAddCustomerModal.addEventListener('click', closeAddModal);
    function closeAddModal() {
        addCustomerModal.classList.remove('active');
    }

    // Edit modal triggers
    const editBtns = document.querySelectorAll('.btn-edit-customer');
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const phone = btn.getAttribute('data-phone');
            const address = btn.getAttribute('data-address');
            const creditLimit = btn.getAttribute('data-credit-limit');
            const notes = btn.getAttribute('data-notes');

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_address').value = address === 'null' ? '' : address;
            document.getElementById('edit_credit_limit').value = creditLimit;
            document.getElementById('edit_notes').value = notes === 'null' ? '' : notes;

            editCustomerForm.action = `{{ url('/admin/customers') }}/${id}`;
            editCustomerModal.classList.add('active');
        });
    });

    btnCloseEditCustomerModal.addEventListener('click', closeEditModal);
    btnCancelEditCustomerModal.addEventListener('click', closeEditModal);
    function closeEditModal() {
        editCustomerModal.classList.remove('active');
    }
</script>
@endsection
