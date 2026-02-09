<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => 'tickets', 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded bg-green-50 px-4 py-2 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <main class="lg:col-span-8">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900">{{ $ticket->title }}</h3>
                            <a href="{{ route('admin.projects.tickets.create', $project->id) }}"
                               class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                                New Ticket
                            </a>
                        </div>

                        <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">Type</div>
                                <div class="mt-1 font-medium">{{ $ticket->type?->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">Status</div>
                                <div class="mt-1 inline-block text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">{{ $ticket->status?->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">Priority</div>
                                <div class="mt-1 inline-block text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">{{ $ticket->priority?->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">Category</div>
                                <div class="mt-1 font-medium">{{ $ticket->category?->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">Assignee</div>
                                <div class="mt-1 font-medium">{{ $ticket->assignee?->name ?? 'Unassigned' }}</div>
                            </div>
                        </div>

                        <div class="mt-6 text-sm text-gray-600">
                            <p>{{ $ticket->description ?: 'No description.' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900">Activity</h4>
                        <div class="mt-4 space-y-4 text-sm text-gray-600">
                            @forelse($ticket->activities as $activity)
                                <div class="border-l-2 border-gray-200 pl-3">
                                    <div class="text-xs text-gray-400">{{ $activity->created_at?->format('Y-m-d H:i') }}</div>
                                    <div>{{ $activity->type }}</div>
                                </div>
                            @empty
                                <p class="text-gray-500">No activity yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900">Comments</h4>
                        <div class="mt-4 space-y-4">
                            @forelse($ticket->comments as $comment)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="text-xs text-gray-400">{{ $comment->user?->name }} · {{ $comment->created_at?->format('Y-m-d H:i') }}</div>
                                    <div class="mt-1 text-sm text-gray-700">{{ $comment->body }}</div>
                                </div>
                            @empty
                                <p class="text-gray-500">No comments yet.</p>
                            @endforelse
                        </div>

                        <form class="mt-4" method="post" action="{{ route('admin.projects.tickets.comments.store', [$project->id, $ticket->id]) }}">
                            @csrf
                            <textarea name="body" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Write a comment...">{{ old('body') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('body')" />
                            <div class="mt-3">
                                <x-primary-button>{{ __('Post Comment') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </main>

                <aside class="lg:col-span-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                        <h4 class="text-lg font-medium text-gray-900">Log Time</h4>
                        <form class="mt-4 space-y-4" method="post" action="{{ route('admin.projects.tickets.time-logs.store', [$project->id, $ticket->id]) }}">
                            @csrf
                            <div>
                                <x-input-label for="minutes" :value="__('Minutes')" />
                                <x-text-input id="minutes" name="minutes" type="number" min="1" max="1440" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="logged_at" :value="__('Date/Time (optional)')" />
                                <x-text-input id="logged_at" name="logged_at" type="datetime-local" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="time_description" :value="__('Description (optional)')" />
                                <x-text-input id="time_description" name="description" type="text" class="mt-1 block w-full" />
                            </div>
                            <x-primary-button>{{ __('Add time') }}</x-primary-button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900">Update Ticket</h4>
                        <form class="mt-4 space-y-4" method="post" action="{{ route('admin.projects.tickets.update', [$project->id, $ticket->id]) }}">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="status_id" :value="__('Status')" />
                                <select id="status_id" name="status_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $ticket->status_id === $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="priority_id" :value="__('Priority')" />
                                <select id="priority_id" name="priority_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ $ticket->priority_id === $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="category_id" :value="__('Category')" />
                                <select id="category_id" name="category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $ticket->category_id === $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="type_id" :value="__('Type')" />
                                <select id="type_id" name="type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ $ticket->type_id === $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="assignee_id" :value="__('Assignee')" />
                                <select id="assignee_id" name="assignee_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Unassigned</option>
                                    @foreach($assignees as $assignee)
                                        <option value="{{ $assignee->id }}" {{ $ticket->assignee_id === $assignee->id ? 'selected' : '' }}>
                                            {{ $assignee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                              :value="old('title', $ticket->title)" required />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $ticket->description) }}</textarea>
                            </div>

                            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
