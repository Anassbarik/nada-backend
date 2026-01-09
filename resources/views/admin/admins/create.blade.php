@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">Create New Admin</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.admins.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Admins
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="password" value="Password" />
                    <div class="relative">
                        <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required />
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i data-lucide="eye" id="password-eye-icon" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <div class="relative">
                        <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10" type="password" name="password_confirmation" required />
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i data-lucide="eye" id="password_confirmation-eye-icon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super-admin" {{ old('role') === 'super-admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Super Admin has all permissions automatically.</p>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div id="permissions-section" class="mb-6" style="display: none;">
                    <x-input-label value="Permissions" />
                    <p class="text-sm text-gray-500 mb-4">Select the permissions for this admin:</p>
                    
                    @foreach($permissionsByResource as $resource => $permissions)
                        <div class="mb-4 border rounded-lg p-4">
                            <h4 class="font-semibold mb-2 capitalize">{{ $resource }}</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               class="rounded border-gray-300"
                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm">{{ $permission->action }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a href="{{ route('admin.admins.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                    <x-primary-button class="btn-logo-primary">
                        Create Admin
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const permissionsSection = document.getElementById('permissions-section');
    
    function togglePermissions() {
        if (roleSelect.value === 'admin') {
            permissionsSection.style.display = 'block';
        } else {
            permissionsSection.style.display = 'none';
            // Uncheck all permissions when super-admin is selected
            document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }
    
    roleSelect.addEventListener('change', togglePermissions);
    togglePermissions(); // Initial call
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-eye-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        field.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script>
@endsection

