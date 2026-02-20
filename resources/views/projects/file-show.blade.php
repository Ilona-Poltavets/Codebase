<x-app-layout>
    <x-slot name="header">
        @include('projects.partials.header', ['project' => $project, 'section' => $section, 'repositories' => $repositories])
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded bg-green-50 px-4 py-2 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-6">
                <section class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 dark:text-gray-400">File details</p>
                            <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100 break-all">{{ $file->name }}</h2>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                Version: <span class="font-semibold">v{{ $file->version }}</span>
                                @if($file->is_current)
                                    <span class="ml-2 inline-flex px-2 py-1 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs">Current</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('admin.projects.files', ['project' => $project->id, 'folder_id' => $file->folder_id]) }}"
                           class="h-10 px-4 rounded-md bg-gray-600 hover:bg-gray-500 text-white text-sm font-semibold inline-flex items-center">
                            Back to files
                        </a>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Versions</h3>
                    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr class="text-left text-sm">
                                <th class="px-4 py-3">Version</th>
                                <th class="px-4 py-3">Uploaded by</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                            @foreach($versions as $version)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100">
                                    <td class="px-4 py-3">
                                        v{{ $version->version }}
                                        @if($version->is_current)
                                            <span class="ml-2 inline-flex px-2 py-1 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs">Current</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $version->uploader?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $version->created_at?->format('d.m.Y H:i:s') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.projects.files.show', ['project' => $project->id, 'file' => $version->id]) }}"
                                               class="h-9 px-3 rounded-md bg-gray-600 hover:bg-gray-500 text-white text-xs font-semibold inline-flex items-center">
                                                Open
                                            </a>
                                            <a href="{{ route('admin.projects.files.download', ['project' => $project->id, 'file' => $version->id]) }}"
                                               class="h-9 px-3 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold inline-flex items-center">
                                                Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Comments</h3>
                    <form method="post" action="{{ route('admin.projects.files.comments.store', ['project' => $project->id, 'file' => $file->id]) }}" class="mt-4">
                        @csrf
                        <label for="comment-body" class="sr-only">Comment</label>
                        <textarea id="comment-body" name="body" rows="4"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Write comment...">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3">
                            <button type="submit"
                                    class="h-10 px-4 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold">
                                Add comment
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 space-y-3">
                        @forelse($file->comments as $comment)
                            <article class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? 'Unknown user' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at?->format('d.m.Y H:i:s') }}</p>
                                </div>
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $comment->body }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No comments yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
