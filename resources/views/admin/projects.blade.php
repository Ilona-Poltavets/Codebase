<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>
            <a href="{{ route('admin.projects.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">
                Add Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 rounded bg-green-50 px-4 py-2 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-900">
                        <thead>
                        <tr>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">ID</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Name</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Company</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Created At</th>
                            <th class="border border-gray-300 dark:border-gray-900 px-4 py-2">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $project->id }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">{{ $project->name }}</td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">
                                    {{ $project->company?->name ?? '-' }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2">
                                    {{ $project->created_at ? $project->created_at->format('Y-m-d') : '-' }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-900 px-4 py-2 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('admin.projects.edit', $project->id) }}"
                                           class="text-green-500 hover:text-green-700" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 4h2m2 0h2m-6 0h2m4 0h2m-6 0h2m-6 0h2M4 20h16M4 4h16v16H4V4zm10 4l2 2-8 8H6v-2l8-8z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this project?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138
                                                              21H7.862a2 2 0 01-1.995-1.858L5
                                                              7m5 4v6m4-6v6m1-10V4a1 1 0
                                                              00-1-1h-4a1 1 0 00-1
                                                              1v3m-4 0h14" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    @if(method_exists($projects, 'links'))
                        <div class="mt-4">
                            {{ $projects->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
