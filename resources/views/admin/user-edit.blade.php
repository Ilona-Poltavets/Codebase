<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="name">Name</label>
                        <input id="name" name="name" type="text"
                               value="{{ old('name', $user->name) }}"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="email">Email</label>
                        <input id="email" name="email" type="email"
                               value="{{ old('email', $user->email) }}"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="password">Password (optional)</label>
                        <input id="password" name="password" type="password"
                               placeholder="Leave blank to keep current password"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Role --}}
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="role">Role</label>
                        <select id="role" name="role_id"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $user->roles->contains($role->id) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                            Save
                        </button>

                        <a href="{{ route('users.show', $user->id) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
