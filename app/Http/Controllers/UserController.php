<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('id');
        if (! $request->user()->hasRole('admin')) {
            $query->where('company_id', $request->user()->company_id);
        }
        $users = $query->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('admin.users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.user-create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'full_name'=> 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'full_name'=> $request->full_name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        if ($request->wantsJson()) {
            return response()->json($user, 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        if (! $request->user()->hasRole('admin') && $user->company_id !== $request->user()->company_id) {
            abort(403);
        }
        if (! $request->user()->hasRole('admin') && $user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $permissions=[];
        foreach ($user->roles as $role){
            foreach ($role->permissions as $permission){
                array_push($permissions, $permission);
            }
        }

        $permissions=array_unique($permissions);
        $user['permissions']=$permissions;

        if ($request->wantsJson()) {
            return response()->json($user);
        }

        return view('admin.user-show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all(); // исправлено

        if ($request->wantsJson()) {
            return response()->json($user);
        }

        return view('admin.user-edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        if (! $request->user()->hasRole('admin') && $user->company_id !== $request->user()->company_id) {
            abort(403);
        }
        if (! $request->user()->hasRole('admin') && $user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'name'     => 'sometimes|required|string',
            'full_name'=> 'sometimes|required|string',
            'email'    => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $user->update([
            'name'     => $request->input('name', $user->name),
            'full_name'=> $request->input('full_name', $user->full_name),
            'email'    => $request->input('email', $user->email),
            'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
        ]);

        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        if ($request->wantsJson()) {
            return response()->json($user);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
