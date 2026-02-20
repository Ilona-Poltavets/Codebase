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

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                <aside class="xl:col-span-1 space-y-4">
                    <section class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                        <h3 class="text-sm uppercase tracking-wider text-gray-500">Wiki Pages</h3>
                        <div class="mt-3 space-y-2">
                            @forelse($pages as $page)
                                <a href="{{ route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $page->id]) }}"
                                   class="block rounded px-3 py-2 text-sm {{ $selectedPage && $selectedPage->id === $page->id ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                    <span class="font-medium">{{ $page->title }}</span>
                                    <span class="ml-1 text-xs text-gray-400">v{{ $page->versions_count }}</span>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">No wiki pages yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                        <h3 class="text-sm uppercase tracking-wider text-gray-500">Create Page</h3>
                        <form method="post" action="{{ route('admin.projects.wiki.store', $project->id) }}" class="mt-3 space-y-3">
                            @csrf
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="Page title"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                            <textarea name="content" rows="6" placeholder="Initial markdown..."
                                      class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">{{ old('content') }}</textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                                Create page
                            </button>
                        </form>
                    </section>
                </aside>

                <section class="xl:col-span-3 space-y-6">
                    @if($selectedPage)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $selectedPage->title }}</h2>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Last update: {{ $selectedPage->updated_at?->format('d.m.Y H:i:s') }}
                                        @if($selectedPage->editor)
                                            by {{ $selectedPage->editor->name }}
                                        @endif
                                    </p>
                                    @if($selectedVersion)
                                        <p class="mt-1 text-xs text-amber-600">
                                            Viewing historical version v{{ $selectedVersion->version }}.
                                            <a class="underline" href="{{ route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $selectedPage->id]) }}">Back to latest</a>
                                        </p>
                                    @endif
                                </div>
                                <form method="post" action="{{ route('admin.projects.wiki.destroy', ['project' => $project->id, 'wikiPage' => $selectedPage->id]) }}"
                                      onsubmit="return confirm('Delete wiki page {{ $selectedPage->title }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 rounded-md bg-red-600 hover:bg-red-500 text-white text-sm">
                                        Delete page
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <h3 class="text-sm uppercase tracking-wider text-gray-500">Markdown Editor</h3>
                                <form method="post" action="{{ route('admin.projects.wiki.update', ['project' => $project->id, 'wikiPage' => $selectedPage->id]) }}" class="mt-3 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="title" value="{{ old('title', $selectedPage->title) }}"
                                           class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                    <textarea id="wiki-editor" name="content" rows="16"
                                              class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm font-mono">{{ old('content', $sourceContent) }}</textarea>
                                    <button type="submit" class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                                        Save changes
                                    </button>
                                </form>
                            </div>

                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <h3 class="text-sm uppercase tracking-wider text-gray-500">Version History</h3>
                                <div class="mt-3 space-y-2 max-h-[460px] overflow-auto pr-1">
                                    @forelse($selectedPage->versions as $version)
                                        <a href="{{ route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $selectedPage->id, 'version_id' => $version->id]) }}"
                                           class="block rounded border px-3 py-2 text-sm {{ $selectedVersion && $selectedVersion->id === $version->id ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                            <div class="font-medium">v{{ $version->version }}</div>
                                            <div class="text-xs text-gray-500">{{ $version->created_at?->format('d.m.Y H:i:s') }}</div>
                                            <div class="text-xs text-gray-400">{{ $version->editor?->name ?? 'Unknown user' }}</div>
                                        </a>
                                    @empty
                                        <p class="text-sm text-gray-500">No versions yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                            <h3 class="text-sm uppercase tracking-wider text-gray-500">Preview</h3>
                            <article id="wiki-preview"
                                     class="prose prose-sm dark:prose-invert max-w-none mt-3 text-gray-800 dark:text-gray-100">
                                {!! $renderedContent ?: '<p class="text-gray-500">No content.</p>' !!}
                            </article>
                        </div>
                    @else
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Wiki is empty</h3>
                            <p class="mt-2 text-sm text-gray-500">Create your first page to start documenting project decisions and notes.</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const editor = document.getElementById('wiki-editor');
            const preview = document.getElementById('wiki-preview');
            if (!editor || !preview) return;

            const escapeHtml = (value) => value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const inline = (value) => value
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/`(.+?)`/g, '<code>$1</code>')
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

            const render = () => {
                const lines = editor.value.split('\n');
                const html = [];
                let inCode = false;

                for (const rawLine of lines) {
                    const line = escapeHtml(rawLine);

                    if (line.startsWith('```')) {
                        inCode = !inCode;
                        html.push(inCode ? '<pre><code>' : '</code></pre>');
                        continue;
                    }

                    if (inCode) {
                        html.push(line);
                        continue;
                    }

                    if (line.startsWith('### ')) {
                        html.push('<h3>' + inline(line.slice(4)) + '</h3>');
                    } else if (line.startsWith('## ')) {
                        html.push('<h2>' + inline(line.slice(3)) + '</h2>');
                    } else if (line.startsWith('# ')) {
                        html.push('<h1>' + inline(line.slice(2)) + '</h1>');
                    } else if (line.trim() === '') {
                        html.push('<br>');
                    } else {
                        html.push('<p>' + inline(line) + '</p>');
                    }
                }

                preview.innerHTML = html.join('');
            };

            editor.addEventListener('input', render);
            render();
        })();
    </script>
</x-app-layout>
