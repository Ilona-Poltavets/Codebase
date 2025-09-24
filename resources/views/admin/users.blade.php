@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
    <h2 class="text-xl font-bold mb-4">Users</h2>

    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Add User</a>

    <table class="min-w-full mt-4 bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Roles</th>
            <th class="px-4 py-2">Permissions</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $user->id }}</td>
                <td class="px-4 py-2">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">{{ $user->roles->pluck('name')->join(', ') }}</td>
                <td class="px-4 py-2">{{ $user->permissions->pluck('name')->join(', ') }}</td>
                <td class="px-4 py-2 flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
