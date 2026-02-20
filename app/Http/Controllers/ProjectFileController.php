<?php

namespace App\Http\Controllers;

use App\Models\ProjectFile;
use App\Models\ProjectFileComment;
use App\Models\ProjectFolder;
use App\Models\ProjectRepository;
use App\Models\Projects;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function repositories(Projects $project)
    {
        if (! Schema::hasTable('project_repositories')) {
            return collect();
        }

        return ProjectRepository::where('project_id', $project->id)
            ->orderBy('name')
            ->get();
    }

    private function applyFolderScope($query, ?int $folderId)
    {
        if ($folderId === null) {
            return $query->whereNull('folder_id');
        }

        return $query->where('folder_id', $folderId);
    }

    public function index(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $folderId = $request->query('folder_id');
        $currentFolder = null;
        if ($folderId) {
            $currentFolder = ProjectFolder::where('project_id', $project->id)->findOrFail($folderId);
        }

        $folders = ProjectFolder::where('project_id', $project->id)
            ->where('parent_id', $currentFolder?->id)
            ->orderBy('name')
            ->get();

        $files = ProjectFile::where('project_id', $project->id)
            ->where('is_current', true)
            ->when($currentFolder?->id, fn ($query, $folderId) => $query->where('folder_id', $folderId), fn ($query) => $query->whereNull('folder_id'))
            ->orderByDesc('id')
            ->get();

        return view('projects.files', [
            'project' => $project->load('company'),
            'section' => 'files',
            'repositories' => $this->repositories($project),
            'currentFolder' => $currentFolder,
            'folders' => $folders,
            'files' => $files,
            'allFolders' => ProjectFolder::where('project_id', $project->id)->orderBy('path')->get(),
        ]);
    }

    public function storeFolder(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:project_folders,id',
        ]);

        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = ProjectFolder::where('project_id', $project->id)->findOrFail($data['parent_id']);
        }

        $slug = Str::slug($data['name']);
        if ($slug === '') {
            $slug = 'folder';
        }
        $basePath = $parent ? ($parent->path . '/' . $slug) : $slug;
        $path = $basePath;
        $counter = 1;
        while (ProjectFolder::where('project_id', $project->id)->where('path', $path)->exists()) {
            $counter++;
            $path = $basePath . '-' . $counter;
        }

        ProjectFolder::create([
            'project_id' => $project->id,
            'parent_id' => $parent?->id,
            'name' => $data['name'],
            'path' => $path,
        ]);

        ActivityLogger::log($project->id, $request->user()->id, 'file.folder_created', [
            'name' => $data['name'],
            'path' => $path,
            'parent_id' => $parent?->id,
        ]);

        return redirect()->route('admin.projects.files', ['project' => $project->id, 'folder_id' => $parent?->id])
            ->with('success', 'Folder created.');
    }

    public function storeFile(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $data = $request->validate([
            'file' => 'required|file|max:51200',
            'folder_id' => 'nullable|exists:project_folders,id',
        ]);

        $folder = null;
        if (! empty($data['folder_id'])) {
            $folder = ProjectFolder::where('project_id', $project->id)->findOrFail($data['folder_id']);
        }

        $file = $data['file'];
        $originalName = $file->getClientOriginalName();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();
        $safeName = Str::slug($nameWithoutExt);
        if ($safeName === '') {
            $safeName = 'file';
        }
        $finalName = $safeName . '-' . Str::lower(Str::random(8)) . ($ext ? ('.' . $ext) : '');

        $baseDir = 'project-files/' . $project->id;
        if ($folder) {
            $baseDir .= '/' . $folder->path;
        }

        $storedPath = $file->storeAs($baseDir, $finalName, 'local');

        $latestCurrent = $this->applyFolderScope(
            ProjectFile::where('project_id', $project->id)
                ->where('name', $originalName)
                ->where('is_current', true),
            $folder?->id
        )->first();

        $version = ($latestCurrent?->version ?? 0) + 1;
        if ($latestCurrent) {
            $latestCurrent->update(['is_current' => false]);
        }

        $savedFile = ProjectFile::create([
            'project_id' => $project->id,
            'folder_id' => $folder?->id,
            'uploaded_by' => $request->user()->id,
            'name' => $originalName,
            'version' => $version,
            'is_current' => true,
            'disk' => 'local',
            'path' => $storedPath,
            'size' => $file->getSize() ?: 0,
            'mime_type' => $file->getClientMimeType(),
        ]);

        ActivityLogger::log($project->id, $request->user()->id, 'file.uploaded', [
            'file_id' => $savedFile->id,
            'name' => $originalName,
            'version' => $version,
            'folder_id' => $folder?->id,
            'size' => $savedFile->size,
        ]);

        return redirect()->route('admin.projects.files', ['project' => $project->id, 'folder_id' => $folder?->id])
            ->with('success', 'File uploaded.');
    }

    public function download(Projects $project, ProjectFile $file)
    {
        $this->authorizeProjectAccess($project);
        if ($file->project_id !== $project->id) {
            abort(404);
        }

        if (! Storage::disk($file->disk)->exists($file->path)) {
            abort(404);
        }

        return Storage::disk($file->disk)->download($file->path, $file->name);
    }

    public function destroy(Projects $project, ProjectFile $file)
    {
        $this->authorizeProjectAccess($project);
        if ($file->project_id !== $project->id) {
            abort(404);
        }

        $wasCurrent = (bool) $file->is_current;
        $folderId = $file->folder_id;

        Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        if ($wasCurrent) {
            $fallback = $this->applyFolderScope(
                ProjectFile::where('project_id', $project->id)
                    ->where('name', $file->name)
                    ->orderByDesc('version'),
                $folderId
            )->first();

            if ($fallback) {
                $fallback->update(['is_current' => true]);
            }
        }

        ActivityLogger::log($project->id, request()->user()->id, 'file.deleted', [
            'file_id' => $file->id,
            'name' => $file->name,
            'version' => $file->version,
            'folder_id' => $folderId,
        ]);

        return redirect()->route('admin.projects.files', ['project' => $project->id, 'folder_id' => $folderId])
            ->with('success', 'File deleted.');
    }

    public function show(Projects $project, ProjectFile $file)
    {
        $this->authorizeProjectAccess($project);
        if ($file->project_id !== $project->id) {
            abort(404);
        }

        $versions = $this->applyFolderScope(
            ProjectFile::where('project_id', $project->id)
                ->where('name', $file->name)
                ->with('uploader')
                ->orderByDesc('version'),
            $file->folder_id
        )->get();

        $file->load(['uploader', 'comments.user']);

        return view('projects.file-show', [
            'project' => $project->load('company'),
            'section' => 'files',
            'repositories' => $this->repositories($project),
            'file' => $file,
            'versions' => $versions,
        ]);
    }

    public function storeComment(Request $request, Projects $project, ProjectFile $file)
    {
        $this->authorizeProjectAccess($project);
        if ($file->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        ProjectFileComment::create([
            'project_file_id' => $file->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        ActivityLogger::log($project->id, $request->user()->id, 'file.comment_added', [
            'file_id' => $file->id,
            'name' => $file->name,
            'comment_length' => mb_strlen($data['body']),
        ]);

        return redirect()
            ->route('admin.projects.files.show', ['project' => $project->id, 'file' => $file->id])
            ->with('success', 'Comment added.');
    }
}
