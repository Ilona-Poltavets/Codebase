@extends('layouts.admin')

@section('title', 'Permissions Management')

@section('content')
    <h2 class="text-xl font-bold mb-4">Permissions</h2>

    <a href="{{ route('admin.permissions.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Add Permission</a>

    <table class="min-w-full mt-4 bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($permissions as $permission)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $permission->id }}</td>
                <td class="px-4 py-2">{{ $permission->name }}</td>
                <td class="px-4 py-2 flex gap-2">
                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Are you sure?');">
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
