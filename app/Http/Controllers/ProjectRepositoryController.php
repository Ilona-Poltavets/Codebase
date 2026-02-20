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
    private const SUPPORTED_TYPES = ['git', 'hg', 'svn'];

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

    private function repositoryType(ProjectRepository $repository): string
    {
        $type = Str::lower((string) ($repository->vcs_type ?: 'git'));

        return in_array($type, self::SUPPORTED_TYPES, true) ? $type : 'git';
    }

    private function repositoriesHasVcsColumn(): bool
    {
        return Schema::hasColumn('project_repositories', 'vcs_type');
    }

    private function runBinary(string $path, string $binary, string $command): string
    {
        $result = Process::path($path)->run('"' . $binary . '" ' . $command);
        if (! $result->successful()) {
            $error = trim($result->errorOutput()) ?: trim($result->output());
            if ($error === '') {
                $error = 'Unknown command error.';
            }
            throw new RuntimeException($error);
        }

        return trim($result->output());
    }

    private function runGit(string $path, string $command): string
    {
        $command = preg_replace('/^\s*git\s+/i', '', $command) ?? $command;

        return $this->runBinary($path, $this->resolveGitBinary(), $command);
    }

    private function runHg(string $path, string $command): string
    {
        $command = preg_replace('/^\s*hg\s+/i', '', $command) ?? $command;

        return $this->runBinary($path, $this->resolveHgBinary(), $command);
    }

    private function runSvn(string $path, string $command): string
    {
        $command = preg_replace('/^\s*svn\s+/i', '', $command) ?? $command;

        return $this->runBinary($path, $this->resolveSvnBinary(), $command);
    }

    private function resolveBinary(string $envKey, array $candidates, string $fallback): string
    {
        $configured = (string) env($envKey, '');
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $fallback;
    }

    private function resolveGitBinary(): string
    {
        return $this->resolveBinary('GIT_BINARY', [
            'C:\\Program Files\\Git\\cmd\\git.exe',
            'C:\\Program Files\\Git\\bin\\git.exe',
            'C:\\Program Files (x86)\\Git\\cmd\\git.exe',
            'C:\\Program Files (x86)\\Git\\bin\\git.exe',
        ], 'git');
    }

    private function resolveHgBinary(): string
    {
        return $this->resolveBinary('HG_BINARY', [
            'C:\\Program Files\\Mercurial\\hg.exe',
            'C:\\Program Files (x86)\\Mercurial\\hg.exe',
        ], 'hg');
    }

    private function resolveSvnBinary(): string
    {
        return $this->resolveBinary('SVN_BINARY', [
            'C:\\Program Files\\TortoiseSVN\\bin\\svn.exe',
            'C:\\Program Files (x86)\\TortoiseSVN\\bin\\svn.exe',
            'C:\\Program Files\\Subversion\\bin\\svn.exe',
        ], 'svn');
    }

    private function resolveSvnAdminBinary(): string
    {
        $configured = (string) env('SVNADMIN_BINARY', '');
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $svnBinary = $this->resolveSvnBinary();
        if (Str::endsWith(Str::lower($svnBinary), 'svn.exe')) {
            $candidate = substr($svnBinary, 0, -7) . 'svnadmin.exe';
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'svnadmin';
    }

    private function escapeArg(string $value): string
    {
        return escapeshellarg($value);
    }

    private function toFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $normalized = ltrim($normalized, '/');
        $normalized = str_replace(' ', '%20', $normalized);

        return 'file:///' . $normalized;
    }

    private function metadataDirectory(string $type): string
    {
        if ($type === 'git') {
            return '.git';
        }
        if ($type === 'hg') {
            return '.hg';
        }

        return '.svn';
    }

    private function initializeRepository(string $type, string $repoPath, string $repoName): string
    {
        if ($type === 'git') {
            $this->runGit($repoPath, 'init -b main');
            file_put_contents($repoPath . DIRECTORY_SEPARATOR . 'README.md', '# ' . $repoName . PHP_EOL);
            $this->runGit($repoPath, 'add README.md');
            $this->runGit($repoPath, '-c user.name="Codebase Bot" -c user.email="repo@local" commit -m "Initial commit"');

            return 'main';
        }

        if ($type === 'hg') {
            $this->runHg($repoPath, 'init');
            file_put_contents($repoPath . DIRECTORY_SEPARATOR . 'README.md', '# ' . $repoName . PHP_EOL);
            $this->runHg($repoPath, 'add README.md');
            $this->runHg($repoPath, 'commit -u "Codebase Bot <repo@local>" -m "Initial commit"');

            return 'default';
        }

        $repoStoragePath = $repoPath . '.svnrepo';
        if (! is_dir($repoStoragePath) && ! mkdir($repoStoragePath, 0755, true) && ! is_dir($repoStoragePath)) {
            throw new RuntimeException('Unable to create svn repository storage directory.');
        }

        $this->runBinary($repoPath, $this->resolveSvnAdminBinary(), 'create ' . $this->escapeArg($repoStoragePath));
        $this->runSvn($repoPath, 'checkout ' . $this->escapeArg($this->toFileUrl($repoStoragePath)) . ' .');
        file_put_contents($repoPath . DIRECTORY_SEPARATOR . 'README.md', '# ' . $repoName . PHP_EOL);
        $this->runSvn($repoPath, 'add README.md');
        $this->runSvn($repoPath, 'commit -m "Initial commit"');

        return 'trunk';
    }

    private function listRepositoryItems(string $resolvedPath, string $relativePath, string $type)
    {
        $metaDir = $this->metadataDirectory($type);

        return collect(scandir($resolvedPath) ?: [])
            ->reject(fn ($name) => in_array($name, ['.', '..', $metaDir], true))
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
    }

    private function readHistory(ProjectRepository $repository): array
    {
        $type = $this->repositoryType($repository);
        $path = $repository->path;

        if ($type === 'git') {
            $output = $this->runGit(
                $path,
                'log --date=iso --pretty=format:%H%x09%h%x09%an%x09%ad%x09%s -n 100'
            );

            return collect(preg_split('/\r?\n/', trim($output)) ?: [])
                ->filter()
                ->map(function ($line) {
                    $parts = explode("\t", $line, 5);

                    return [
                        'id' => $parts[0] ?? '',
                        'short' => $parts[1] ?? '',
                        'author' => $parts[2] ?? '',
                        'date' => $parts[3] ?? '',
                        'message' => $parts[4] ?? '',
                    ];
                })
                ->values()
                ->all();
        }

        if ($type === 'hg') {
            $output = $this->runHg(
                $path,
                'log --template "{node}\t{short(node)}\t{author|person}\t{date|isodatesec}\t{desc|firstline}\n" -l 100'
            );

            return collect(preg_split('/\r?\n/', trim($output)) ?: [])
                ->filter()
                ->map(function ($line) {
                    $parts = explode("\t", $line, 5);

                    return [
                        'id' => $parts[0] ?? '',
                        'short' => $parts[1] ?? '',
                        'author' => $parts[2] ?? '',
                        'date' => $parts[3] ?? '',
                        'message' => $parts[4] ?? '',
                    ];
                })
                ->values()
                ->all();
        }

        $xml = $this->runSvn($path, 'log --xml -l 100');
        $parsed = @simplexml_load_string($xml);
        if (! $parsed) {
            return [];
        }

        $history = [];
        foreach ($parsed->logentry as $entry) {
            $revision = (string) ($entry['revision'] ?? '');
            $history[] = [
                'id' => $revision,
                'short' => 'r' . $revision,
                'author' => (string) ($entry->author ?? ''),
                'date' => (string) ($entry->date ?? ''),
                'message' => trim((string) ($entry->msg ?? '')),
            ];
        }

        return $history;
    }

    private function readDiff(ProjectRepository $repository, ?string $from, ?string $to): array
    {
        $history = $this->readHistory($repository);
        if ($from === null && isset($history[1])) {
            $from = (string) $history[1]['id'];
        }
        if ($to === null && isset($history[0])) {
            $to = (string) $history[0]['id'];
        }

        if (! $from || ! $to) {
            return [
                'from' => $from,
                'to' => $to,
                'content' => '',
                'error' => 'Not enough revisions to build diff.',
            ];
        }

        $type = $this->repositoryType($repository);
        $path = $repository->path;
        if ($type === 'git') {
            $content = $this->runGit($path, 'diff ' . $this->escapeArg($from) . ' ' . $this->escapeArg($to));
        } elseif ($type === 'hg') {
            $content = $this->runHg($path, 'diff -r ' . $this->escapeArg($from) . ' -r ' . $this->escapeArg($to));
        } else {
            $content = $this->runSvn($path, 'diff -r ' . $this->escapeArg($from . ':' . $to));
        }

        return [
            'from' => $from,
            'to' => $to,
            'content' => mb_substr($content, 0, 400000),
            'error' => '',
        ];
    }

    private function readRefs(ProjectRepository $repository): array
    {
        $type = $this->repositoryType($repository);
        $path = $repository->path;

        if ($type === 'git') {
            $branches = $this->runGit($path, 'branch --format=%(refname:short)');
            $tags = $this->runGit($path, 'tag --sort=-creatordate');
            $current = '';
            try {
                $current = $this->runGit($path, 'branch --show-current');
            } catch (Throwable) {
                $current = '';
            }

            return [
                'current' => trim($current),
                'branches' => collect(preg_split('/\r?\n/', trim($branches)) ?: [])->filter()->values()->all(),
                'tags' => collect(preg_split('/\r?\n/', trim($tags)) ?: [])->filter()->values()->all(),
            ];
        }

        if ($type === 'hg') {
            $branches = $this->runHg($path, 'branches');
            $tags = $this->runHg($path, 'tags');
            $current = '';
            try {
                $current = $this->runHg($path, 'branch');
            } catch (Throwable) {
                $current = '';
            }

            return [
                'current' => trim($current),
                'branches' => collect(preg_split('/\r?\n/', trim($branches)) ?: [])
                    ->filter()
                    ->map(fn ($line) => trim((string) preg_replace('/\s+\d+:[a-f0-9]+$/i', '', $line)))
                    ->values()
                    ->all(),
                'tags' => collect(preg_split('/\r?\n/', trim($tags)) ?: [])
                    ->filter()
                    ->map(fn ($line) => trim((string) preg_replace('/\s+\d+:[a-f0-9]+$/i', '', $line)))
                    ->reject(fn ($tag) => in_array(Str::lower($tag), ['tip'], true))
                    ->values()
                    ->all(),
            ];
        }

        $current = '';
        try {
            $current = 'r' . $this->runSvn($path, 'info --show-item revision');
        } catch (Throwable) {
            $current = '';
        }

        $branches = [];
        $tags = [];
        try {
            $branchesOutput = $this->runSvn($path, 'list ^/branches');
            $branches = collect(preg_split('/\r?\n/', trim($branchesOutput)) ?: [])
                ->filter()
                ->map(fn ($line) => trim($line, " /\t\n\r\0\x0B"))
                ->values()
                ->all();
        } catch (Throwable) {
            $branches = [];
        }

        try {
            $tagsOutput = $this->runSvn($path, 'list ^/tags');
            $tags = collect(preg_split('/\r?\n/', trim($tagsOutput)) ?: [])
                ->filter()
                ->map(fn ($line) => trim($line, " /\t\n\r\0\x0B"))
                ->values()
                ->all();
        } catch (Throwable) {
            $tags = [];
        }

        return [
            'current' => $current,
            'branches' => $branches,
            'tags' => $tags,
        ];
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
                'vcs_type' => 'nullable|string|in:git,hg,svn',
            ]);

            $vcsType = Str::lower((string) ($data['vcs_type'] ?? 'git'));
            if (! in_array($vcsType, self::SUPPORTED_TYPES, true)) {
                $vcsType = 'git';
            }

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
                throw new RuntimeException('Unable to create repositories directory.');
            }

            $repoPath = $basePath . DIRECTORY_SEPARATOR . $slug;
            if (! is_dir($repoPath) && ! mkdir($repoPath, 0755, true) && ! is_dir($repoPath)) {
                throw new RuntimeException('Unable to create repository directory.');
            }

            $defaultBranch = $this->initializeRepository($vcsType, $repoPath, $data['name']);

            $payload = [
                'project_id' => $project->id,
                'created_by' => $request->user()->id,
                'name' => $data['name'],
                'slug' => $slug,
                'path' => $repoPath,
                'default_branch' => $defaultBranch,
            ];
            if ($this->repositoriesHasVcsColumn()) {
                $payload['vcs_type'] = $vcsType;
            }

            $repository = ProjectRepository::create($payload);

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

        $tab = (string) $request->query('tab', 'files');
        if (! in_array($tab, ['files', 'history', 'diff', 'refs'], true)) {
            $tab = 'files';
        }

        $relativePath = trim((string) $request->query('path', ''), '/');
        $repoPath = rtrim($repository->path, '\\/');
        $targetPath = $repoPath . ($relativePath !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath) : '');
        $resolvedPath = realpath($targetPath);
        $resolvedRepoPath = realpath($repoPath);
        $repoType = $this->repositoryType($repository);

        if (! $resolvedPath || ! $resolvedRepoPath || ! str_starts_with($resolvedPath, $resolvedRepoPath)) {
            abort(404);
        }

        $repoFiles = collect();
        $isFileView = false;
        $fileContent = null;
        $history = [];
        $refs = ['current' => '', 'branches' => [], 'tags' => []];
        $diff = ['from' => null, 'to' => null, 'content' => '', 'error' => ''];
        $tabError = null;

        try {
            if ($tab === 'files') {
                if (is_file($resolvedPath)) {
                    $content = @file_get_contents($resolvedPath);
                    if ($content === false) {
                        $content = 'Unable to read file.';
                    }
                    $isFileView = true;
                    $fileContent = mb_substr($content, 0, 200000);
                } else {
                    $repoFiles = $this->listRepositoryItems($resolvedPath, $relativePath, $repoType);
                }
            } elseif ($tab === 'history') {
                $history = $this->readHistory($repository);
            } elseif ($tab === 'diff') {
                $from = $request->query('from');
                $to = $request->query('to');
                $from = is_string($from) && trim($from) !== '' ? trim($from) : null;
                $to = is_string($to) && trim($to) !== '' ? trim($to) : null;
                $diff = $this->readDiff($repository, $from, $to);
                $history = $this->readHistory($repository);
            } elseif ($tab === 'refs') {
                $refs = $this->readRefs($repository);
            }
        } catch (Throwable $e) {
            $tabError = $e->getMessage();
        }

        return view('projects.repository', [
            'project' => $project->load('company'),
            'repository' => $repository,
            'repositories' => $this->repositoriesForProject($project),
            'section' => 'repositories',
            'repoFiles' => $repoFiles,
            'relativePath' => $relativePath,
            'isFileView' => $isFileView,
            'fileContent' => $fileContent,
            'cloneUrl' => $this->cloneUrl($repository),
            'tab' => $tab,
            'repoType' => $repoType,
            'history' => $history,
            'diffData' => $diff,
            'refsData' => $refs,
            'tabError' => $tabError,
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
            $svnStoragePath = $repoPath . '.svnrepo';
            if (is_dir($svnStoragePath)) {
                $this->deleteDirectory($svnStoragePath);
            }
        }

        $repository->delete();

        return redirect()
            ->route('admin.projects.overview', $project->id)
            ->with('success', 'Repository deleted.');
    }

    private function cloneUrl(ProjectRepository $repository): string
    {
        if ($this->repositoryType($repository) === 'svn') {
            $repoStoragePath = $repository->path . '.svnrepo';
            if (is_dir($repoStoragePath)) {
                return $this->toFileUrl($repoStoragePath);
            }
        }

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
