<footer class="mt-14 border-t border-slate-200 bg-gradient-to-b from-white to-slate-100 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="container mx-auto grid gap-10 px-6 py-12 md:grid-cols-3">
        <div>
            <div class="inline-flex items-center gap-3">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100">Codebase</p>
            </div>
            <p class="mt-3 max-w-xs text-sm text-slate-600 dark:text-slate-300">
                Lightweight project management for teams that need clarity, speed, and structure.
            </p>
        </div>

        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Navigation</p>
            <div class="mt-4 grid gap-2">
                <a href="{{ url('/') }}" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">Home</a>
                <a href="{{ route('about') }}" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">About</a>
                <a href="{{ route('contact') }}" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">Contact</a>
            </div>
        </div>

        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Connect</p>
            <div class="mt-4 grid gap-2">
                <a href="mailto:{{ config('services.codebase.contact_email') }}" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">{{ config('services.codebase.contact_email') }}</a>
                <a href="{{ config('services.codebase.github_url') }}" target="_blank" rel="noopener noreferrer" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">GitHub</a>
                <a href="{{ config('services.codebase.linkedin_url') }}" target="_blank" rel="noopener noreferrer" class="text-slate-700 transition hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300">LinkedIn</a>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200/80 py-4 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
        &copy; {{ date('Y') }} Codebase. All rights reserved.
    </div>
</footer>
