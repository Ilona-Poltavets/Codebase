@extends('layouts.main')

@section('title', 'Home Page')

@section('content')
    <!-- Hero Section -->
    <section class="bg-indigo-600 dark:bg-indigo-700 text-white py-20">
        <div class="container mx-auto text-center px-6">
            <h1 class="text-5xl font-bold mb-4">Welcome to Codebase</h1>
            <p class="text-xl mb-6">Beautiful and modern UI with Tailwind CSS and Laravel Blade</p>
            <a href="#features" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Get Started</a>
        </div>
    </section>

    <!-- Features / Cards Section -->
    <section id="features" class="container mx-auto py-20 px-6">
        <h2 class="text-3xl font-bold text-center mb-12">Our Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <h3 class="text-xl font-semibold mb-2">Feature One</h3>
                <p class="text-gray-600 dark:text-gray-300">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <h3 class="text-xl font-semibold mb-2">Feature Two</h3>
                <p class="text-gray-600 dark:text-gray-300">Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <h3 class="text-xl font-semibold mb-2">Feature Three</h3>
                <p class="text-gray-600 dark:text-gray-300">Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-indigo-50 dark:bg-gray-900 py-20 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to start?</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-6">Sign up today and make your project beautiful with MyApp.</p>
        <a href="#!" class="bg-indigo-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-indigo-500 transition">Sign Up</a>
    </section>
@endsection
