@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100">Create an account</h2>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                @if(isset($invite) && $invite)
                    <input type="hidden" name="invite_token" value="{{ $invite->token }}">
                    <input type="hidden" name="register_type" value="invite">
                    <div class="p-3 rounded bg-indigo-50 text-indigo-700 text-sm">
                        You were invited to join
                        <strong>{{ $invite->company->name }}</strong>
                        as <strong>{{ $invite->role->name }}</strong>.
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration type</label>
                        <div class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="register_type" value="normal" checked>
                                <span>Regular user</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="register_type" value="owner">
                                <span>Company owner (create company)</span>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                           class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- full name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" required autofocus
                           class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                    @error('full_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if(!isset($invite) || ! $invite)
                    <!-- Company fields for owner -->
                    <div data-owner-fields>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company name (for owner)</label>
                        <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}"
                               class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                        @error('company_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div data-owner-fields>
                        <label for="company_domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company domain (optional)</label>
                        <input id="company_domain" name="company_domain" type="text" value="{{ old('company_domain') }}"
                               class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                        @error('company_domain')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div data-owner-fields>
                        <label for="company_plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company plan</label>
                        <select id="company_plan" name="company_plan"
                                class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                            <option value="free" {{ old('company_plan') === 'free' ? 'selected' : '' }}>free</option>
                            <option value="pro" {{ old('company_plan') === 'pro' ? 'selected' : '' }}>pro</option>
                            <option value="pro_enterprise" {{ old('company_plan') === 'pro_enterprise' ? 'selected' : '' }}>pro_enterprise</option>
                        </select>
                        @error('company_plan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email', isset($invite) && $invite ? $invite->email : '') }}" required
                           class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                    @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                    @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100">
                    @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div>
                    <button type="submit"
                            class="w-full px-4 py-2 font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        Register
                    </button>
                </div>

                <p class="text-sm text-center text-gray-600 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Login</a>
                </p>
            </form>
        </div>
    </div>
    @if(!isset($invite) || ! $invite)
        <script>
            (function () {
                const ownerFields = document.querySelectorAll('[data-owner-fields]');
                const radios = document.querySelectorAll('input[name="register_type"]');
                const toggle = () => {
                    const value = document.querySelector('input[name="register_type"]:checked')?.value;
                    ownerFields.forEach(el => {
                        el.style.display = value === 'owner' ? '' : 'none';
                    });
                };
                radios.forEach(r => r.addEventListener('change', toggle));
                toggle();
            })();
        </script>
    @endif
@endsection
