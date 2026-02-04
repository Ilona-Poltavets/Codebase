<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id')->get();

        if (request()->wantsJson()) {
            return response()->json($roles);
        }

        return view('admin.roles', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.role-create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->only('name'));

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        if ($request->wantsJson()) {
            return response()->json($role, 201);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($role);
        }

        return redirect()->route('admin.roles.edit', $role);
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        return view('admin.role-edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($request->only('name'));
        $role->permissions()->sync($request->permissions ?? []);

        if ($request->wantsJson()) {
            return response()->json($role);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Admin role cannot be deleted.'], 422);
            }

            return redirect()->route('admin.roles.index')->with('error', 'Admin role cannot be deleted.');
        }

        $role->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
