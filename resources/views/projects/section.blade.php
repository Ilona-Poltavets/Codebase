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
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tickets</h3>
                                <div class="text-sm text-gray-500">Status chart placeholder</div>
                            </div>
                            <div class="mt-4 h-48 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-400">
                                Chart placeholder (status breakdown by existing ticket statuses)
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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Time</h3>
                            <p class="mt-2 text-sm text-gray-500">Charts and reports placeholder.</p>
                            <div class="mt-4 h-40 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-400">
                                Time tracking dashboard
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
