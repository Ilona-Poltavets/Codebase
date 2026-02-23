<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight dark:text-gray-100">Workspace Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">One place for board work, project flow and time tracking.</p>
            </div>
            <div class="flex items-center gap-2">
                @if($boardsProject)
                    <a href="{{ route('admin.projects.board', $boardsProject->id) }}"
                       class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-500">
                        Open Custom Trello
                    </a>
                @endif
                <a href="{{ route('admin.projects.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Projects
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <article class="rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-cyan-100 p-5 shadow-sm dark:border-sky-500/30 dark:from-sky-900/35 dark:to-cyan-900/25">
                    <p class="text-xs uppercase tracking-widest text-sky-700 dark:text-sky-300">Total Tickets</p>
                    <p class="mt-2 text-3xl font-semibold text-sky-900 dark:text-sky-100">{{ $totalTickets }}</p>
                </article>
                <article class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-lime-100 p-5 shadow-sm dark:border-emerald-500/30 dark:from-emerald-900/35 dark:to-lime-900/20">
                    <p class="text-xs uppercase tracking-widest text-emerald-700 dark:text-emerald-300">My Tickets</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900 dark:text-emerald-100">{{ $myTicketsCount }}</p>
                </article>
                <article class="rounded-xl border border-violet-200 bg-gradient-to-br from-violet-50 to-indigo-100 p-5 shadow-sm dark:border-violet-500/30 dark:from-violet-900/35 dark:to-indigo-900/25">
                    <p class="text-xs uppercase tracking-widest text-violet-700 dark:text-violet-300">Done Tickets</p>
                    <p class="mt-2 text-3xl font-semibold text-violet-900 dark:text-violet-100">{{ $doneTicketsCount }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <article class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Status Pulse</h3>
                        <span class="text-xs text-gray-500">Live from current company tickets</span>
                    </div>
                    @php
                        $maxStatusCount = max(1, (int) collect($statusBreakdown)->max('count'));
                        $colors = ['bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-indigo-500', 'bg-teal-500'];
                    @endphp
                    <div class="mt-5 space-y-4">
                        @forelse($statusBreakdown as $i => $status)
                            @php
                                $width = $maxStatusCount > 0 ? ($status['count'] / $maxStatusCount) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $status['name'] }}</span>
                                    <span class="text-gray-500">{{ $status['count'] }}</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full {{ $colors[$i % count($colors)] }}" style="width: {{ number_format($width, 2, '.', '') }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No status data yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Time Tracker Utility</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Desktop mini-tool for automatic task time tracking.
                        Manual time entries will stay available in tickets.
                    </p>
                    <div class="mt-5 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-700">Download coming soon</p>
                        <p class="text-xs text-gray-500 mt-1">Windows first release. Mac/Linux planned next.</p>
                        <button type="button" disabled
                                class="mt-4 w-full rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-500 cursor-not-allowed">
                            Download Tracker (Soon)
                        </button>
                    </div>
                    <ul class="mt-4 text-xs text-gray-500 space-y-1">
                        <li>Auto-track active task time</li>
                        <li>Map tracking to ticket IDs</li>
                        <li>Sync with board and ticket logs</li>
                    </ul>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Board Access</h3>
                        <a href="{{ route('admin.projects.index') }}" class="text-sm text-sky-600 hover:text-sky-500">All projects</a>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse($projects as $project)
                            <a href="{{ route('admin.projects.board', $project->id) }}"
                               class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                {{ $project->name }} <span class="text-gray-400">({{ $project->tickets_count }})</span>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">No projects available.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">My Queue</h3>
                        <span class="text-xs text-gray-500">Assigned and not done</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($myQueue as $ticket)
                            <a href="{{ route('admin.projects.tickets.show', [$ticket->project_id, $ticket->id]) }}"
                               class="block rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-800">{{ $ticket->title }}</p>
                                    <span class="text-xs rounded bg-gray-100 px-2 py-1 text-gray-600">{{ $ticket->status?->name }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ $ticket->project?->name }}</p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">Queue is empty. Nice.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Ticket Updates</h3>
                    <a href="{{ route('admin.projects.index') }}" class="text-sm text-sky-600 hover:text-sky-500">Open project areas</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wider text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-2 pr-4">Ticket</th>
                                <th class="py-2 pr-4">Project</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Assignee</th>
                                <th class="py-2 pr-4">Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets as $ticket)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4">
                                        <a href="{{ route('admin.projects.tickets.show', [$ticket->project_id, $ticket->id]) }}"
                                           class="text-gray-800 hover:text-sky-600">
                                            {{ $ticket->title }}
                                        </a>
                                    </td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $ticket->project?->name }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $ticket->status?->name }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                    <td class="py-3 pr-4 text-gray-500">{{ $ticket->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-3 text-gray-500">No recent ticket updates.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
