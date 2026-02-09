<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => 'tickets', 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Tickets</h3>
                    <a href="{{ route('admin.projects.tickets.create', $project->id) }}"
                       class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                        New Ticket
                    </a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($tickets as $ticket)
                        <a href="{{ route('admin.projects.tickets.show', [$project->id, $ticket->id]) }}"
                           class="block border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-800">{{ $ticket->title }}</h4>
                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">{{ $ticket->status?->name }}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $ticket->type?->name }} · {{ $ticket->category?->name }} · {{ $ticket->priority?->name }}
                            </div>
                            <div class="mt-3 text-xs text-gray-400">
                                Assigned: {{ $ticket->assignee?->name ?? 'Unassigned' }}
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500">No tickets yet.</p>
                    @endforelse
                </div>

                @if(method_exists($tickets, 'links'))
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
