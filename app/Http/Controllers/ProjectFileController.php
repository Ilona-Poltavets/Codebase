<?php

namespace App\Http\Controllers;

use App\Models\ProjectFile;
use App\Models\ProjectFolder;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function repositories(): array
    {
        return [
            'api-service',
            'frontend-app',
            'infra-scripts',
        ];
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
            ->where('folder_id', $currentFolder?->id)
            ->orderByDesc('id')
            ->get();

        return view('projects.files', [
            'project' => $project->load('company'),
            'section' => 'files',
            'repositories' => $this->repositories(),
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

        ProjectFile::create([
            'project_id' => $project->id,
            'folder_id' => $folder?->id,
            'uploaded_by' => $request->user()->id,
            'name' => $originalName,
            'disk' => 'local',
            'path' => $storedPath,
            'size' => $file->getSize() ?: 0,
            'mime_type' => $file->getClientMimeType(),
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

        Storage::disk($file->disk)->delete($file->path);
        $folderId = $file->folder_id;
        $file->delete();

        return redirect()->route('admin.projects.files', ['project' => $project->id, 'folder_id' => $folderId])
            ->with('success', 'File deleted.');
    }
}
