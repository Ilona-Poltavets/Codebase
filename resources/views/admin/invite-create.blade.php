<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Create Invite') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 dark:text-white text-black bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ __('Invite User') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Send an invite link with role and company.') }}
                            </p>
                        </header>

                        @if(session('success'))
                            <div class="mt-4 rounded bg-green-50 px-4 py-2 text-green-700 text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('admin.invites.store') }}" class="mt-6 space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                              :value="old('email')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            @if(Auth::user()?->hasRole('owner'))
                                <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                            @else
                                <div>
                                    <x-input-label for="company_id" :value="__('Company')" />
                                    <select id="company_id" name="company_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required>
                                        <option value="">{{ __('Select a company') }}</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('company_id')" />
                                </div>
                            @endif

                            <div>
                                <x-input-label for="role_id" :value="__('Role')" />
                                <select id="role_id" name="role_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <option value="">{{ __('Select a role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Create Invite') }}</x-primary-button>
                                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
