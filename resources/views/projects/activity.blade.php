<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => $section, 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Activity Feed</h3>
                        <p class="mt-1 text-sm text-gray-500">Project-wide events and changes.</p>
                    </div>
                    <a href="{{ route('admin.projects.activity.rss', ['project' => $project->id, 'types' => implode(',', $selectedTypes)]) }}"
                       class="px-3 py-2 rounded-md bg-orange-600 hover:bg-orange-500 text-white text-sm">
                        Export RSS
                    </a>
                </div>

                <form method="get" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <span class="block text-xs uppercase tracking-wider text-gray-500 mb-2">Event types</span>
                        <div class="max-h-40 overflow-auto rounded-md border border-gray-200 dark:border-gray-700 p-3 space-y-2">
                            @forelse($availableTypes as $type)
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" name="types[]" value="{{ $type }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ in_array($type, $selectedTypes, true) ? 'checked' : '' }}>
                                    <span>{{ $type }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No event types yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                            Apply
                        </button>
                        <a href="{{ route('admin.projects.activity', ['project' => $project->id]) }}"
                           class="flex-1 px-3 py-2 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm text-center">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse($logs as $log)
                        <article class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $log->event_type }}</p>
                                <p class="text-xs text-gray-500">{{ $log->created_at?->format('d.m.Y H:i:s') }}</p>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $log->user?->name ?? 'System' }}
                            </p>
                            @if(!empty($log->details))
                                <pre class="mt-2 rounded bg-gray-50 dark:bg-gray-900 p-2 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No activity yet.</p>
                    @endforelse
                </div>

                @if($logs->hasPages())
                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
