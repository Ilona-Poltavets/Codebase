@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 text-center mb-6">Login to Your Account</h2>

            @if(session('status'))
                <div class="bg-red-100 text-red-700 p-2 rounded mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-gray-700 dark:text-gray-300">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-700 dark:text-gray-300">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-indigo-600">
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Forgot Password?</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2 rounded-lg font-semibold transition">
                        Login
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-gray-600 dark:text-gray-400 text-sm">
                Don't have an account?
                <a href="" class="text-indigo-600 dark:text-indigo-400 hover:underline">Register</a>
            </p>
        </div>
    </div>
@endsection
