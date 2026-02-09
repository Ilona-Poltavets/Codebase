<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => 'tickets', 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Create Ticket</h3>

                <form class="mt-6 space-y-6" method="post" action="{{ route('admin.projects.tickets.store', $project->id) }}">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="5"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="status_id" :value="__('Status')" />
                            <select id="status_id" name="status_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ (int) old('status_id') === $status->id ? 'selected' : '' }}>
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
                                    <option value="{{ $priority->id }}" {{ (int) old('priority_id') === $priority->id ? 'selected' : '' }}>
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
                                    <option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>
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
                                    <option value="{{ $type->id }}" {{ (int) old('type_id') === $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="assignee_id" :value="__('Assignee')" />
                            <select id="assignee_id" name="assignee_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" {{ (int) old('assignee_id') === $assignee->id ? 'selected' : '' }}>
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create') }}</x-primary-button>
                        <a href="{{ route('admin.projects.tickets', $project->id) }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
