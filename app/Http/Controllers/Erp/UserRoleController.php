<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view list user role')) {
            abort(403, 'Unauthorized action.');
        }
        $roles = Role::all();
        $permissions = Permission::all();
        
        // Group permissions by category
        $permissionsByCategory = $permissions->groupBy('category');
        
        return view('erp.userRole.userrole', compact('roles', 'permissions', 'permissionsByCategory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('userRole.index')->with('success', 'Role created successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->save();

        if ($request->has('permissions')) {
            // Convert permission IDs to permission names
            $permissionIds = $request->permissions;
            $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('userRole.index')->with('success', 'Role updated successfully!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('delete user role')) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::findOrFail($id);
        
        // Prevent deletion of Admin role
        if ($role->name === 'Admin') {
            return redirect()->route('userRole.index')->with('error', 'Cannot delete the Admin role.');
        }
        
        // Check if role is assigned to any users
        $usersWithRole = \App\Models\User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return redirect()->route('userRole.index')->with('error', 'Cannot delete role that is assigned to users. Please reassign users first.');
        }
        
        $role->delete();
        
        return redirect()->route('userRole.index')->with('success', 'Role deleted successfully!');
    }
}

