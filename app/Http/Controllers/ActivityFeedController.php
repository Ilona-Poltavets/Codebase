<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProjectRepository;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ActivityFeedController extends Controller
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

    private function normalizeTypes(string|array|null $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $items = is_array($raw) ? $raw : explode(',', $raw);

        return collect($items)
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function query(Projects $project, array $types = [])
    {
        return ActivityLog::where('project_id', $project->id)
            ->when(! empty($types), fn ($q) => $q->whereIn('event_type', $types))
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function index(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $types = $this->normalizeTypes($request->query('types'));
        $logs = $this->query($project, $types)->paginate(30)->withQueryString();
        $availableTypes = ActivityLog::where('project_id', $project->id)
            ->select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        return view('projects.activity', [
            'project' => $project->load('company'),
            'section' => 'activity',
            'repositories' => $this->repositories($project),
            'logs' => $logs,
            'selectedTypes' => $types,
            'availableTypes' => $availableTypes,
        ]);
    }

    public function indexApi(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $types = $this->normalizeTypes($request->query('types'));
        $logs = $this->query($project, $types)->paginate(100)->withQueryString();

        return response()->json($logs);
    }

    public function rss(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $types = $this->normalizeTypes($request->query('types'));
        $logs = $this->query($project, $types)->limit(200)->get();
        $projectTitle = htmlspecialchars($project->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $selfUrl = route('admin.projects.activity.rss', ['project' => $project->id, 'types' => implode(',', $types)]);

        $items = $logs->map(function (ActivityLog $log) {
            $title = htmlspecialchars($log->event_type, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $user = $log->user?->name ?: 'System';
            $detailsJson = $log->details ? json_encode($log->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}';
            $description = htmlspecialchars('User: ' . $user . ' | Details: ' . ($detailsJson ?: '{}'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $pubDate = ($log->created_at ?: now())->toRfc2822String();
            $guid = 'activity-log-' . $log->id;

            return "<item><title>{$title}</title><description>{$description}</description><pubDate>{$pubDate}</pubDate><guid>{$guid}</guid></item>";
        })->implode('');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<rss version="2.0"><channel>'
            . '<title>Activity Feed: ' . $projectTitle . '</title>'
            . '<link>' . htmlspecialchars($selfUrl, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</link>'
            . '<description>Project activity stream</description>'
            . '<lastBuildDate>' . now()->toRfc2822String() . '</lastBuildDate>'
            . $items
            . '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
