<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('id')->get();

        if (request()->wantsJson()) {
            return response()->json($permissions);
        }

        return view('admin.permissions', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permission-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        $permission = Permission::create($request->only('name'));

        if ($request->wantsJson()) {
            return response()->json($permission, 201);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($permission);
        }

        return redirect()->route('admin.permissions.edit', $permission);
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.permission-edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id
        ]);

        $permission->update($request->only('name'));

        if ($request->wantsJson()) {
            return response()->json($permission);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
