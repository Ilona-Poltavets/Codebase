<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/85">
    <div class="container mx-auto flex flex-wrap items-center justify-between gap-4 px-6 py-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
            <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Codebase</span>
        </a>

        <nav class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-100/80 p-1 dark:border-slate-700 dark:bg-slate-800/70">
            <a href="{{ url('/') }}" class="rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->is('/') ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-600 hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300' }}">Home</a>
            <a href="{{ route('about') }}" class="rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('about') ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-600 hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300' }}">About</a>
            <a href="{{ route('contact') }}" class="rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('contact') ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-600 hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300' }}">Contact</a>
        </nav>

        <div class="flex items-center gap-3">
            <button id="theme-toggle" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 transition hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-200">
                Theme
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-200">Login</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">Sign up</a>
            @endauth
        </div>
    </div>
</header>
