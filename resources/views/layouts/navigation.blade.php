<nav x-data="{ open: false }" class="admin-topbar border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/85">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <div class="shrink-0">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="brand-font text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100">Codebase</span>
                </a>
            </div>

            <div class="hidden items-center gap-1 sm:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                @if(Auth::user()?->hasRole('admin') || Auth::user()?->hasRole('owner') || Auth::user()?->hasRole('manager'))
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('Users') }}</x-nav-link>
                @endif
                @if(Auth::user()?->hasRole('admin'))
                    <x-nav-link :href="route('admin.companies.index')" :active="request()->routeIs('admin.companies.*')">{{ __('Companies') }}</x-nav-link>
                    <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">{{ __('Roles') }}</x-nav-link>
                    <x-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')">{{ __('Permissions') }}</x-nav-link>
                @endif
                <x-nav-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.*')">{{ __('Projects') }}</x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            <button id="theme-toggle" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-200" aria-label="Toggle theme">
                <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8 8 0 1010.586 10.586z" />
                </svg>
                <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v1a1 1 0 11-2 0V4a1 1 0 011-1zm4.95 2.05a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 9a1 1 0 110 2h-1a1 1 0 110-2h1zM6.464 6.464a1 1 0 010 1.414l-.707.707A1 1 0 114.343 7.17l.707-.707a1 1 0 011.414 0zM5 10a1 1 0 100 2H4a1 1 0 100-2h1zm1.464 3.536a1 1 0 00-1.414 0l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 000-1.414zM10 14a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm4.95-.95a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414l-.707-.707zM13 10a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
            </button>

            <x-dropdown align="right" width="48" contentClasses="py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-200">
                        <span>{{ Auth::user()->name }}</span>
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <div class="-me-2 flex items-center sm:hidden">
            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-200 bg-white/95 px-3 pb-4 pt-2 dark:border-slate-800 dark:bg-slate-900/95 sm:hidden">
        <div class="space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            @if(Auth::user()?->hasRole('admin') || Auth::user()?->hasRole('owner') || Auth::user()?->hasRole('manager'))
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('Users') }}</x-responsive-nav-link>
            @endif
            @if(Auth::user()?->hasRole('admin'))
                <x-responsive-nav-link :href="route('admin.companies.index')" :active="request()->routeIs('admin.companies.*')">{{ __('Companies') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">{{ __('Roles') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')">{{ __('Permissions') }}</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.*')">{{ __('Projects') }}</x-responsive-nav-link>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="font-medium text-slate-800 dark:text-slate-100">{{ Auth::user()->name }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
