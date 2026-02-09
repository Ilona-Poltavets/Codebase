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
                    <div class="flex items-start justify-between gap-6">
                        <div class="min-w-0">
                            <h3 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Static Files</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-300 text-sm">Project-level files available for this workspace.</p>
                            <p class="mt-1 text-gray-500 dark:text-gray-400 text-xs break-all">
                                {{ $currentFolder ? $currentFolder->path : 'root' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    class="h-10 px-4 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold leading-none">
                                Download All
                            </button>
                            <button id="add-file-btn" type="button"
                                    class="h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold leading-none">
                                Upload
                            </button>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <h3 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Reallife File Manager</h3>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($currentFolder)
                            <a href="{{ route('admin.projects.files', $project->id) }}"
                               class="h-10 px-4 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold inline-flex items-center">
                                Back
                            </a>
                        @endif
                        <button id="add-folder-btn" type="button"
                                class="h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">
                            + Folder
                        </button>
                        <button id="add-file-btn-secondary" type="button"
                                class="h-10 px-4 rounded-md bg-gray-600 hover:bg-gray-500 text-white text-sm font-semibold">
                            Add file
                        </button>
                    </div>

                    <div id="dropzone"
                         class="mt-5 rounded-xl border border-dashed border-slate-500 bg-slate-950/40 min-h-[220px] p-6 flex flex-col items-center justify-center text-center transition">
                        <div class="text-lg font-semibold text-slate-100">Upload files or drag and drop</div>
                        <div class="mt-1 text-sm text-slate-400">Any file type. Max size 50 MB.</div>
                        <div class="mt-4 text-xs text-slate-500">
                            {{ $currentFolder ? 'Target: ' . $currentFolder->path : 'Target: root' }}
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr class="text-left text-sm">
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Size</th>
                                <th class="px-4 py-3">Modified</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                            @foreach($folders as $folder)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.projects.files', ['project' => $project->id, 'folder_id' => $folder->id]) }}"
                                           class="text-emerald-600 dark:text-emerald-300 hover:underline">
                                            {{ $folder->name }}/
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs">Folder</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">Folder</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">-</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $folder->updated_at?->format('d.m.Y H:i:s') }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">Open</td>
                                </tr>
                            @endforeach

                            @forelse($files as $file)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100">
                                    <td class="px-4 py-3 truncate max-w-[360px]">{{ $file->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs">New</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">File</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ number_format($file->size / 1024 / 1024, 2) }} MB</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $file->updated_at?->format('d.m.Y H:i:s') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.projects.files.download', ['project' => $project->id, 'file' => $file->id]) }}"
                                               class="h-9 px-3 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold inline-flex items-center">
                                                Download
                                            </a>
                                            <form method="post" action="{{ route('admin.projects.files.destroy', ['project' => $project->id, 'file' => $file->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="h-9 px-3 rounded-md bg-red-600 hover:bg-red-500 text-white text-xs font-semibold">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @if($folders->isEmpty())
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td colspan="6" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No files or folders in this location.</td>
                                    </tr>
                                @endif
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div id="folder-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
        <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 shadow-xl">
            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Create folder</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter folder name.</p>
            <input id="folder-name-input" type="text"
                   class="mt-4 w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Folder name">
            <div class="mt-4 flex justify-end gap-2">
                <button id="folder-cancel-btn" type="button"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button id="folder-save-btn" type="button"
                        class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">
                    Create
                </button>
            </div>
        </div>
    </div>

    <form id="create-folder-form" method="post" action="{{ route('admin.projects.files.folders.store', $project->id) }}" class="hidden">
        @csrf
        <input type="hidden" name="name" id="folder-name-hidden">
        <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
    </form>

    <form id="upload-file-form" method="post" enctype="multipart/form-data" action="{{ route('admin.projects.files.upload', $project->id) }}" class="hidden">
        @csrf
        <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
        <input id="upload-file-input" type="file" name="file">
    </form>

    <script>
        (function () {
            const addFolderBtn = document.getElementById('add-folder-btn');
            const folderModal = document.getElementById('folder-modal');
            const folderCancelBtn = document.getElementById('folder-cancel-btn');
            const folderSaveBtn = document.getElementById('folder-save-btn');
            const folderNameInput = document.getElementById('folder-name-input');
            const folderNameHidden = document.getElementById('folder-name-hidden');
            const createFolderForm = document.getElementById('create-folder-form');
            const addFileBtn = document.getElementById('add-file-btn');
            const addFileBtnSecondary = document.getElementById('add-file-btn-secondary');
            const uploadInput = document.getElementById('upload-file-input');
            const uploadForm = document.getElementById('upload-file-form');
            const dropzone = document.getElementById('dropzone');

            const openFolderModal = () => {
                folderModal.classList.remove('hidden');
                folderModal.classList.add('flex');
                folderNameInput.value = '';
                folderNameInput.focus();
            };

            const closeFolderModal = () => {
                folderModal.classList.add('hidden');
                folderModal.classList.remove('flex');
            };

            addFolderBtn.addEventListener('click', openFolderModal);
            folderCancelBtn.addEventListener('click', closeFolderModal);
            folderModal.addEventListener('click', (e) => {
                if (e.target === folderModal) closeFolderModal();
            });

            folderSaveBtn.addEventListener('click', () => {
                const name = (folderNameInput.value || '').trim();
                if (!name) {
                    folderNameInput.focus();
                    return;
                }
                folderNameHidden.value = name;
                createFolderForm.submit();
            });

            const triggerUpload = () => uploadInput.click();
            addFileBtn.addEventListener('click', triggerUpload);
            addFileBtnSecondary.addEventListener('click', triggerUpload);
            uploadInput.addEventListener('change', () => {
                if (uploadInput.files && uploadInput.files.length > 0) {
                    uploadForm.submit();
                }
            });

            const setDropzoneState = (active) => {
                if (active) {
                    dropzone.classList.add('border-indigo-400', 'bg-indigo-500/10');
                } else {
                    dropzone.classList.remove('border-indigo-400', 'bg-indigo-500/10');
                }
            };

            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                setDropzoneState(true);
            });
            dropzone.addEventListener('dragleave', () => setDropzoneState(false));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                setDropzoneState(false);
                if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length === 0) return;
                uploadInput.files = e.dataTransfer.files;
                uploadForm.submit();
            });
        })();
    </script>
</x-app-layout>
