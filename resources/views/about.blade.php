@extends('layouts.main')

@section('title', 'About Codebase')

@section('content')
    <div class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800/70">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(6,182,212,0.12),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.10),_transparent_38%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_38%)]"></div>
            <div class="container relative mx-auto px-6 py-16 lg:py-20">
                <p class="mb-3 inline-flex rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:border-cyan-400/40 dark:text-cyan-200">
                    About Codebase
                </p>
                <h1 class="text-4xl font-bold leading-tight text-slate-900 md:text-5xl dark:text-white">What is Codebase?</h1>
                <p class="mt-6 max-w-3xl text-lg text-slate-600 dark:text-slate-300">
                    Codebase is a modern project management platform designed for small teams who want structure without complexity.
                </p>
                <p class="mt-4 max-w-3xl text-lg text-slate-600 dark:text-slate-300">
                    The goal is simple: help teams stay organized, focused, and productive without overwhelming interfaces.
                </p>
            </div>
        </section>

        <section class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">What Codebase Includes</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Kanban boards</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Task tracking</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Time logging</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Team roles and permissions</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Activity history</div>
                <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Project documentation</div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-cyan-50/40 dark:border-slate-800/70 dark:bg-slate-900/40">
            <div class="container mx-auto grid gap-10 px-6 py-16 lg:grid-cols-2">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Why Codebase Exists</h2>
                    <p class="mt-5 text-slate-600 dark:text-slate-300">
                        Many project management tools are either too simple for growing teams or too complex and overloaded.
                    </p>
                    <p class="mt-4 text-slate-600 dark:text-slate-300">
                        Codebase stays in the middle with a clean interface, clear workflow, transparent responsibility, and measurable productivity.
                    </p>
                    <p class="mt-4 text-slate-600 dark:text-slate-300">
                        It is built for teams who want control, not chaos.
                    </p>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Who It Is For</h2>
                    <div class="mt-5 grid gap-3">
                        <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Small development teams</div>
                        <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Startups</div>
                        <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Agencies</div>
                        <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Freelancers working in teams</div>
                        <div class="rounded-xl border border-slate-300 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70">Technical founders</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container mx-auto grid gap-10 px-6 py-16 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">How Codebase Is Different</h2>
                <p class="mt-5 text-slate-600 dark:text-slate-300">
                    Codebase is evolving into a productivity-driven SaaS platform with AI-assisted workflow optimization.
                </p>
                <p class="mt-4 text-slate-600 dark:text-slate-300">
                    The long-term vision is not just task management, but intelligent project guidance that helps teams decide what to do next.
                </p>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Technology</h2>
                <div class="mt-5 grid gap-3">
                    <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-5 py-4">Laravel (Backend)</div>
                    <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-5 py-4">Vue.js (Frontend)</div>
                    <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-5 py-4">REST API architecture</div>
                    <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-5 py-4">Secure cloud-ready infrastructure</div>
                    <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-5 py-4">Scalable SaaS design principles</div>
                </div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-slate-100/70 dark:border-slate-800/70 dark:bg-slate-900/50">
            <div class="container mx-auto px-6 py-16">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Roadmap</h2>
                <div class="mt-8 space-y-5">
                    <article class="rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                        <h3 class="text-xl font-semibold">Phase 1 - Product Foundation</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Multi-tenant architecture, advanced roles/permissions, secure auth, onboarding experience, usage analytics.</p>
                        <p class="mt-3 font-medium text-cyan-700 dark:text-cyan-300">Goal: production-ready SaaS core.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                        <h3 class="text-xl font-semibold">Phase 2 - Productivity Features</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Email and push notifications, weekly summaries, reporting dashboard, templates, real-time board updates.</p>
                        <p class="mt-3 font-medium text-cyan-700 dark:text-cyan-300">Goal: stronger collaboration and transparency.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                        <h3 class="text-xl font-semibold">Phase 3 - Billing and Subscription Model</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Plans (Free/Pro/Team), Stripe integration, usage limits, trials, subscription management panel.</p>
                        <p class="mt-3 font-medium text-cyan-700 dark:text-cyan-300">Goal: sustainable SaaS growth.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                        <h3 class="text-xl font-semibold">Phase 4 - AI Assistant</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Task generation, subtask splitting, weekly reports, workload analysis, prioritization suggestions, deadline risk detection.</p>
                        <p class="mt-3 font-medium text-cyan-700 dark:text-cyan-300">Goal: intelligent productivity system.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-800 dark:bg-slate-900/70">
                        <h3 class="text-xl font-semibold">Phase 5 - Integrations and Expansion</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">GitHub, Slack, calendar sync, public API, usage-based AI billing, advanced analytics.</p>
                        <p class="mt-3 font-medium text-cyan-700 dark:text-cyan-300">Goal: extensible ecosystem.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Long-Term Direction</h2>
            <p class="mt-5 max-w-4xl text-slate-600 dark:text-slate-300">
                Codebase is being developed as a real SaaS product, a scalable multi-tenant system, and a platform with AI-powered workflow optimization.
                The mission is to help teams think clearly, execute faster, and grow without losing structure.
            </p>
        </section>
    </div>
@endsection
