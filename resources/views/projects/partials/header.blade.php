<div class="flex flex-col gap-4">
    @if(session('error'))
        <div class="rounded bg-red-50 px-4 py-2 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-500">{{ $project->company?->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.projects.edit', $project->id) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm rounded-md hover:bg-gray-50">
                Edit Project
            </a>
            <a href="{{ route('admin.projects.index') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">
                Back to Projects
            </a>
        </div>
    </div>
    <nav class="flex flex-wrap items-center gap-2 rounded-lg bg-gray-100 border border-gray-200 px-3 py-2 overflow-visible relative z-40">
        <a href="{{ route('admin.projects.overview', $project->id) }}"
           class="px-3 py-2 rounded-md text-sm {{ $section === 'overview' ? 'bg-indigo-100 text-indigo-800' : 'text-gray-700 hover:bg-white' }}">
            Overview
        </a>
        <div class="relative z-50" id="repo-dropdown">
            <button type="button" id="repo-tab"
                    class="px-3 py-2 rounded-md text-sm {{ $section === 'repositories' ? 'bg-indigo-100 text-indigo-800' : 'text-gray-700 hover:bg-white' }} inline-flex items-center gap-2">
                Repositories
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="repo-menu" class="absolute left-0 top-full mt-2 w-56 rounded-lg border border-gray-200 bg-white shadow-lg hidden">
                <div class="p-2">
                    <button type="button" id="create-repository-btn" class="w-full text-left px-3 py-2 rounded-md text-sm text-indigo-700 hover:bg-indigo-50">
                        Create new repository...
                    </button>
                    <div class="my-2 border-t border-gray-100"></div>
                    <div class="text-xs uppercase tracking-wider text-gray-400 px-3 py-1">Existing</div>
                    @forelse($repositories as $repo)
                        <a href="{{ route('admin.projects.repositories.show', [$project->id, $repo->id]) }}"
                           class="block w-full text-left px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            {{ $repo->name }} <span class="text-xs text-gray-400">({{ strtoupper($repo->vcs_type ?? 'git') }})</span>
                        </a>
                    @empty
                        <div class="px-3 py-2 text-sm text-gray-400">No repositories yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <a href="{{ route('admin.projects.tickets', $project->id) }}"
           class="px-3 py-2 rounded-md text-sm {{ $section === 'tickets' ? 'bg-indigo-100 text-indigo-800' : 'text-gray-700 hover:bg-white' }}">
            Tickets
        </a>
        <a href="{{ route('admin.projects.files', $project->id) }}"
           class="px-3 py-2 rounded-md text-sm {{ $section === 'files' ? 'bg-indigo-100 text-indigo-800' : 'text-gray-700 hover:bg-white' }}">
            Files
        </a>
        <a href="{{ route('admin.projects.time', $project->id) }}"
           class="px-3 py-2 rounded-md text-sm {{ $section === 'time' ? 'bg-indigo-100 text-indigo-800' : 'text-gray-700 hover:bg-white' }}">
            Time
        </a>
    </nav>
</div>

<form id="create-repository-form" method="post" action="{{ route('admin.projects.repositories.store', $project->id) }}" class="hidden">
    @csrf
    <input type="hidden" name="name" id="create-repository-name">
    <input type="hidden" name="vcs_type" id="create-repository-vcs">
</form>

@push('scripts')
    <script>
        (function () {
            const tab = document.getElementById('repo-tab');
            const menu = document.getElementById('repo-menu');
            const createRepositoryButton = document.getElementById('create-repository-btn');
            const createRepositoryForm = document.getElementById('create-repository-form');
            const createRepositoryInput = document.getElementById('create-repository-name');
            const createRepositoryVcsInput = document.getElementById('create-repository-vcs');
            if (!tab || !menu) return;

            let overTab = false;
            let overMenu = false;

            const show = () => { menu.classList.remove('hidden'); };
            const hide = () => { menu.classList.add('hidden'); };
            const sync = () => { (overTab || overMenu) ? show() : hide(); };

            tab.addEventListener('mouseenter', () => { overTab = true; sync(); });
            tab.addEventListener('mouseleave', () => { overTab = false; sync(); });
            menu.addEventListener('mouseenter', () => { overMenu = true; sync(); });
            menu.addEventListener('mouseleave', () => { overMenu = false; sync(); });

            if (createRepositoryButton && createRepositoryForm && createRepositoryInput && createRepositoryVcsInput) {
                createRepositoryButton.addEventListener('click', () => {
                    const name = window.prompt('Repository name');
                    if (!name) return;
                    const trimmed = name.trim();
                    if (!trimmed) return;
                    const typeRaw = window.prompt('VCS type: git, hg, svn', 'git');
                    if (!typeRaw) return;
                    const type = typeRaw.trim().toLowerCase();
                    if (!['git', 'hg', 'svn'].includes(type)) {
                        window.alert('Unsupported VCS type. Use: git, hg, svn.');
                        return;
                    }
                    createRepositoryInput.value = trimmed;
                    createRepositoryVcsInput.value = type;
                    createRepositoryForm.submit();
                });
            }
        })();
    </script>
@endpush
