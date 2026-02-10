<?php

namespace App\Http\Controllers;

use App\Models\ProjectRepository;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProjectRepositoryController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function repositoriesForProject(Projects $project)
    {
        if (! Schema::hasTable('project_repositories')) {
            return collect();
        }

        return $project->repositories()->orderBy('name')->get();
    }

    private function ensureRepositoriesTableExists()
    {
        if (! Schema::hasTable('project_repositories')) {
            return redirect()
                ->back()
                ->with('error', 'Repository table is missing. Run migrations first.');
        }

        return null;
    }

    private function basePath(Projects $project): string
    {
        return storage_path('app/project-repositories/' . $project->id);
    }

    private function runGit(string $path, string $command): void
    {
        $git = $this->resolveGitBinary();
        $result = Process::path($path)->run('"' . $git . '" ' . $command);
        if (! $result->successful()) {
            $error = trim($result->errorOutput()) ?: trim($result->output());
            if ($error === '') {
                $error = 'Unknown git error.';
            }
            throw new RuntimeException($error);
        }
    }

    private function resolveGitBinary(): string
    {
        $configured = (string) env('GIT_BINARY', '');
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = [
            'C:\\Program Files\\Git\\cmd\\git.exe',
            'C:\\Program Files\\Git\\bin\\git.exe',
            'C:\\Program Files (x86)\\Git\\cmd\\git.exe',
            'C:\\Program Files (x86)\\Git\\bin\\git.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'git';
    }

    public function store(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        if ($response = $this->ensureRepositoriesTableExists()) {
            return $response;
        }
        try {
            $data = $request->validate([
                'name' => 'required|string|max:120',
            ]);

            $slug = Str::slug($data['name']);
            if ($slug === '') {
                $slug = 'repository';
            }

            $baseSlug = $slug;
            $counter = 1;
            while ($project->repositories()->where('slug', $slug)->exists()) {
                $counter++;
                $slug = $baseSlug . '-' . $counter;
            }

            $basePath = $this->basePath($project);
            if (! is_dir($basePath) && ! mkdir($basePath, 0755, true) && ! is_dir($basePath)) {
                throw new \RuntimeException('Unable to create repositories directory.');
            }

            $repoPath = $basePath . DIRECTORY_SEPARATOR . $slug;
            if (! is_dir($repoPath) && ! mkdir($repoPath, 0755, true) && ! is_dir($repoPath)) {
                throw new \RuntimeException('Unable to create repository directory.');
            }

            $this->runGit($repoPath, 'git init -b main');
            file_put_contents($repoPath . DIRECTORY_SEPARATOR . 'README.md', '# ' . $data['name'] . PHP_EOL);
            $this->runGit($repoPath, 'git add README.md');
            $this->runGit($repoPath, 'git -c user.name="Codebase Bot" -c user.email="repo@local" commit -m "Initial commit"');

            $repository = ProjectRepository::create([
                'project_id' => $project->id,
                'created_by' => $request->user()->id,
                'name' => $data['name'],
                'slug' => $slug,
                'path' => $repoPath,
                'default_branch' => 'main',
            ]);

            return redirect()
                ->route('admin.projects.repositories.show', [$project->id, $repository->id])
                ->with('success', 'Repository created.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Failed to create repository: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Projects $project, ProjectRepository $repository)
    {
        $this->authorizeProjectAccess($project);
        if (! Schema::hasTable('project_repositories')) {
            abort(404);
        }
        if ($repository->project_id !== $project->id) {
            abort(404);
        }

        $relativePath = trim((string) $request->query('path', ''), '/');
        $repoPath = rtrim($repository->path, '\\/');
        $targetPath = $repoPath . ($relativePath !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath) : '');
        $resolvedPath = realpath($targetPath);
        $resolvedRepoPath = realpath($repoPath);

        if (! $resolvedPath || ! $resolvedRepoPath || ! str_starts_with($resolvedPath, $resolvedRepoPath)) {
            abort(404);
        }

        if (is_file($resolvedPath)) {
            $content = @file_get_contents($resolvedPath);
            if ($content === false) {
                $content = 'Unable to read file.';
            }

            return view('projects.repository', [
                'project' => $project->load('company'),
                'repository' => $repository,
                'repositories' => $this->repositoriesForProject($project),
                'section' => 'repositories',
                'repoFiles' => collect(),
                'relativePath' => $relativePath,
                'isFileView' => true,
                'fileContent' => mb_substr($content, 0, 200000),
                'cloneUrl' => $this->cloneUrl($repository),
            ]);
        }

        $items = collect(scandir($resolvedPath) ?: [])
            ->reject(fn ($name) => in_array($name, ['.', '..', '.git'], true))
            ->map(function ($name) use ($resolvedPath, $relativePath) {
                $fullPath = $resolvedPath . DIRECTORY_SEPARATOR . $name;
                $itemRelativePath = trim(($relativePath !== '' ? $relativePath . '/' : '') . $name, '/');

                return [
                    'name' => $name,
                    'path' => $itemRelativePath,
                    'is_dir' => is_dir($fullPath),
                    'size' => is_file($fullPath) ? filesize($fullPath) : null,
                    'modified_at' => filemtime($fullPath) ?: null,
                ];
            })
            ->sortBy([
                fn ($item) => $item['is_dir'] ? 0 : 1,
                fn ($item) => Str::lower($item['name']),
            ])
            ->values();

        return view('projects.repository', [
            'project' => $project->load('company'),
            'repository' => $repository,
            'repositories' => $this->repositoriesForProject($project),
            'section' => 'repositories',
            'repoFiles' => $items,
            'relativePath' => $relativePath,
            'isFileView' => false,
            'fileContent' => null,
            'cloneUrl' => $this->cloneUrl($repository),
        ]);
    }

    public function destroy(Projects $project, ProjectRepository $repository)
    {
        $this->authorizeProjectAccess($project);
        if (! Schema::hasTable('project_repositories')) {
            return redirect()
                ->route('admin.projects.overview', $project->id)
                ->with('error', 'Repository table is missing.');
        }
        if ($repository->project_id !== $project->id) {
            abort(404);
        }

        $repoPath = realpath($repository->path);
        $basePath = realpath($this->basePath($project));

        if ($repoPath && $basePath && str_starts_with($repoPath, $basePath)) {
            $this->deleteDirectory($repoPath);
        }

        $repository->delete();

        return redirect()
            ->route('admin.projects.overview', $project->id)
            ->with('success', 'Repository deleted.');
    }

    private function cloneUrl(ProjectRepository $repository): string
    {
        return $repository->path;
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                $this->deleteDirectory($itemPath);
            } else {
                @unlink($itemPath);
            }
        }

        @rmdir($path);
    }
}
