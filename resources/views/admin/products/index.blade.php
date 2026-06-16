@extends('layouts.app')

@section('title', __('messages.products'))
@section('header_title', __('messages.product_catalog'))

@section('content')
<!-- Excel Import & Add product row -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px; align-items: start;">
    
    <!-- Left panel: Excel Import -->
    <div class="panel" style="margin-bottom: 0;">
        <div class="panel-header">
            <h3 class="panel-title">📥 {{ __('messages.excel_import') }}</h3>
        </div>
        
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 15px; align-items: flex-end;">
            @csrf
            <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                <label class="form-label">{{ __('messages.select_excel_file') }}</label>
                <input type="file" name="import_file" class="form-control" required style="padding: 9px 12px;">
            </div>
            <button type="submit" class="btn btn-success" style="padding: 11px 24px;">
                ⚡ {{ __('messages.import') }}
            </button>
        </form>

        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px; line-height: 1.4; margin-bottom: 12px;">
            {{ app()->getLocale() === 'ar'
                ? 'الملف يجب أن يحتوي على أعمدة بالأسماء التالية: الباركود (SKU)، الاسم بالعربية (Name AR)، الاسم بالإنجليزية (Name EN)، السعر (Price)، نوع البيع (pricing_type - مثل وزن أو قطعة)، المخزون (Stock)، القسم (Category).'
                : 'Expected columns: SKU, Name AR, Name EN, Price, Pricing Type (weight or piece), Stock, Category.'
            }}
        </p>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding-top: 8px; border-top: 1px dashed var(--border-color);">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);">
                {{ app()->getLocale() === 'ar' ? 'تحميل نموذج الجدول الفارغ:' : 'Download blank template:' }}
            </span>
            <a href="{{ asset('products_template.xlsx') }}" download class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.72rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                🟢 Excel Template (.xlsx)
            </a>
            <a href="{{ asset('products_template.csv') }}" download class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.72rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                📄 CSV Template (.csv)
            </a>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding-top: 8px; margin-top: 8px; border-top: 1px dashed var(--border-color);">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);">
                {{ app()->getLocale() === 'ar' ? 'تحميل شيت أسعار "التكامل" المعبأ بالكامل:' : 'Download filled "Takamul" sheet:' }}
            </span>
            <a href="{{ asset('products_takamul.xlsx') }}" download class="btn btn-primary" style="padding: 5px 10px; font-size: 0.72rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; color: #fff;">
                🟢 Takamul Sheet (.xlsx)
            </a>
            <a href="{{ asset('products_takamul.csv') }}" download class="btn btn-primary" style="padding: 5px 10px; font-size: 0.72rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; color: #fff;">
                📄 Takamul CSV (.csv)
            </a>
        </div>

        <!-- Excel row errors display -->
        @if(session('import_errors'))
            <div class="panel" style="background-color: var(--danger-light); border-color: var(--danger-color); color: var(--danger-color); padding: 15px; margin-top: 15px; border-radius: var(--btn-radius); font-size: 0.82rem; max-height: 180px; overflow-y: auto;">
                <h4 style="font-weight: 700; margin-bottom: 8px;">{{ app()->getLocale() === 'ar' ? 'تنبيهات أخطاء الأسطر أثناء الاستيراد:' : 'Row error warnings during import:' }}</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 4px;">
                    @foreach(session('import_errors') as $err)
                        <li>⚠️ {{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Right panel: Add Button CTA -->
    <div class="panel" style="margin-bottom: 0; text-align: center; height: 100%; display: flex; align-items: center; justify-content: center;">
        <button type="button" id="btnOpenAddProductModal" class="btn btn-primary" style="padding: 16px 30px; font-size: 1.05rem; width: 100%;">
            ➕ {{ __('messages.add_product') }}
        </button>
    </div>
</div>

<!-- Filter search bar -->
<div class="panel" style="padding: 15px 24px; margin-bottom: 25px;">
    <form action="{{ route('admin.products.index') }}" method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <!-- Query -->
        <div class="form-group" style="flex-grow: 1; margin-bottom: 0; min-width: 200px;">
            <input type="text" name="q" class="form-control" placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث برقم الباركود أو اسم المنتج...' : 'Search by PLU code or product name...' }}" value="{{ $q }}">
        </div>
        
        <!-- Category Filter -->
        <div class="form-group" style="width: 200px; margin-bottom: 0;">
            <select name="category_id" class="form-control">
                <option value="">-- {{ app()->getLocale() === 'ar' ? 'تصفية بالقسم' : 'All Categories' }} --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? $cat->name_ar : $cat->name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-secondary" style="padding: 11px 20px;">
            🔍 {{ app()->getLocale() === 'ar' ? 'تطبيق الفلتر' : 'Filter' }}
        </button>

        @if($q || $categoryId)
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary" style="padding: 11px 20px;">
                🔄 {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
            </a>
        @endif
    </form>
</div>

<!-- Products Table List -->
<div class="panel">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>{{ __('messages.sku_plu') }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الاسم (عربي / انجليزي)' : 'Name (AR / EN)' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'القسم' : 'Category' }}</th>
                    <th>{{ __('messages.price_unit') }}</th>
                    <th>{{ __('messages.pricing_type') }}</th>
                    <th>{{ __('messages.stock_qty') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th style="text-align: center;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                    <tr>
                        <td class="font-bold" style="color: var(--accent-color);">{{ $prod->sku }}</td>
                        <td>
                            <div class="font-bold">{{ $prod->name_ar }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $prod->name_en }}</div>
                        </td>
                        <td>
                            <span class="badge badge-warning" style="background-color: var(--accent-light); color: var(--accent-color);">
                                {{ app()->getLocale() === 'ar' ? $prod->category->name_ar : $prod->category->name_en }}
                            </span>
                        </td>
                        <td class="font-bold">{{ floatval($prod->price) }} ج.م</td>
                        <td>
                            {{ $prod->pricing_type === 'weight' ? __('messages.pricing_by_weight') : __('messages.pricing_by_piece') }}
                        </td>
                        <td class="font-bold {{ $prod->stock < 5 ? 'text-danger' : '' }}" style="color: {{ $prod->stock < 5 ? 'var(--danger-color)' : 'inherit' }}">
                            {{ floatval($prod->stock) }} {{ $prod->pricing_type === 'weight' ? __('messages.kg') : __('messages.piece') }}
                        </td>
                        <td>
                            @if($prod->is_active)
                                <span class="badge badge-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td style="text-align: center; display: flex; justify-content: center; gap: 8px;">
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-secondary btn-edit-product" style="padding: 6px 12px; font-size: 0.75rem;"
                                    data-id="{{ $prod->id }}"
                                    data-category-id="{{ $prod->category_id }}"
                                    data-sku="{{ $prod->sku }}"
                                    data-name-ar="{{ $prod->name_ar }}"
                                    data-name-en="{{ $prod->name_en }}"
                                    data-price="{{ $prod->price }}"
                                    data-pricing-type="{{ $prod->pricing_type }}"
                                    data-stock="{{ $prod->stock }}"
                                    data-is-active="{{ $prod->is_active ? 1 : 0 }}">
                                ✏️ {{ __('messages.edit') }}
                            </button>

                            <!-- Delete Form -->
                            <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا المنتج؟' : 'Are you sure you want to delete this product?' }}');">
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
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد منتجات مطابقة للبحث.' : 'No products found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="app-pagination" style="margin-top: 20px;">
        {{ $products->appends(['q' => $q, 'category_id' => $categoryId])->links() }}
    </div>
</div>

<!-- Modal: Add Product -->
<div class="modal-backdrop" id="addProductModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.add_product') }}</span>
            <button class="modal-close" id="btnCloseAddProductModal">×</button>
        </div>
        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.sku_plu') }} *</label>
                        <input type="text" name="sku" class="form-control" required placeholder="E.g. 01113">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.price_unit') }} *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.pricing_type') }} *</label>
                        <select name="pricing_type" class="form-control" required>
                            <option value="weight">{{ __('messages.pricing_by_weight') }}</option>
                            <option value="piece">{{ __('messages.pricing_by_piece') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.stock_qty') }} *</label>
                        <input type="number" name="stock" class="form-control" step="0.001" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_name_ar') }} *</label>
                    <input type="text" name="name_ar" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_name_en') }} *</label>
                    <input type="text" name="name_en" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Category' }} *</label>
                    <select name="category_id" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ app()->getLocale() === 'ar' ? $cat->name_ar : $cat->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="add_is_active" value="1" checked style="width:18px; height:18px; accent-color:var(--accent-color);">
                    <label for="add_is_active" class="form-label" style="margin-bottom: 0; cursor: pointer; user-select: none;">{{ __('messages.active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelAddProductModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Product -->
<div class="modal-backdrop" id="editProductModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.edit_product') }}</span>
            <button class="modal-close" id="btnCloseEditProductModal">×</button>
        </div>
        <form id="editProductForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.sku_plu') }} *</label>
                        <input type="text" name="sku" id="edit_sku" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.price_unit') }} *</label>
                        <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.pricing_type') }} *</label>
                        <select name="pricing_type" id="edit_pricing_type" class="form-control" required>
                            <option value="weight">{{ __('messages.pricing_by_weight') }}</option>
                            <option value="piece">{{ __('messages.pricing_by_piece') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.stock_qty') }} *</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control" step="0.001" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_name_ar') }} *</label>
                    <input type="text" name="name_ar" id="edit_name_ar" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_name_en') }} *</label>
                    <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Category' }} *</label>
                    <select name="category_id" id="edit_category_id" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ app()->getLocale() === 'ar' ? $cat->name_ar : $cat->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" style="width:18px; height:18px; accent-color:var(--accent-color);">
                    <label for="edit_is_active" class="form-label" style="margin-bottom: 0; cursor: pointer; user-select: none;">{{ __('messages.active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelEditProductModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Elements
    const addProductModal = document.getElementById('addProductModal');
    const btnOpenAddProductModal = document.getElementById('btnOpenAddProductModal');
    const btnCloseAddProductModal = document.getElementById('btnCloseAddProductModal');
    const btnCancelAddProductModal = document.getElementById('btnCancelAddProductModal');

    const editProductModal = document.getElementById('editProductModal');
    const editProductForm = document.getElementById('editProductForm');
    const btnCloseEditProductModal = document.getElementById('btnCloseEditProductModal');
    const btnCancelEditProductModal = document.getElementById('btnCancelEditProductModal');
    
    // Add product form popup triggers
    btnOpenAddProductModal.addEventListener('click', () => {
        addProductModal.classList.add('active');
    });

    btnCloseAddProductModal.addEventListener('click', closeAddModal);
    btnCancelAddProductModal.addEventListener('click', closeAddModal);

    function closeAddModal() {
        addProductModal.classList.remove('active');
    }

    // Edit product triggers
    const editBtns = document.querySelectorAll('.btn-edit-product');
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const categoryId = btn.getAttribute('data-category-id');
            const sku = btn.getAttribute('data-sku');
            const nameAr = btn.getAttribute('data-name-ar');
            const nameEn = btn.getAttribute('data-name-en');
            const price = btn.getAttribute('data-price');
            const pricingType = btn.getAttribute('data-pricing-type');
            const stock = btn.getAttribute('data-stock');
            const isActive = btn.getAttribute('data-is-active') === '1';

            // Populate form values
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_sku').value = sku;
            document.getElementById('edit_name_ar').value = nameAr;
            document.getElementById('edit_name_en').value = nameEn;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_pricing_type').value = pricingType;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_is_active').checked = isActive;

            // Set Form action endpoint
            editProductForm.action = `{{ url('/admin/products') }}/${id}`;

            editProductModal.classList.add('active');
        });
    });

    btnCloseEditProductModal.addEventListener('click', closeEditModal);
    btnCancelEditProductModal.addEventListener('click', closeEditModal);

    function closeEditModal() {
        editProductModal.classList.remove('active');
    }
</script>
@endsection
