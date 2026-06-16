@extends('layouts.app')

@section('title', __('messages.users'))
@section('header_title', __('messages.user_management'))

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 25px;">
    <button type="button" id="btnOpenAddUserModal" class="btn btn-primary">
        ➕ {{ __('messages.add_user') }}
    </button>
</div>

<!-- Table listing -->
<div class="panel">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>{{ __('messages.user_name') }}</th>
                    <th>{{ __('messages.email') }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'نوع الحساب' : 'Account Type' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الصلاحيات النشطة' : 'Active Permissions' }}</th>
                    <th style="text-align: center;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="font-bold">🔑 {{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge badge-success">{{ __('messages.is_super_admin') }}</span>
                            @else
                                <span class="badge badge-warning" style="background-color: var(--accent-light); color: var(--accent-color);">
                                    {{ app()->getLocale() === 'ar' ? 'مستخدم فرعي' : 'Sub-User' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span style="font-style: italic; color: var(--text-muted);">
                                    * {{ app()->getLocale() === 'ar' ? 'يمتلك كافة صلاحيات النظام' : 'Has all system permissions' }}
                                </span>
                            @else
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @forelse($user->permissions as $p)
                                        <span class="badge badge-success" style="font-size: 0.7rem; font-weight:600;">
                                            {{ app()->getLocale() === 'ar' ? $p->name_ar : $p->name_en }}
                                        </span>
                                    @empty
                                        <span style="font-size: 0.78rem; color: var(--danger-color);">
                                            🚫 {{ app()->getLocale() === 'ar' ? 'لا توجد صلاحيات (حساب معطل)' : 'No permissions (Locked account)' }}
                                        </span>
                                    @endforelse
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; display: flex; justify-content: center; gap: 8px;">
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-secondary btn-edit-user" style="padding: 6px 12px; font-size: 0.75rem;"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-is-admin="{{ $user->is_admin ? 1 : 0 }}"
                                    data-permissions="{{ $user->permissions->pluck('id')->toJson() }}">
                                ✏️ {{ __('messages.edit') }}
                            </button>

                            <!-- Delete Form -->
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا المستخدم؟' : 'Are you sure you want to delete this user?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.75rem;">
                                        🗑️ {{ __('messages.delete') }}
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.75rem; opacity: 0.5;" disabled>
                                    🔒 {{ __('messages.delete') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add User -->
<div class="modal-backdrop" id="addUserModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.add_user') }}</span>
            <button class="modal-close" id="btnCloseAddUserModal">×</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.user_name') }} *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }} *</label>
                    <input type="email" name="email" class="form-control" required placeholder="cashier@takamul.com">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.password') }} *</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••">
                </div>

                <!-- Admin Checkbox toggle -->
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                    <input type="checkbox" name="is_admin" id="add_is_admin" value="1" style="width:18px; height:18px; accent-color:var(--accent-color);">
                    <label for="add_is_admin" class="form-label" style="margin-bottom: 0; cursor: pointer; user-select: none; font-weight: 700; color: var(--accent-color);">
                        {{ __('messages.is_super_admin') }}
                    </label>
                </div>

                <!-- Permissions Checkboxes Group -->
                <div id="addPermissionsWrapper">
                    <label class="form-label">{{ __('messages.permissions') }}</label>
                    <div class="permissions-grid">
                        @foreach($permissions as $perm)
                            <label class="permission-check-item">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}">
                                <div class="permission-check-info">
                                    <span class="permission-check-label">{{ app()->getLocale() === 'ar' ? $perm->name_ar : $perm->name_en }}</span>
                                    <span class="permission-check-desc">
                                        {{ app()->getLocale() === 'ar' ? $perm->name_ar : $perm->name_en }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelAddUserModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div class="modal-backdrop" id="editUserModal">
    <div class="modal-card">
        <div class="modal-header">
            <span>{{ __('messages.edit_user') }}</span>
            <button class="modal-close" id="btnCloseEditUserModal">×</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.user_name') }} *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }} *</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.password') }}</label>
                    <input type="password" name="password" class="form-control" placeholder="{{ __('messages.password_help') }}">
                </div>

                <!-- Admin Checkbox toggle -->
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                    <input type="checkbox" name="is_admin" id="edit_is_admin" value="1" style="width:18px; height:18px; accent-color:var(--accent-color);">
                    <label for="edit_is_admin" class="form-label" style="margin-bottom: 0; cursor: pointer; user-select: none; font-weight: 700; color: var(--accent-color);">
                        {{ __('messages.is_super_admin') }}
                    </label>
                </div>

                <!-- Permissions Checkboxes Group -->
                <div id="editPermissionsWrapper">
                    <label class="form-label">{{ __('messages.permissions') }}</label>
                    <div class="permissions-grid">
                        @foreach($permissions as $perm)
                            <label class="permission-check-item">
                                <input type="checkbox" name="permissions[]" class="edit-perm-checkbox" value="{{ $perm->id }}" id="edit_perm_{{ $perm->id }}">
                                <div class="permission-check-info">
                                    <span class="permission-check-label">{{ app()->getLocale() === 'ar' ? $perm->name_ar : $perm->name_en }}</span>
                                    <span class="permission-check-desc">
                                        @if($perm->slug === 'access_pos')
                                            {{ __('messages.access_pos_desc') }}
                                        @elseif($perm->slug === 'manage_inventory')
                                            {{ __('messages.manage_inventory_desc') }}
                                        @elseif($perm->slug === 'view_reports')
                                            {{ __('messages.view_reports_desc') }}
                                        @elseif($perm->slug === 'manage_users')
                                            {{ __('messages.manage_users_desc') }}
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelEditUserModal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const addUserModal = document.getElementById('addUserModal');
    const btnOpenAddUserModal = document.getElementById('btnOpenAddUserModal');
    const btnCloseAddUserModal = document.getElementById('btnCloseAddUserModal');
    const btnCancelAddUserModal = document.getElementById('btnCancelAddUserModal');
    const addIsAdmin = document.getElementById('add_is_admin');
    const addPermissionsWrapper = document.getElementById('addPermissionsWrapper');

    const editUserModal = document.getElementById('editUserModal');
    const editUserForm = document.getElementById('editUserForm');
    const btnCloseEditUserModal = document.getElementById('btnCloseEditUserModal');
    const btnCancelEditUserModal = document.getElementById('btnCancelEditUserModal');
    const editIsAdmin = document.getElementById('edit_is_admin');
    const editPermissionsWrapper = document.getElementById('editPermissionsWrapper');

    // Toggle permissions display based on admin check
    addIsAdmin.addEventListener('change', () => {
        addPermissionsWrapper.style.display = addIsAdmin.checked ? 'none' : 'block';
    });
    
    editIsAdmin.addEventListener('change', () => {
        editPermissionsWrapper.style.display = editIsAdmin.checked ? 'none' : 'block';
    });

    // Add modal triggers
    btnOpenAddUserModal.addEventListener('click', () => {
        addIsAdmin.checked = false;
        addPermissionsWrapper.style.display = 'block';
        addUserModal.classList.add('active');
    });
    btnCloseAddUserModal.addEventListener('click', closeAddModal);
    btnCancelAddUserModal.addEventListener('click', closeAddModal);
    function closeAddModal() {
        addUserModal.classList.remove('active');
    }

    // Edit modal triggers
    const editBtns = document.querySelectorAll('.btn-edit-user');
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const email = btn.getAttribute('data-email');
            const isAdmin = btn.getAttribute('data-is-admin') === '1';
            const userPerms = JSON.parse(btn.getAttribute('data-permissions')); // array of permission IDs

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            editIsAdmin.checked = isAdmin;
            
            // Toggle view of permissions
            editPermissionsWrapper.style.display = isAdmin ? 'none' : 'block';

            // Reset checkboxes
            document.querySelectorAll('.edit-perm-checkbox').forEach(cb => {
                cb.checked = false;
            });

            // Set user checkboxes active
            userPerms.forEach(permId => {
                const cb = document.getElementById(`edit_perm_${permId}`);
                if (cb) cb.checked = true;
            });

            editUserForm.action = `{{ url('/admin/users') }}/${id}`;
            editUserModal.classList.add('active');
        });
    });

    btnCloseEditUserModal.addEventListener('click', closeEditModal);
    btnCancelEditUserModal.addEventListener('click', closeEditModal);
    function closeEditModal() {
        editUserModal.classList.remove('active');
    }
</script>
@endsection
