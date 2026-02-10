<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => 'repositories', 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded bg-green-50 px-4 py-2 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $repository->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">Repository Browser</p>
                    </div>
                    <form method="post" action="{{ route('admin.projects.repositories.destroy', [$project->id, $repository->id]) }}"
                          onsubmit="return confirm('Delete repository {{ $repository->name }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-md bg-red-600 hover:bg-red-500 text-white text-sm">
                            Delete repository
                        </button>
                    </form>
                </div>

                <div class="mt-4">
                    <label for="clone-url" class="block text-xs uppercase tracking-wider text-gray-400">Git clone</label>
                    <div class="mt-1 flex gap-2">
                        <input id="clone-url" type="text" readonly value="{{ $cloneUrl }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('clone-url').value)"
                                class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                            Copy
                        </button>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <a href="{{ route('admin.projects.repositories.show', [$project->id, $repository->id]) }}" class="hover:underline">root</a>
                        @php($builtPath = '')
                        @foreach(explode('/', trim($relativePath, '/')) as $segment)
                            @continue($segment === '')
                            @php($builtPath = trim($builtPath . '/' . $segment, '/'))
                            <span>/</span>
                            <a href="{{ route('admin.projects.repositories.show', [$project->id, $repository->id]) }}?path={{ urlencode($builtPath) }}"
                               class="hover:underline">{{ $segment }}</a>
                        @endforeach
                    </div>
                </div>

                @if($isFileView)
                    <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200">
                            {{ $relativePath }}
                        </div>
                        <pre class="p-4 text-xs bg-gray-950 text-gray-100 overflow-x-auto">{{ $fileContent }}</pre>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">Size</th>
                                <th class="px-4 py-2 text-left">Modified</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                            @forelse($repoFiles as $item)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('admin.projects.repositories.show', [$project->id, $repository->id]) }}?path={{ urlencode($item['path']) }}"
                                           class="text-indigo-600 dark:text-indigo-300 hover:underline">
                                            {{ $item['name'] }}{{ $item['is_dir'] ? '/' : '' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $item['is_dir'] ? 'Directory' : 'File' }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $item['size'] !== null ? number_format($item['size'] / 1024, 2) . ' KB' : '-' }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $item['modified_at'] ? date('Y-m-d H:i', $item['modified_at']) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-gray-500">Repository is empty.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
