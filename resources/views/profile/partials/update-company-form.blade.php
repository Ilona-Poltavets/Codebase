<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Company Settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Update your company details.') }}
        </p>
    </header>

    <form method="post" action="{{ route('company.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="company_name" :value="__('Company Name')" />
            <x-text-input id="company_name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $company->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="company_domain" :value="__('Domain')" />
            <x-text-input id="company_domain" name="domain" type="text" class="mt-1 block w-full"
                          :value="old('domain', $company->domain)" required />
            <x-input-error class="mt-2" :messages="$errors->get('domain')" />
        </div>

        <div>
            <x-input-label for="company_description" :value="__('Description')" />
            <textarea id="company_description" name="description"
                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      rows="4">{{ old('description', $company->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div>
            <x-input-label for="company_plan" :value="__('Plan')" />
            <select id="company_plan" name="plan"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="free" {{ old('plan', $company->plan) === 'free' ? 'selected' : '' }}>free</option>
                <option value="pro" {{ old('plan', $company->plan) === 'pro' ? 'selected' : '' }}>pro</option>
                <option value="pro_enterprise" {{ old('plan', $company->plan) === 'pro_enterprise' ? 'selected' : '' }}>pro_enterprise</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('plan')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'company-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
