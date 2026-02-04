{{--@extends('layouts.admin')--}}

{{--@section('title', 'Users Management')--}}

{{--@section('content')--}}
{{--    <h2 class="text-xl font-bold mb-4">Users</h2>--}}

{{--    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Add User</a>--}}

{{--    <table class="min-w-full mt-4 bg-white rounded shadow overflow-hidden">--}}
{{--        <thead class="bg-gray-200">--}}
{{--        <tr>--}}
{{--            <th class="px-4 py-2">ID</th>--}}
{{--            <th class="px-4 py-2">Name</th>--}}
{{--            <th class="px-4 py-2">Email</th>--}}
{{--            <th class="px-4 py-2">Roles</th>--}}
{{--            <th class="px-4 py-2">Permissions</th>--}}
{{--            <th class="px-4 py-2">Actions</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach($users as $user)--}}
{{--            <tr class="border-t">--}}
{{--                <td class="px-4 py-2">{{ $user->id }}</td>--}}
{{--                <td class="px-4 py-2">{{ $user->name }}</td>--}}
{{--                <td class="px-4 py-2">{{ $user->email }}</td>--}}
{{--                <td class="px-4 py-2">{{ $user->roles->pluck('name')->join(', ') }}</td>--}}
{{--                <td class="px-4 py-2">{{ $user->permissions->pluck('name')->join(', ') }}</td>--}}
{{--                <td class="px-4 py-2 flex gap-2">--}}
{{--                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline">Edit</a>--}}
{{--                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?');">--}}
{{--                        @csrf--}}
{{--                        @method('DELETE')--}}
{{--                        <button type="submit" class="text-red-600 hover:underline">Delete</button>--}}
{{--                    </form>--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}
{{--@endsection--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Users') }}
            </h2>
            @if(Auth::user()?->hasRole('admin'))
                <a href="{{ route('admin.users.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">
                    Add User
                </a>
            @elseif(Auth::user()?->hasRole('owner'))
                <a href="{{ route('admin.invites.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">
                    Invite User
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-900">
                        <thead>
                        <tr>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">ID</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Name</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Email</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Created At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $user->id }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $user->name }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $user->email }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('admin.users.show', $user->id) }}"
                                           class="text-blue-500 hover:text-blue-700" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                                          9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                                                          0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                           class="text-green-500 hover:text-green-700" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 4h2m2 0h2m-6 0h2m4 0h2m-6 0h2m-6 0h2M4 20h16M4 4h16v16H4V4zm10 4l2 2-8 8H6v-2l8-8z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138
                                                              21H7.862a2 2 0 01-1.995-1.858L5
                                                              7m5 4v6m4-6v6m1-10V4a1 1 0
                                                              00-1-1h-4a1 1 0 00-1
                                                              1v3m-4 0h14" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(method_exists($users, 'links'))
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
