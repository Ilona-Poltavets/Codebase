<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => $section, 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6">
                <main>
                    @if($section === 'overview')
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            @foreach($statusCounts as $status)
                                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                    <div class="text-xs uppercase tracking-wider text-gray-400">{{ $status['name'] }}</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-800 dark:text-gray-100">{{ $status['count'] }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            @php
                                $totalTickets = collect($statusCounts)->sum('count');
                                $maxTicketsInStatus = collect($statusCounts)->max('count') ?: 1;
                                $palette = ['#38bdf8', '#22c55e', '#f59e0b', '#f43f5e', '#a78bfa', '#14b8a6'];
                                $segments = [];
                                $legend = [];
                                $cursor = 0.0;

                                foreach ($statusCounts as $i => $status) {
                                    $count = (int) $status['count'];
                                    if ($count <= 0) {
                                        continue;
                                    }

                                    $color = $palette[$i % count($palette)];
                                    $percent = $totalTickets > 0 ? ($count / $totalTickets) * 100 : 0;
                                    $end = $cursor + $percent;
                                    $segments[] = $color.' '.number_format($cursor, 4, '.', '').'% '.number_format($end, 4, '.', '').'%';
                                    $legend[] = [
                                        'name' => $status['name'],
                                        'count' => $count,
                                        'percent' => $percent,
                                        'color' => $color,
                                    ];
                                    $cursor = $end;
                                }

                                $donutGradient = count($segments)
                                    ? 'conic-gradient('.implode(', ', $segments).')'
                                    : 'conic-gradient(#334155 0% 100%)';
                            @endphp

                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tickets Analytics</h3>
                                <div class="text-sm text-gray-500">Total: {{ $totalTickets }}</div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Status distribution</div>
                                    <div class="mt-4 flex items-center gap-4">
                                        <div class="relative h-36 w-36 shrink-0 rounded-full" style="background: {{ $donutGradient }};">
                                            <div class="absolute inset-4 rounded-full bg-white dark:bg-gray-800"></div>
                                            <div class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                {{ $totalTickets }}
                                            </div>
                                        </div>
                                        <div class="space-y-2 text-sm">
                                            @forelse($legend as $row)
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                                    <span class="text-gray-700 dark:text-gray-200">{{ $row['name'] }}</span>
                                                    <span class="text-gray-500">{{ $row['count'] }} ({{ number_format($row['percent'], 0) }}%)</span>
                                                </div>
                                            @empty
                                                <p class="text-gray-500">No tickets yet.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Status load</div>
                                    <div class="mt-4 space-y-3">
                                        @foreach($statusCounts as $i => $status)
                                            @php
                                                $count = (int) $status['count'];
                                                $width = $maxTicketsInStatus > 0 ? ($count / $maxTicketsInStatus) * 100 : 0;
                                                $color = $palette[$i % count($palette)];
                                            @endphp
                                            <div>
                                                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                                                    <span>{{ $status['name'] }}</span>
                                                    <span>{{ $count }}</span>
                                                </div>
                                                <div class="mt-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                    <div class="h-full rounded-full" style="width: {{ number_format($width, 2, '.', '') }}%; background-color: {{ $color }}"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($tickets as $ticket)
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-medium text-gray-800 dark:text-gray-100">{{ $ticket->title }}</h4>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ $ticket->status?->name }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                {{ $ticket->type?->name }} · {{ $ticket->category?->name }} · {{ $ticket->priority?->name }}
                                            </div>
                                            <div class="mt-3 text-xs text-gray-400">
                                                Assigned: {{ $ticket->assignee?->name ?? 'Unassigned' }}
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">No tickets in this project yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @elseif($section === 'tickets')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tickets</h3>
                                <a href="{{ route('admin.projects.tickets', $project->id) }}"
                                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                                    Open tickets
                                </a>
                            </div>
                            <p class="mt-4 text-sm text-gray-500">
                                Ticket list is available in the dedicated Tickets section.
                            </p>
                        </div>
                    @elseif($section === 'files')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Files</h3>
                            <p class="mt-2 text-sm text-gray-500">
                                File manager is available in the dedicated Files section.
                            </p>
                        </div>
                    @elseif($section === 'time')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Time Statistics</h3>
                                <form method="get" class="flex items-center gap-2 flex-wrap">
                                    <input type="hidden" name="period" id="period-input" value="{{ $timeFilter }}">
                                    <button type="submit" onclick="document.getElementById('period-input').value='today'"
                                            class="px-3 py-1 text-sm rounded {{ $timeFilter === 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                        Today
                                    </button>
                                    <button type="submit" onclick="document.getElementById('period-input').value='yesterday'"
                                            class="px-3 py-1 text-sm rounded {{ $timeFilter === 'yesterday' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                        Yesterday
                                    </button>
                                    <button type="submit" onclick="document.getElementById('period-input').value='this_week'"
                                            class="px-3 py-1 text-sm rounded {{ $timeFilter === 'this_week' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                        This week
                                    </button>
                                    <button type="submit" onclick="document.getElementById('period-input').value='this_month'"
                                            class="px-3 py-1 text-sm rounded {{ $timeFilter === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                        This month
                                    </button>
                                    <button type="button" onclick="document.getElementById('range-wrap').classList.toggle('hidden')"
                                            class="px-3 py-1 text-sm rounded {{ $timeFilter === 'range' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                        Range
                                    </button>
                                    <span id="range-wrap" class="{{ $timeFilter === 'range' ? '' : 'hidden' }} flex items-center gap-2">
                                        <input type="date" name="from" value="{{ $rangeFrom }}"
                                               class="rounded border-gray-300 text-sm">
                                        <input type="date" name="to" value="{{ $rangeTo }}"
                                               class="rounded border-gray-300 text-sm">
                                        <button type="submit" onclick="document.getElementById('period-input').value='range'"
                                                class="px-3 py-1 text-sm rounded bg-indigo-600 text-white">
                                            Apply
                                        </button>
                                    </span>
                                </form>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-xs uppercase tracking-wider text-gray-400">Project total</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-800 dark:text-gray-100">
                                        {{ number_format($projectTimeTotalMinutes / 60, 2) }} h
                                    </div>
                                </div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 md:col-span-2">
                                    <div class="text-xs uppercase tracking-wider text-gray-400">Filter</div>
                                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ strtoupper(str_replace('_', ' ', $timeFilter)) }}</div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500">By user</h4>
                                    <div class="mt-3 space-y-2">
                                        @forelse($timeByUser as $row)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex items-center justify-between">
                                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $row->user?->name ?? 'Unknown' }}</span>
                                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ number_format($row->total_minutes / 60, 2) }} h</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">No data for this period.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500">By ticket</h4>
                                    <div class="mt-3 space-y-2">
                                        @forelse($timeByTicket as $row)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex items-center justify-between gap-4">
                                                <span class="text-sm text-gray-700 dark:text-gray-200 truncate">{{ $row->ticket?->title ?? 'Unknown ticket' }}</span>
                                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ number_format($row->total_minutes / 60, 2) }} h</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">No data for this period.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Time log records</h4>
                                <div class="mt-3 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Date</th>
                                            <th class="px-4 py-2 text-left">User</th>
                                            <th class="px-4 py-2 text-left">Ticket</th>
                                            <th class="px-4 py-2 text-left">Minutes</th>
                                            <th class="px-4 py-2 text-left">Description</th>
                                        </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800">
                                        @forelse($timeLogs as $log)
                                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                                <td class="px-4 py-2">{{ $log->logged_at?->format('Y-m-d H:i') }}</td>
                                                <td class="px-4 py-2">{{ $log->user?->name ?? 'Unknown' }}</td>
                                                <td class="px-4 py-2">{{ $log->ticket?->title ?? 'Unknown ticket' }}</td>
                                                <td class="px-4 py-2">{{ $log->minutes }}</td>
                                                <td class="px-4 py-2">{{ $log->description ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-3 text-gray-500">No records for selected period.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
