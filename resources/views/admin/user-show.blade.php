<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                        {{ $user->name }}
                    </h3>
                    <div class="space-y-2">
                        <p><strong>ID:</strong> {{ $user->id }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Roles:</strong>
                            @foreach ($user->roles as $role)
                                <span
                                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-md">{{ $role->name }}</span>
                            @endforeach
                        </p>
                        <p><strong>Permissions:</strong>
                            @foreach ($user->permissions as $perm)
                                <span
                                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-md">{{ $perm->name }}</span>
                            @endforeach
                        </p>
                        <p><strong>Created at:</strong> {{ $user->created_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        <p><strong>Updated at:</strong> {{ $user->updated_at?->format('Y-m-d H:i') ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.users.edit', $user->id) }}"
                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow">
                        Edit
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
