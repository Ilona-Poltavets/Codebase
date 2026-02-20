<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $project->name }} - Board Workspace</title>
    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .boards-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(56, 189, 248, 0.55) rgba(15, 23, 42, 0.45);
        }

        .boards-scroll::-webkit-scrollbar {
            width: 10px;
        }

        .boards-scroll::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.45);
            border-radius: 9999px;
        }

        .boards-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.75), rgba(16, 185, 129, 0.65));
            border-radius: 9999px;
            border: 2px solid rgba(15, 23, 42, 0.7);
        }

        .boards-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.95), rgba(16, 185, 129, 0.85));
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.24),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.18),transparent_40%)]"></div>

        <div class="relative z-10 flex min-h-screen">
            <aside id="boards-sidebar" class="fixed inset-y-0 left-0 z-30 w-80 -translate-x-full border-r border-slate-800 bg-slate-900/95 p-4 backdrop-blur transition-transform duration-300">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Boards</h2>
                    <button id="boards-sidebar-close" type="button" class="rounded-md border border-slate-700 px-2 py-1 text-xs text-slate-300 hover:bg-slate-800">
                        Close
                    </button>
                </div>

                <div class="space-y-2">
                    <p class="px-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Pinned</p>
                    <a href="{{ route('admin.projects.board', $project->id) }}?view=my"
                       class="block rounded-lg border px-3 py-2 text-sm {{ request('view') === 'my' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 text-slate-200 hover:bg-slate-800' }}">
                        My Developer Board
                    </a>
                </div>

                <div class="mt-5 space-y-2">
                    <p class="px-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Project Boards</p>
                    <div class="boards-scroll max-h-[70vh] overflow-y-auto pr-1">
                        @foreach($availableProjects as $availableProject)
                            <a href="{{ route('admin.projects.board', $availableProject->id) }}"
                               class="mb-2 block rounded-lg border px-3 py-2 text-sm {{ $availableProject->id === $project->id && request('view') !== 'my' ? 'border-sky-400 bg-sky-500/10 text-sky-200' : 'border-slate-700 text-slate-200 hover:bg-slate-800' }}">
                                {{ $availableProject->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div id="boards-sidebar-overlay" class="fixed inset-0 z-20 hidden bg-black/40"></div>

            <div class="mx-auto flex min-h-screen w-full max-w-[1800px] flex-1 flex-col px-4 py-4 sm:px-6 lg:px-8">
                <header class="mb-4 rounded-2xl border border-slate-800 bg-slate-900/90 px-4 py-3 backdrop-blur">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <button id="boards-sidebar-open" type="button" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800">
                                Boards
                            </button>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Board Workspace</p>
                                <h1 class="text-xl font-semibold text-white">{{ $project->name }}</h1>
                                <p class="text-xs text-slate-400">{{ $project->company?->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.projects.overview', $project->id) }}"
                               class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800">
                                Open Project Site
                            </a>
                            <a href="{{ route('admin.projects.tickets', $project->id) }}"
                               class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800">
                                Ticket List
                            </a>
                            <a href="{{ route('admin.projects.tickets.create', $project->id) }}"
                               class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950 hover:bg-emerald-400">
                                New Ticket
                            </a>
                        </div>
                    </div>
                </header>

                <main class="flex-1 min-h-0">
                    <div
                        id="ticket-board-app"
                        class="h-full min-h-full"
                        data-fetch-url="{{ route('admin.projects.board.data', $project->id) }}"
                        data-move-url-template="{{ route('admin.projects.board.tickets.move', [$project->id, '__TICKET__']) }}"
                        data-ticket-url-template="{{ route('admin.projects.tickets.show', ['__PROJECT__', '__TICKET__']) }}"
                        data-create-ticket-url="{{ route('admin.projects.tickets.create', $project->id) }}"
                        data-initial-view="{{ request('view') === 'developer' ? 'developer' : (request('view') === 'my' ? 'my' : 'project') }}"
                    ></div>
                </main>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('boards-sidebar');
            const openBtn = document.getElementById('boards-sidebar-open');
            const closeBtn = document.getElementById('boards-sidebar-close');
            const overlay = document.getElementById('boards-sidebar-overlay');
            if (!sidebar || !openBtn || !closeBtn || !overlay) return;

            const open = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            };

            const close = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            };

            openBtn.addEventListener('click', open);
            closeBtn.addEventListener('click', close);
            overlay.addEventListener('click', close);
        })();
    </script>
</body>
</html>

