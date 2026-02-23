@extends('layouts.main')

@section('title', 'Contact')

@section('content')
    <div class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800/70">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(6,182,212,0.12),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.10),_transparent_38%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_38%)]"></div>
            <div class="container relative mx-auto px-6 py-16 lg:py-20">
                <p class="mb-3 inline-flex rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:border-cyan-400/40 dark:text-cyan-200">
                    Contact
                </p>
                <h1 class="text-4xl font-bold leading-tight text-slate-900 md:text-5xl dark:text-white">Get in touch</h1>
                <p class="mt-5 max-w-2xl text-slate-600 dark:text-slate-300">
                    If you have questions, feedback, or partnership ideas, send us a message.
                </p>
            </div>
        </section>

        <section class="container mx-auto grid gap-10 px-6 py-16 lg:grid-cols-2">
            <div class="space-y-4">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Contacts</h2>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                    <a href="mailto:{{ config('services.codebase.contact_email') }}" class="mt-1 inline-block font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                        {{ config('services.codebase.contact_email') }}
                    </a>
                </div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">
                    <p class="text-sm text-slate-500 dark:text-slate-400">GitHub</p>
                    <a href="{{ config('services.codebase.github_url') }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                        {{ config('services.codebase.github_url') }}
                    </a>
                </div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">
                    <p class="text-sm text-slate-500 dark:text-slate-400">LinkedIn</p>
                    <a href="{{ config('services.codebase.linkedin_url') }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                        {{ config('services.codebase.linkedin_url') }}
                    </a>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Feedback Form</h2>

                @if (session('status'))
                    <div class="mt-5 rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-emerald-700 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}" class="mt-5 space-y-4 rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 outline-none ring-cyan-500/40 focus:ring dark:border-slate-700 dark:bg-slate-950/60" required>
                        @error('name')
                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 outline-none ring-cyan-500/40 focus:ring dark:border-slate-700 dark:bg-slate-950/60" required>
                        @error('email')
                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="mb-2 block text-sm font-medium">Message</label>
                        <textarea id="message" name="message" rows="6" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 outline-none ring-cyan-500/40 focus:ring dark:border-slate-700 dark:bg-slate-950/60" required>{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex rounded-lg bg-emerald-400 px-6 py-3 font-semibold text-slate-950 transition hover:bg-emerald-300">
                        Send Message
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
