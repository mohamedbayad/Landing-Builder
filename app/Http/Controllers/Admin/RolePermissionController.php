<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy(fn ($permission) => $permission->group ?: 'general');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync(array_map('intval', $validated['permission_ids'] ?? []));

        return redirect()->route('roles-permissions.index')->with('status', 'Role permissions updated successfully.');
    }
}
