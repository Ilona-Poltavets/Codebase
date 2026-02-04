<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Edit Company') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 dark:text-white text-black bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ __('Company Information') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Update company details and assignment.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('admin.companies.update', $company->id) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="name" :value="__('Company Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                              :value="old('name', $company->name)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="domain" :value="__('Domain')" />
                                <x-text-input id="domain" name="domain" type="text" class="mt-1 block w-full"
                                              :value="old('domain', $company->domain)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('domain')" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description"
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          rows="4">{{ old('description', $company->description) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <div>
                                <x-input-label for="owner_id" :value="__('Owner')" />
                                @php($selectedOwner = old('owner_id', $company->owner_id))
                                <select id="owner_id" name="owner_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('Select owner') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (int) $selectedOwner === $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('owner_id')" />
                            </div>

                            <div>
                                <x-input-label for="plan" :value="__('Plan')" />
                                <select id="plan" name="plan"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="free" {{ old('plan', $company->plan) === 'free' ? 'selected' : '' }}>free</option>
                                    <option value="pro" {{ old('plan', $company->plan) === 'pro' ? 'selected' : '' }}>pro</option>
                                    <option value="pro_enterprise" {{ old('plan', $company->plan) === 'pro_enterprise' ? 'selected' : '' }}>pro_enterprise</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('plan')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                <a href="{{ route('admin.companies.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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
