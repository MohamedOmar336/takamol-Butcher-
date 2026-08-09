@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'طيارين الدليفري' : 'Delivery Drivers')
@section('header_title', app()->getLocale() === 'ar' ? 'إدارة طيارين التوصيل' : 'Delivery Drivers Management')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Top Bar -->
    <div class="panel" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
            <span>🛵</span> {{ app()->getLocale() === 'ar' ? 'سجل طيارين الدليفري والتوصيل' : 'Delivery Drivers Roster' }}
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.drivers.settlement') }}" class="btn btn-secondary" style="font-weight: 700;">
                📊 {{ app()->getLocale() === 'ar' ? 'تقرير تقفيل الطيارين اليومي' : 'End-of-Day Settlement Report' }}
            </a>
            <button type="button" class="btn btn-primary" id="btnOpenAddDriverModal" style="font-weight: 700;">
                ➕ {{ app()->getLocale() === 'ar' ? 'إضافة طيار جديد' : 'Add New Driver' }}
            </button>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="panel" style="padding: 25px;">
        @if($drivers->isEmpty())
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <div style="font-size: 3rem; margin-bottom: 10px;">🛵</div>
                {{ app()->getLocale() === 'ar' ? 'لم يتم إضافة طيارين دليفري بعد.' : 'No delivery drivers registered yet.' }}
            </div>
        @else
            <table class="app-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary);">
                        <th style="padding: 12px; text-align: start;">#</th>
                        <th style="padding: 12px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'اسم الطيار' : 'Driver Name' }}</th>
                        <th style="padding: 12px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}</th>
                        <th style="padding: 12px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'وسيلة التوصيل' : 'Vehicle Type' }}</th>
                        <th style="padding: 12px; text-align: center;">{{ app()->getLocale() === 'ar' ? 'طلبات اليوم' : 'Today Orders' }}</th>
                        <th style="padding: 12px; text-align: center;">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drivers as $driver)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 14px; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="padding: 14px; font-weight: 700; color: var(--text-primary);">
                                🛵 {{ $driver->name }}
                            </td>
                            <td style="padding: 14px; font-family: monospace;">{{ $driver->phone ?: '-' }}</td>
                            <td style="padding: 14px;">{{ $driver->vehicle_type }}</td>
                            <td style="padding: 14px; text-align: center; font-weight: 800; color: var(--accent-color);">
                                {{ $driver->orders_count }} {{ app()->getLocale() === 'ar' ? 'طلب' : 'orders' }}
                            </td>
                            <td style="padding: 14px; text-align: center;">
                                <span class="badge {{ $driver->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $driver->is_active ? (app()->getLocale() === 'ar' ? 'نشط' : 'Active') : (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>

<!-- Modal: Add Driver -->
<div class="modal-backdrop" id="addDriverModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>🛵 {{ app()->getLocale() === 'ar' ? 'إضافة طيار توصيل جديد' : 'Add New Delivery Driver' }}</span>
            <button class="modal-close" id="btnCloseAddDriverModal">×</button>
        </div>
        <form action="{{ route('admin.drivers.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم الطيار' : 'Driver Name' }}</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. محمد علي" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رقم المحمول' : 'Phone Number' }}</label>
                    <input type="text" name="phone" class="form-control" placeholder="01012345678">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وسيلة التوصيل' : 'Vehicle Type' }}</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="دراجة نارية">🛵 دراجة نارية / موتوسيكل</option>
                        <option value="سيارة">🚗 سيارة توصيل</option>
                        <option value="عجلة">🚲 دراجة هوائية</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelAddDriverModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('addDriverModal');
        document.getElementById('btnOpenAddDriverModal').addEventListener('click', () => modal.classList.add('active'));
        document.getElementById('btnCloseAddDriverModal').addEventListener('click', () => modal.classList.remove('active'));
        document.getElementById('btnCancelAddDriverModal').addEventListener('click', () => modal.classList.remove('active'));
    });
</script>
@endsection
