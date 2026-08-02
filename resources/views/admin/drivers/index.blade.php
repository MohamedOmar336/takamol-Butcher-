@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إدارة الطيارين' : 'Manage Drivers')
@section('header_title', app()->getLocale() === 'ar' ? 'إدارة طيارين الديليفري' : 'Delivery Drivers Management')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 25px;">
    <button type="button" id="btnOpenAddDriverModal" class="btn btn-primary">
        ➕ {{ app()->getLocale() === 'ar' ? 'إضافة طيار جديد' : 'Add New Driver' }}
    </button>
</div>

<!-- Table listing -->
<div class="panel">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>{{ app()->getLocale() === 'ar' ? 'اسم الطيار' : 'Driver Name' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th style="text-align: center;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td class="font-bold">🛵 {{ $driver->name }}</td>
                        <td>{{ $driver->phone ?? '-' }}</td>
                        <td>
                            @if($driver->is_active)
                                <span class="badge badge-success">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</span>
                            @else
                                <span class="badge badge-danger" style="background-color: var(--danger-light); color: var(--danger-color);">
                                    {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; display: flex; justify-content: center; gap: 8px;">
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-secondary btn-edit-driver" style="padding: 6px 12px; font-size: 0.75rem;"
                                    data-id="{{ $driver->id }}"
                                    data-name="{{ $driver->name }}"
                                    data-phone="{{ $driver->phone ?? '' }}"
                                    data-is-active="{{ $driver->is_active ? 1 : 0 }}">
                                ✏️ {{ __('messages.edit') }}
                            </button>

                            <!-- Delete Form -->
                            <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا الطيار؟' : 'Are you sure you want to delete this driver?' }}');">
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
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ app()->getLocale() === 'ar' ? 'لا يوجد طيارين مضافين حالياً.' : 'No drivers registered yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">
        {{ $drivers->links() }}
    </div>
</div>

<!-- Modal: Add Driver -->
<div class="modal-backdrop" id="addDriverModal">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header">
            <span>{{ app()->getLocale() === 'ar' ? 'إضافة طيار جديد' : 'Add New Driver' }}</span>
            <button class="modal-close" id="btnCloseAddDriverModal">×</button>
        </div>
        <form action="{{ route('admin.drivers.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الاسم بالكامل' : 'Full Name' }} *</label>
                    <input type="text" name="name" class="form-control" placeholder="محمد أحمد..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}</label>
                    <input type="text" name="phone" class="form-control" placeholder="01xxxxxxxxx">
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                    <input type="checkbox" name="is_active" id="add_is_active" checked style="width: 18px; height: 18px; accent-color: var(--accent-color);">
                    <label for="add_is_active" class="form-label" style="margin: 0; cursor: pointer;">{{ app()->getLocale() === 'ar' ? 'نشط (متاح للتوصيل)' : 'Active (Available for delivery)' }}</label>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" id="btnCancelAddDriverModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Driver -->
<div class="modal-backdrop" id="editDriverModal">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header">
            <span>{{ app()->getLocale() === 'ar' ? 'تعديل بيانات الطيار' : 'Edit Driver Details' }}</span>
            <button class="modal-close" id="btnCloseEditDriverModal">×</button>
        </div>
        <form id="editDriverForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الاسم بالكامل' : 'Full Name' }} *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--accent-color);">
                    <label for="edit_is_active" class="form-label" style="margin: 0; cursor: pointer;">{{ app()->getLocale() === 'ar' ? 'نشط (متاح للتوصيل)' : 'Active (Available for delivery)' }}</label>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" id="btnCancelEditDriverModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Modal Add Driver
        const addModal = document.getElementById('addDriverModal');
        const btnOpenAdd = document.getElementById('btnOpenAddDriverModal');
        const btnCloseAdd = document.getElementById('btnCloseAddDriverModal');
        const btnCancelAdd = document.getElementById('btnCancelAddDriverModal');

        const openAddModal = () => addModal.classList.add('active');
        const closeAddModal = () => addModal.classList.remove('active');

        if (btnOpenAdd) btnOpenAdd.addEventListener('click', openAddModal);
        if (btnCloseAdd) btnCloseAdd.addEventListener('click', closeAddModal);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAddModal);

        // Modal Edit Driver
        const editModal = document.getElementById('editDriverModal');
        const editForm = document.getElementById('editDriverForm');
        const btnCloseEdit = document.getElementById('btnCloseEditDriverModal');
        const btnCancelEdit = document.getElementById('btnCancelEditDriverModal');

        const edit_name = document.getElementById('edit_name');
        const edit_phone = document.getElementById('edit_phone');
        const edit_is_active = document.getElementById('edit_is_active');

        const closeEditModal = () => editModal.classList.remove('active');

        if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEditModal);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);

        document.querySelectorAll('.btn-edit-driver').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const phone = btn.getAttribute('data-phone');
                const isActive = parseInt(btn.getAttribute('data-is-active')) === 1;

                editForm.action = `/admin/drivers/${id}`;
                edit_name.value = name;
                edit_phone.value = phone;
                edit_is_active.checked = isActive;

                editModal.classList.add('active');
            });
        });
    });
</script>
@endsection
