<?php

namespace App\Http\Middleware;

use App\Models\ProjectFile;
use App\Models\ProjectRepository;
use App\Models\Projects;
use App\Models\Ticket;
use App\Models\WikiPage;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $project = $this->resolveModel($request->route('project'), Projects::class);

        if ($project) {
            if (! $user->hasRole('admin') && (int) $project->company_id !== (int) $user->company_id) {
                abort(403);
            }

            $this->ensureBelongsToProject($request, 'ticket', Ticket::class, (int) $project->id);
            $this->ensureBelongsToProject($request, 'file', ProjectFile::class, (int) $project->id);
            $this->ensureBelongsToProject($request, 'repository', ProjectRepository::class, (int) $project->id);
            $this->ensureBelongsToProject($request, 'wikiPage', WikiPage::class, (int) $project->id);
        }

        return $next($request);
    }

    private function ensureBelongsToProject(Request $request, string $param, string $modelClass, int $projectId): void
    {
        $resource = $this->resolveModel($request->route($param), $modelClass);

        if ($resource && (int) $resource->project_id !== $projectId) {
            abort(404);
        }
    }

    /**
     * @template T of Model
     *
     * @param  mixed  $value
     * @param  class-string<T>  $modelClass
     * @return T|null
     */
    private function resolveModel(mixed $value, string $modelClass): ?Model
    {
        if ($value instanceof $modelClass) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_scalar($value) && ctype_digit((string) $value)) {
            return $modelClass::query()->find((int) $value);
        }

        return null;
    }
}
