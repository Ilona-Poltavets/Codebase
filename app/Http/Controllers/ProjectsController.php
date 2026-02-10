<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Http\Requests\StoreProjectsRequest;
use App\Http\Requests\UpdateProjectsRequest;
use App\Models\Company;
use App\Models\ProjectRepository;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketTimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ProjectsController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function sectionResponse(Projects $project, string $section)
    {
        $this->authorizeProjectAccess($project);

        $project->load('company');

        $repositories = collect();
        if (Schema::hasTable('project_repositories')) {
            $repositories = ProjectRepository::where('project_id', $project->id)
                ->orderBy('name')
                ->get();
        }

        $statusCounts = collect();
        $tickets = collect();
        $timeLogs = collect();
        $timeByUser = collect();
        $timeByTicket = collect();
        $projectTimeTotalMinutes = 0;
        $timeFilter = request()->query('period', 'today');
        $rangeFrom = request()->query('from');
        $rangeTo = request()->query('to');

        if ($section === 'overview') {
            $statuses = TicketStatus::where('company_id', $project->company_id)
                ->orderBy('sort')
                ->orderBy('id')
                ->get();

            $ticketCounts = Ticket::where('project_id', $project->id)
                ->selectRaw('status_id, COUNT(*) as aggregate')
                ->groupBy('status_id')
                ->pluck('aggregate', 'status_id');

            $statusCounts = $statuses->map(function (TicketStatus $status) use ($ticketCounts) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'count' => (int) ($ticketCounts[$status->id] ?? 0),
                ];
            });

            $tickets = Ticket::with(['status', 'priority', 'category', 'type', 'assignee'])
                ->where('project_id', $project->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        if ($section === 'time') {
            $timeLogQuery = TicketTimeLog::query()
                ->whereHas('ticket', function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                });

            $now = now();
            if ($timeFilter === 'today') {
                $timeLogQuery->whereDate('logged_at', $now->toDateString());
            } elseif ($timeFilter === 'yesterday') {
                $timeLogQuery->whereDate('logged_at', $now->copy()->subDay()->toDateString());
            } elseif ($timeFilter === 'this_week') {
                $timeLogQuery->whereBetween('logged_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek(),
                ]);
            } elseif ($timeFilter === 'this_month') {
                $timeLogQuery->whereBetween('logged_at', [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
            } elseif ($timeFilter === 'range') {
                if ($rangeFrom) {
                    $timeLogQuery->whereDate('logged_at', '>=', Carbon::parse($rangeFrom)->toDateString());
                }
                if ($rangeTo) {
                    $timeLogQuery->whereDate('logged_at', '<=', Carbon::parse($rangeTo)->toDateString());
                }
            }

            $timeLogs = (clone $timeLogQuery)
                ->with(['ticket', 'user'])
                ->orderByDesc('logged_at')
                ->orderByDesc('id')
                ->get();

            $projectTimeTotalMinutes = (clone $timeLogQuery)->sum('minutes');

            $timeByUser = (clone $timeLogQuery)
                ->selectRaw('user_id, SUM(minutes) as total_minutes')
                ->groupBy('user_id')
                ->orderByDesc('total_minutes')
                ->get()
                ->map(function ($row) {
                    $row->user = $row->user_id ? \App\Models\User::find($row->user_id) : null;
                    return $row;
                });

            $timeByTicket = (clone $timeLogQuery)
                ->selectRaw('ticket_id, SUM(minutes) as total_minutes')
                ->groupBy('ticket_id')
                ->orderByDesc('total_minutes')
                ->get()
                ->map(function ($row) {
                    $row->ticket = $row->ticket_id ? Ticket::find($row->ticket_id) : null;
                    return $row;
                });
        }

        return view('projects.section', [
            'project' => $project,
            'section' => $section,
            'statusCounts' => $statusCounts,
            'tickets' => $tickets,
            'repositories' => $repositories,
            'timeLogs' => $timeLogs,
            'timeByUser' => $timeByUser,
            'timeByTicket' => $timeByTicket,
            'projectTimeTotalMinutes' => $projectTimeTotalMinutes,
            'timeFilter' => $timeFilter,
            'rangeFrom' => $rangeFrom,
            'rangeTo' => $rangeTo,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Projects::with('company')->orderBy('id');
        if (! request()->user()->hasRole('admin')) {
            $query->where('company_id', request()->user()->company_id);
        }
        $projects = $query->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($projects);
        }

        return view('admin.projects', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        if (request()->user()->hasRole('owner') || request()->user()->hasRole('manager')) {
            $companies = $companies->where('id', request()->user()->company_id);
        }
        return view('admin.project-create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectsRequest $request)
    {
        $data = $request->validated();
        if ($request->user()->hasRole('owner') || $request->user()->hasRole('manager')) {
            $data['company_id'] = $request->user()->company_id;
        }
        $project = Projects::create($data);

        if ($request->wantsJson()) {
            return response()->json($project, 201);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $project)
    {
        $this->authorizeProjectAccess($project);
        if (request()->wantsJson()) {
            return response()->json($project->load('company'));
        }

        return redirect()->route('admin.projects.overview', $project);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $companies = Company::orderBy('name')->get();
        if (request()->user()->hasRole('owner') || request()->user()->hasRole('manager')) {
            $companies = $companies->where('id', request()->user()->company_id);
        }
        return view('admin.project-edit', compact('project', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectsRequest $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $data = $request->validated();
        if ($request->user()->hasRole('owner') || $request->user()->hasRole('manager')) {
            $data['company_id'] = $request->user()->company_id;
        }
        $project->update($data);

        if ($request->wantsJson()) {
            return response()->json($project);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $project->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function overview(Projects $project)
    {
        return $this->sectionResponse($project, 'overview');
    }

    public function tickets(Projects $project)
    {
        return $this->sectionResponse($project, 'tickets');
    }

    public function files(Projects $project)
    {
        return $this->sectionResponse($project, 'files');
    }

    public function time(Projects $project)
    {
        return $this->sectionResponse($project, 'time');
    }
}
