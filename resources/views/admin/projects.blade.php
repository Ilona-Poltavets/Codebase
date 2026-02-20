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

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-[820px] w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">ID</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Company</th>
                                    <th class="hidden md:table-cell px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Created At</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($projects as $project)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20">
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $project->id }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $project->name }}</td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                            {{ $project->company?->name ?? '-' }}
                                        </td>
                                        <td class="hidden md:table-cell px-4 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $project->created_at ? $project->created_at->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('admin.projects.overview', $project->id) }}"
                                           class="text-blue-500 hover:text-blue-700" title="Overview">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                                          9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                                                          0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
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
                        </div>
                    </div>

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
