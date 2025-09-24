@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
    <h2 class="text-xl font-bold mb-4">Roles</h2>

    <a href="{{ route('admin.roles.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Add Role</a>

    <table class="min-w-full mt-4 bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($roles as $role)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $role->id }}</td>
                <td class="px-4 py-2">{{ $role->name }}</td>
                <td class="px-4 py-2 flex gap-2">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure?');">
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
