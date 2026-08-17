@extends('layouts.super_admin')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم العامة' : 'Super Admin Dashboard')
@section('header_title', app()->getLocale() === 'ar' ? 'إدارة المتاجر والشركاء' : 'Store Tenant Management')

@section('content')
<div style="display: flex; flex-direction: column; gap: 30px;">

    <!-- Stats row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="panel card-summary" style="display: flex; align-items: center; gap: 20px; padding: 20px;">
            <div style="font-size: 2.5rem; background-color: var(--accent-light); padding: 10px; border-radius: 12px;">🏪</div>
            <div>
                <div style="font-size: 0.9rem; color: var(--text-secondary);">{{ app()->getLocale() === 'ar' ? 'إجمالي المتاجر' : 'Total Stores' }}</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary);">{{ $tenants->count() }}</div>
            </div>
        </div>
        <div class="panel card-summary" style="display: flex; align-items: center; gap: 20px; padding: 20px;">
            <div style="font-size: 2.5rem; background-color: var(--success-light); padding: 10px; border-radius: 12px; color: var(--success-color);">✅</div>
            <div>
                <div style="font-size: 0.9rem; color: var(--text-secondary);">{{ app()->getLocale() === 'ar' ? 'المتاجر النشطة' : 'Active Stores' }}</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--success-color);">{{ $tenants->where('status', 'active')->count() }}</div>
            </div>
        </div>
        <div class="panel card-summary" style="display: flex; align-items: center; gap: 20px; padding: 20px;">
            <div style="font-size: 2.5rem; background-color: var(--danger-light); padding: 10px; border-radius: 12px; color: var(--danger-color);">⚠️</div>
            <div>
                <div style="font-size: 0.9rem; color: var(--text-secondary);">{{ app()->getLocale() === 'ar' ? 'المتاجر الموقوفة' : 'Suspended Stores' }}</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--danger-color);">{{ $tenants->where('status', 'suspended')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
        
        <!-- Stores Directory Panel -->
        <div class="panel" style="padding: 25px;">
            <h3 style="font-weight: 800; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>📋</span>
                {{ app()->getLocale() === 'ar' ? 'سجل المتاجر المسجلة' : 'Registered Stores Directory' }}
            </h3>

            @if($tenants->isEmpty())
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">🫙</div>
                    {{ app()->getLocale() === 'ar' ? 'لا يوجد أي متاجر مسجلة حالياً.' : 'No stores registered yet.' }}
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: start;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.85rem;">
                                <th style="padding: 12px 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'المتجر' : 'Store' }}</th>
                                <th style="padding: 12px 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'الرابط الفرعي' : 'Subdomain Slug' }}</th>
                                <th style="padding: 12px 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'نوع النشاط' : 'Business Type' }}</th>
                                <th style="padding: 12px 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني للمالك' : 'Owner Email' }}</th>
                                <th style="padding: 12px 10px; text-align: start;">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                                <th style="padding: 12px 10px; text-align: center;">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $tenant)
                                <tr style="border-bottom: 1px solid var(--border-color); font-size: 0.9rem; transition: background 0.2s;">
                                    <!-- Store Name -->
                                    <td style="padding: 15px 10px; font-weight: 700; color: var(--text-primary);">
                                        {{ $tenant->name }}
                                    </td>
                                    <!-- Subdomain Link -->
                                    <td style="padding: 15px 10px; font-family: monospace; text-align: start;">
                                        @php
                                            $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
                                            $url = "http://{$tenant->slug}.{$centralDomain}/login";
                                        @endphp
                                        <a href="{{ $url }}" target="_blank" style="color: var(--accent-color); text-decoration: underline; font-weight: 600;">
                                            {{ $tenant->slug }}.{{ $centralDomain }}
                                        </a>
                                    </td>
                                    <!-- Store Type Badge -->
                                    <td style="padding: 15px 10px;">
                                        @switch($tenant->store_type)
                                            @case('butcher')
                                                <span class="badge" style="background-color: #ffe4e6; color: #e11d48; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    🥩 {{ app()->getLocale() === 'ar' ? 'جزارة' : 'Butcher' }}
                                                </span>
                                                @break
                                            @case('supermarket')
                                                <span class="badge" style="background-color: #dbeafe; color: #2563eb; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    🛒 {{ app()->getLocale() === 'ar' ? 'سوبر ماركت' : 'Supermarket' }}
                                                </span>
                                                @break
                                            @case('clothing')
                                                <span class="badge" style="background-color: #f3e8ff; color: #9333ea; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    👕 {{ app()->getLocale() === 'ar' ? 'ملابس' : 'Clothing' }}
                                                </span>
                                                @break
                                            @case('shoes')
                                                <span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    👟 {{ app()->getLocale() === 'ar' ? 'أحذية' : 'Shoes' }}
                                                </span>
                                                @break
                                            @case('car_service')
                                                <span class="badge" style="background-color: #e0f2fe; color: #0284c7; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    🚗 {{ app()->getLocale() === 'ar' ? 'مركز خدمة سيارات' : 'Car Service Center' }}
                                                </span>
                                                @break
                                            @default
                                                <span class="badge" style="background-color: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                                    💼 {{ app()->getLocale() === 'ar' ? 'عام' : 'General' }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <!-- Owner Email -->
                                    <td style="padding: 15px 10px; color: var(--text-secondary);">
                                        {{ $tenant->owner_email }}
                                    </td>
                                    <!-- Status Badge -->
                                    <td style="padding: 15px 10px;">
                                        @if($tenant->status === 'active')
                                            <span style="background-color: var(--success-light); color: var(--success-color); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block;">
                                                {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                                            </span>
                                        @else
                                            <span style="background-color: var(--danger-light); color: var(--danger-color); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block;">
                                                {{ app()->getLocale() === 'ar' ? 'موقوف' : 'Suspended' }}
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Actions -->
                                    <td style="padding: 15px 10px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <button type="button" class="btn btn-secondary btn-edit-tenant" style="padding: 6px 12px; font-size: 0.8rem;"
                                                data-id="{{ $tenant->id }}"
                                                data-name="{{ $tenant->name }}"
                                                data-slug="{{ $tenant->slug }}"
                                                data-store-type="{{ $tenant->store_type }}"
                                                data-owner-email="{{ $tenant->owner_email }}"
                                                data-report-email="{{ $tenant->settings['report_email'] ?? '' }}"
                                                data-logo="{{ isset($tenant->settings['logo']) ? asset($tenant->settings['logo']) : '' }}">
                                            ✏️ {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                                        </button>
                                        <form action="/super-admin/tenants/{{ $tenant->id }}/toggle-status" method="POST" style="display: inline;">
                                            @csrf
                                            @if($tenant->status === 'active')
                                                <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--danger-color); color: var(--danger-color);" onclick="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من إيقاف هذا المتجر؟ لن يتمكن الموظفون من الدخول إليه.' : 'Are you sure you want to suspend this store? Employees will lose access.' }}')">
                                                    🚫 {{ app()->getLocale() === 'ar' ? 'إيقاف' : 'Suspend' }}
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--success-color); border-color: var(--success-color); color: white;">
                                                    ✅ {{ app()->getLocale() === 'ar' ? 'تفعيل' : 'Activate' }}
                                                </button>
                                            @endif
                                        </form>
                                        <form action="/super-admin/tenants/{{ $tenant->id }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-color: #ef4444; color: #ef4444; background: none;" onclick="return confirm('{{ app()->getLocale() === 'ar' ? '⚠️ تحذير: هل أنت متأكد من حذف هذا المتجر نهائياً؟ سيتم مسح قاعدة البيانات وكل المبيعات والمنتجات ولا يمكن استرجاعها!' : '⚠️ Warning: Are you sure you want to permanently delete this store? This will delete the database, all sales, and products. This action CANNOT be undone!' }}')">
                                                🗑️ {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                                            </button>
                                        </form>
                                        @php
                                            $bypassTimestamp = time();
                                            $bypassSignature = hash_hmac('sha256', $tenant->slug . '|' . $bypassTimestamp, config('app.key'));
                                            $bypassUrl = "http://{$tenant->slug}.{$centralDomain}/login/bypass?timestamp={$bypassTimestamp}&signature={$bypassSignature}";
                                        @endphp
                                        <a href="{{ $bypassUrl }}" target="_blank" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; background-color: #0f172a; border-color: #0f172a; color: white; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; border-radius: 6px;">
                                            🔑 {{ app()->getLocale() === 'ar' ? 'دخول للمتجر' : 'Login to Store' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Create New Store Form -->
        <div class="panel" style="padding: 25px;">
            <h3 style="font-weight: 800; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>✨</span>
                {{ app()->getLocale() === 'ar' ? 'إنشاء متجر جديد' : 'Register New Store' }}
            </h3>

            @if($errors->has('error'))
                <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 12px; margin-bottom: 20px; font-size: 0.85rem;">
                    ⚠️ {{ $errors->first('error') }}
                </div>
            @endif

            <form action="/super-admin/tenants" method="POST">
                @csrf
                
                <!-- Store Name -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم المحل / المتجر' : 'Store/Shop Name' }}</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Trendy Clothing" required value="{{ old('name') }}">
                    @error('name')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Subdomain Slug -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم الرابط الفرعي (Slug)' : 'Subdomain Slug' }}</label>
                    <input type="text" name="slug" class="form-control" placeholder="e.g. trendy-shop" required value="{{ old('slug') }}" style="text-align: ltr;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
                        {{ app()->getLocale() === 'ar' ? 'يستخدم كرابط للمتجر (أحرف إنجليزية وأرقام فقط)' : 'Used as domain prefix. Only lowercase letters, numbers, and dashes.' }}
                    </small>
                    @error('slug')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Store Preset Type -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'نوع النشاط (التهيئة المسبقة)' : 'Business Type Preset' }}</label>
                    <select name="store_type" class="form-control" required style="height: 46px; padding: 10px;">
                        <option value="butcher" {{ old('store_type') === 'butcher' ? 'selected' : '' }}>🥩 {{ app()->getLocale() === 'ar' ? 'محل جزارة (يدعم الموازين)' : 'Butcher Shop (Weight scale active)' }}</option>
                        <option value="supermarket" {{ old('store_type') === 'supermarket' ? 'selected' : '' }}>🛒 {{ app()->getLocale() === 'ar' ? 'سوبر ماركت (يدعم الموازين والقطع)' : 'Supermarket (Scale + piece active)' }}</option>
                        <option value="clothing" {{ old('store_type') === 'clothing' ? 'selected' : '' }}>👕 {{ app()->getLocale() === 'ar' ? 'محل ملابس (بيع بالقطعة فقط)' : 'Clothing Store (Piece only, scale inactive)' }}</option>
                        <option value="shoes" {{ old('store_type') === 'shoes' ? 'selected' : '' }}>👟 {{ app()->getLocale() === 'ar' ? 'محل أحذية (بيع بالقطعة فقط)' : 'Shoe Store (Piece only, scale inactive)' }}</option>
                        <option value="car_service" {{ old('store_type') === 'car_service' ? 'selected' : '' }}>🚗 {{ app()->getLocale() === 'ar' ? 'مركز خدمة وصيانة سيارات (أوامر شغل وفواتير A4)' : 'Car Service Center (Job Cards & A4 Invoices)' }}</option>
                        <option value="general" {{ old('store_type') === 'general' ? 'selected' : '' }}>💼 {{ app()->getLocale() === 'ar' ? 'نشاط تجاري عام' : 'General Business (Standard POS)' }}</option>
                    </select>
                    @error('store_type')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Tenant Admin Owner Details -->
                <div style="border-top: 1px solid var(--border-color); margin: 25px 0 15px 0; padding-top: 15px;">
                    <h4 style="font-weight: 700; font-size: 0.95rem; margin-bottom: 15px; color: var(--text-secondary);">
                        {{ app()->getLocale() === 'ar' ? 'بيانات مالك المتجر (حساب المدير)' : 'Store Owner Account Details' }}
                    </h4>
                </div>

                <!-- Owner Name -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الاسم الكامل للمالك' : 'Owner Full Name' }}</label>
                    <input type="text" name="owner_name" class="form-control" placeholder="e.g. Mohamed Omar" required value="{{ old('owner_name') }}">
                    @error('owner_name')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Owner Email -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني للمالك' : 'Owner Email Address' }}</label>
                    <input type="email" name="owner_email" class="form-control" placeholder="e.g. owner@store.com" required value="{{ old('owner_email') }}" style="text-align: ltr;">
                    @error('owner_email')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Report Email -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني لاستلام التقارير' : 'Report Email Address' }}</label>
                    <input type="email" name="report_email" class="form-control" placeholder="e.g. reports@store.com" value="{{ old('report_email') }}" style="text-align: ltr;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني الذي ستُرسل إليه تقارير المبيعات اليومية تلقائياً (اتركه فارغاً للاستلام على بريد المالك)' : 'Email address that will automatically receive daily sales reports (leave blank to use owner email)' }}
                    </small>
                    @error('report_email')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Owner Password -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'كلمة مرور حساب المالك' : 'Owner Password' }}</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required style="text-align: ltr;">
                    @error('password')
                        <small style="color: var(--danger-color);">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--btn-radius); font-weight: 700; margin-top: 10px;">
                    ➕ {{ app()->getLocale() === 'ar' ? 'إنشاء وتجهيز المتجر' : 'Create & Setup Store' }}
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Modal: Edit Store -->
<div class="modal-backdrop" id="editTenantModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ app()->getLocale() === 'ar' ? 'تعديل بيانات المتجر' : 'Edit Store Details' }}</span>
            <button class="modal-close" id="btnCloseEditTenantModal">×</button>
        </div>
        <form id="editTenantForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <!-- Store Name -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم المحل / المتجر' : 'Store/Shop Name' }}</label>
                    <input type="text" name="name" id="edit_tenant_name" class="form-control" required>
                </div>

                <!-- Subdomain Slug (Read Only) -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رابط المتجر (لا يمكن تعديله)' : 'Subdomain Slug (Read-only)' }}</label>
                    <input type="text" id="edit_tenant_slug" class="form-control" readonly style="background-color: var(--bg-primary); text-align: ltr;">
                </div>

                <!-- Store Preset Type -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'نوع النشاط (التهيئة المسبقة)' : 'Business Type Preset' }}</label>
                    <select name="store_type" id="edit_tenant_store_type" class="form-control" required style="height: 46px; padding: 10px;">
                        <option value="butcher">🥩 {{ app()->getLocale() === 'ar' ? 'محل جزارة (يدعم الموازين)' : 'Butcher Shop (Weight scale active)' }}</option>
                        <option value="supermarket">🛒 {{ app()->getLocale() === 'ar' ? 'سوبر ماركت (يدعم الموازين والقطع)' : 'Supermarket (Scale + piece active)' }}</option>
                        <option value="clothing">👕 {{ app()->getLocale() === 'ar' ? 'محل ملابس (بيع بالقطعة فقط)' : 'Clothing Store (Piece only, scale inactive)' }}</option>
                        <option value="shoes">👟 {{ app()->getLocale() === 'ar' ? 'محل أحذية (بيع بالقطعة فقط)' : 'Shoe Store (Piece only, scale inactive)' }}</option>
                        <option value="car_service">🚗 {{ app()->getLocale() === 'ar' ? 'مركز خدمة وصيانة سيارات (أوامر شغل وفواتير A4)' : 'Car Service Center (Job Cards & A4 Invoices)' }}</option>
                        <option value="general">💼 {{ app()->getLocale() === 'ar' ? 'نشاط تجاري عام' : 'General Business (Standard POS)' }}</option>
                    </select>
                </div>

                <!-- Owner Email -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني للمالك' : 'Owner Email Address' }}</label>
                    <input type="email" name="owner_email" id="edit_tenant_owner_email" class="form-control" required style="text-align: ltr;">
                </div>

                <!-- Report Email -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني لاستلام التقارير' : 'Report Email Address' }}</label>
                    <input type="email" name="report_email" id="edit_tenant_report_email" class="form-control" style="text-align: ltr;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني الذي ستُرسل إليه تقارير المبيعات اليومية تلقائياً (اتركه فارغاً للاستلام على بريد المالك)' : 'Email address that will automatically receive daily sales reports (leave blank to use owner email)' }}
                    </small>
                </div>

                <!-- Custom Logo Upload -->
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'شعار المتجر (Logo)' : 'Store Logo Image' }}</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <div style="flex-grow: 1;">
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
                                {{ app()->getLocale() === 'ar' ? 'صورة شعار مخصصة (تظهر كشعار لوحة البيع، الفواتير، وأيقونة الموقع)' : 'Custom image for POS headers, invoice thermal receipts, and favicon.' }}
                            </small>
                        </div>
                        <div id="edit_tenant_logo_preview_container" style="display: none;">
                            <img id="edit_tenant_logo_preview" src="" alt="Preview" style="height: 50px; width: 50px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border-color); background-color: white;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelEditTenantModal">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                <button type="submit" class="btn btn-primary">{{ app()->getLocale() === 'ar' ? 'حفظ التعديلات' : 'Save Changes' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Edit Store Modal logic
        const editTenantModal = document.getElementById('editTenantModal');
        const editTenantForm = document.getElementById('editTenantForm');
        const btnCloseEditTenantModal = document.getElementById('btnCloseEditTenantModal');
        const btnCancelEditTenantModal = document.getElementById('btnCancelEditTenantModal');
        
        const editTenantBtns = document.querySelectorAll('.btn-edit-tenant');
        editTenantBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const slug = btn.getAttribute('data-slug');
                const storeType = btn.getAttribute('data-store-type');
                const ownerEmail = btn.getAttribute('data-owner-email');
                const reportEmail = btn.getAttribute('data-report-email');
                const logoUrl = btn.getAttribute('data-logo');

                // Populate values
                document.getElementById('edit_tenant_name').value = name;
                document.getElementById('edit_tenant_slug').value = slug;
                document.getElementById('edit_tenant_store_type').value = storeType;
                document.getElementById('edit_tenant_owner_email').value = ownerEmail;
                document.getElementById('edit_tenant_report_email').value = reportEmail;

                // Preview current logo if exists
                const previewImg = document.getElementById('edit_tenant_logo_preview');
                const previewContainer = document.getElementById('edit_tenant_logo_preview_container');
                if (logoUrl) {
                    previewImg.src = logoUrl;
                    previewContainer.style.display = 'block';
                } else {
                    previewContainer.style.display = 'none';
                }

                // Set Form action endpoint
                editTenantForm.action = `/super-admin/tenants/${id}`;

                editTenantModal.classList.add('active');
            });
        });

        btnCloseEditTenantModal.addEventListener('click', closeEditTenantModal);
        btnCancelEditTenantModal.addEventListener('click', closeEditTenantModal);

        function closeEditTenantModal() {
            editTenantModal.classList.remove('active');
        }
    });
</script>
@endsection
