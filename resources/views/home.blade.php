@extends('layouts.main')

@section('title', 'Home Page')

@section('content')
    <div class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800/70">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(6,182,212,0.12),_transparent_40%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.10),_transparent_35%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.18),_transparent_40%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_35%)]"></div>
            <div class="container mx-auto px-6 py-16 lg:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="mb-4 inline-flex rounded-full border border-cyan-500/35 bg-cyan-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:border-cyan-400/40 dark:text-cyan-200">
                            Project Workspace
                        </p>
                        <h1 class="text-4xl font-bold leading-tight text-slate-900 md:text-5xl dark:text-white">
                            Manage projects without chaos
                        </h1>
                        <p class="mt-6 max-w-xl text-lg text-slate-600 dark:text-slate-300">
                            Codebase is a lightweight project management system for small teams who want structure without complexity.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('login') }}" class="rounded-lg bg-emerald-400 px-6 py-3 font-semibold text-slate-950 transition hover:bg-emerald-300">
                                Try Demo
                            </a>
                            <a href="{{ route('register') }}" class="rounded-lg border border-slate-300 bg-white/80 px-6 py-3 font-semibold text-slate-800 transition hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-600 dark:bg-slate-900/70 dark:text-slate-100 dark:hover:border-cyan-400 dark:hover:text-cyan-200">
                                Create Account
                            </a>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-slate-300 bg-[#eaf1ff] p-5 shadow-[0_20px_40px_rgba(15,23,42,0.15)] dark:border-slate-800 dark:bg-[#071329] dark:shadow-[0_25px_55px_rgba(2,8,23,0.75)]">
                        <div class="rounded-2xl border border-slate-300 bg-[#f4f8ff] p-4 dark:border-slate-700/80 dark:bg-[#0b1a37]">
                            <div class="mb-4 flex items-center justify-between rounded-xl border border-slate-300 bg-white/80 px-4 py-3 dark:border-slate-700/70 dark:bg-slate-900/50">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Board Workspace</p>
                                    <p class="text-xl font-semibold text-slate-900 dark:text-white">Mercuria Core</p>
                                </div>
                                <span class="rounded-lg bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950">New Ticket</span>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="rounded-xl border border-slate-300 bg-white/80 p-3 dark:border-slate-700/70 dark:bg-slate-950/70">
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="font-semibold">In progress</p>
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs dark:bg-slate-800">2</span>
                                    </div>
                                    <div class="rounded-lg border border-slate-300 bg-slate-50 p-3 text-sm text-slate-700 dark:border-slate-700/70 dark:bg-slate-900/70 dark:text-slate-300">Fix ticket status sync edge case</div>
                                </div>
                                <div class="rounded-xl border border-slate-300 bg-white/80 p-3 dark:border-slate-700/70 dark:bg-slate-950/70">
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="font-semibold">In review</p>
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs dark:bg-slate-800">1</span>
                                    </div>
                                    <div class="rounded-lg border border-slate-300 bg-slate-50 p-3 text-sm text-slate-700 dark:border-slate-700/70 dark:bg-slate-900/70 dark:text-slate-300">Improve card design on board</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-slate-900 md:text-4xl dark:text-white">Tired of messy project tools?</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 text-slate-700 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200">Too many notifications</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 text-slate-700 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200">Hard to track real work</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 text-slate-700 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200">Complicated UI</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 text-slate-700 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200">No clarity on deadlines</div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-cyan-50/40 dark:border-slate-800/70 dark:bg-slate-900/40">
            <div class="container mx-auto px-6 py-16">
                <h2 class="text-3xl font-bold text-slate-900 md:text-4xl dark:text-white">Codebase helps your team stay focused.</h2>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-cyan-500/35 bg-cyan-500/10 px-5 py-4 dark:border-cyan-400/35">Kanban boards</div>
                    <div class="rounded-xl border border-cyan-500/35 bg-cyan-500/10 px-5 py-4 dark:border-cyan-400/35">Time tracking</div>
                    <div class="rounded-xl border border-cyan-500/35 bg-cyan-500/10 px-5 py-4 dark:border-cyan-400/35">Team roles</div>
                    <div class="rounded-xl border border-cyan-500/35 bg-cyan-500/10 px-5 py-4 dark:border-cyan-400/35">Activity logs</div>
                    <div class="rounded-xl border border-cyan-500/35 bg-cyan-500/10 px-5 py-4 dark:border-cyan-400/35">Reports</div>
                </div>
            </div>
        </section>

        <section class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-slate-900 md:text-4xl dark:text-white">Built for teams like yours</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-5 text-center font-medium dark:border-slate-800 dark:bg-slate-900/70">Small dev teams</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-5 text-center font-medium dark:border-slate-800 dark:bg-slate-900/70">Startups</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-5 text-center font-medium dark:border-slate-800 dark:bg-slate-900/70">Freelancers</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-5 text-center font-medium dark:border-slate-800 dark:bg-slate-900/70">Agencies</div>
            </div>
        </section>

        <section class="border-t border-slate-200 pb-20 pt-16 text-center dark:border-slate-800/70">
            <div class="container mx-auto px-6">
                <h2 class="text-3xl font-bold text-slate-900 md:text-4xl dark:text-white">Start organizing your projects today.</h2>
                <a href="{{ route('register') }}" class="mt-8 inline-flex rounded-lg bg-emerald-400 px-8 py-4 text-lg font-semibold text-slate-950 transition hover:bg-emerald-300">
                    Create Account
                </a>
            </div>
        </section>
    </div>
@endsection
