<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => $section, 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6">
                <main>
                    @if($section === 'overview')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                <div class="text-xs uppercase tracking-wider text-gray-400">New</div>
                                <div class="mt-2 text-3xl font-semibold text-gray-800">{{ $statusCounts['new'] }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                <div class="text-xs uppercase tracking-wider text-gray-400">In progress</div>
                                <div class="mt-2 text-3xl font-semibold text-gray-800">{{ $statusCounts['in_progress'] }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                <div class="text-xs uppercase tracking-wider text-gray-400">Done</div>
                                <div class="mt-2 text-3xl font-semibold text-gray-800">{{ $statusCounts['done'] }}</div>
                            </div>
                        </div>

                        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Tickets</h3>
                                <div class="text-sm text-gray-500">Pie chart placeholder</div>
                            </div>
                            <div class="mt-4 h-48 rounded-lg border border-dashed border-gray-300 flex items-center justify-center text-gray-400">
                                Pie chart (status breakdown)
                            </div>

                            <div class="mt-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($tickets as $ticket)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-medium text-gray-800">{{ $ticket['title'] }}</h4>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">{{ $ticket['status'] }}</span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                {{ $ticket['type'] }} · {{ $ticket['category'] }} · {{ $ticket['priority'] }} priority
                                            </div>
                                            <div class="mt-3 text-xs text-gray-400">Assigned: {{ $ticket['assignee'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($section === 'tickets')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Tickets</h3>
                                <button class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">New ticket</button>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach($tickets as $ticket)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-800">{{ $ticket['title'] }}</h4>
                                            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">{{ $ticket['status'] }}</span>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ $ticket['type'] }} · {{ $ticket['category'] }} · {{ $ticket['priority'] }} priority
                                        </div>
                                        <div class="mt-3 text-xs text-gray-400">Assigned: {{ $ticket['assignee'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($section === 'files')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Files</h3>
                            <p class="mt-2 text-sm text-gray-500">Storage placeholder. Upload and list files here.</p>
                        </div>
                    @elseif($section === 'time')
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Time</h3>
                            <p class="mt-2 text-sm text-gray-500">Charts and reports placeholder.</p>
                            <div class="mt-4 h-40 rounded-lg border border-dashed border-gray-300 flex items-center justify-center text-gray-400">
                                Time tracking dashboard
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
