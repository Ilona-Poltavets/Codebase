<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\ProjectRepository;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    private function authorizeProject(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function loadMeta(int $companyId): array
    {
        return [
            'statuses' => TicketStatus::where('company_id', $companyId)->orderBy('sort')->get(),
            'priorities' => TicketPriority::where('company_id', $companyId)->orderBy('sort')->get(),
            'categories' => TicketCategory::where('company_id', $companyId)->orderBy('sort')->get(),
            'types' => TicketType::where('company_id', $companyId)->orderBy('sort')->get(),
        ];
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

    public function index(Projects $project)
    {
        $this->authorizeProject($project);

        $tickets = Ticket::with(['status', 'priority', 'assignee'])
            ->where('project_id', $project->id)
            ->orderByDesc('id')
            ->paginate(15);

        $repositories = $this->repositories($project);
        return view('tickets.index', compact('project', 'tickets', 'repositories'));
    }

    public function board(Projects $project)
    {
        $this->authorizeProject($project);

        $repositories = $this->repositories($project);
        $user = request()->user();

        $availableProjects = Projects::query()
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tickets.board', compact('project', 'repositories', 'availableProjects'));
    }

    public function boardData(Request $request, Projects $project)
    {
        $this->authorizeProject($project);

        $view = $request->validate([
            'view' => ['nullable', Rule::in(['project', 'developer', 'my'])],
        ])['view'] ?? 'project';

        if ($view === 'my') {
            return $this->boardDataMy($project);
        }

        if ($view === 'developer') {
            return $this->boardDataByDevelopers($project);
        }

        return $this->boardDataByProject($project);
    }

    private function boardDataByProject(Projects $project)
    {
        $statuses = TicketStatus::query()
            ->where('company_id', $project->company_id)
            ->where('is_active', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'slug']);

        $tickets = Ticket::query()
            ->with(['priority:id,name,slug', 'assignee:id,name,full_name'])
            ->where('project_id', $project->id)
            ->orderBy('status_id')
            ->orderByRaw('COALESCE(board_position, 2147483647)')
            ->orderBy('id')
            ->get([
                'id',
                'project_id',
                'status_id',
                'priority_id',
                'assignee_id',
                'title',
                'board_position',
                'updated_at',
            ]);

        $columns = $statuses->map(function (TicketStatus $status) use ($tickets) {
            $columnTickets = $tickets
                ->where('status_id', $status->id)
                ->values()
                ->map(function (Ticket $ticket) {
                    return [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                        'project_id' => $ticket->project_id,
                        'status_id' => $ticket->status_id,
                        'priority' => $ticket->priority ? [
                            'name' => $ticket->priority->name,
                            'slug' => $ticket->priority->slug,
                        ] : null,
                        'assignee' => $ticket->assignee ? [
                            'id' => $ticket->assignee->id,
                            'name' => $ticket->assignee->full_name ?: $ticket->assignee->name,
                        ] : null,
                        'updated_at' => optional($ticket->updated_at)->toIso8601String(),
                    ];
                });

            return [
                'id' => $status->id,
                'name' => $status->name,
                'slug' => $status->slug,
                'kind' => 'status',
                'tickets' => $columnTickets,
            ];
        });

        return response()->json([
            'view' => 'project',
            'columns' => $columns,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    private function boardDataByDevelopers(Projects $project)
    {
        $projectUsers = $project->users()
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.full_name']);

        $statusesById = TicketStatus::query()
            ->where('company_id', $project->company_id)
            ->pluck('name', 'id');

        $tickets = Ticket::query()
            ->with(['priority:id,name,slug', 'assignee:id,name,full_name'])
            ->where('project_id', $project->id)
            ->orderByRaw('COALESCE(assignee_id, 2147483647)')
            ->orderByRaw('COALESCE(board_position, 2147483647)')
            ->orderBy('id')
            ->get([
                'id',
                'project_id',
                'status_id',
                'priority_id',
                'assignee_id',
                'title',
                'board_position',
                'updated_at',
            ]);

        $mapTicket = function (Ticket $ticket) use ($statusesById) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'project_id' => $ticket->project_id,
                'status_id' => $ticket->status_id,
                'status_name' => $statusesById[$ticket->status_id] ?? null,
                'priority' => $ticket->priority ? [
                    'name' => $ticket->priority->name,
                    'slug' => $ticket->priority->slug,
                ] : null,
                'assignee' => $ticket->assignee ? [
                    'id' => $ticket->assignee->id,
                    'name' => $ticket->assignee->full_name ?: $ticket->assignee->name,
                ] : null,
                'updated_at' => optional($ticket->updated_at)->toIso8601String(),
            ];
        };

        $columns = $projectUsers->map(function (User $user) use ($tickets, $mapTicket) {
            return [
                'id' => 'user_'.$user->id,
                'name' => $user->full_name ?: $user->name,
                'slug' => null,
                'kind' => 'developer',
                'assignee_id' => $user->id,
                'tickets' => $tickets
                    ->where('assignee_id', $user->id)
                    ->values()
                    ->map($mapTicket),
            ];
        })->values();

        $unassigned = [
            'id' => 'unassigned',
            'name' => 'Unassigned',
            'slug' => null,
            'kind' => 'developer',
            'assignee_id' => null,
            'tickets' => $tickets
                ->whereNull('assignee_id')
                ->values()
                ->map($mapTicket),
        ];

        return response()->json([
            'view' => 'developer',
            'columns' => $columns->push($unassigned),
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    private function boardDataMy(Projects $project)
    {
        $user = request()->user();

        $statuses = TicketStatus::query()
            ->where('company_id', $project->company_id)
            ->where('is_active', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'slug']);

        $projectsById = Projects::query()
            ->where('company_id', $project->company_id)
            ->pluck('name', 'id');

        $tickets = Ticket::query()
            ->with(['priority:id,name,slug'])
            ->where('company_id', $project->company_id)
            ->where('assignee_id', $user->id)
            ->orderBy('status_id')
            ->orderByRaw('COALESCE(board_position, 2147483647)')
            ->orderBy('id')
            ->get([
                'id',
                'project_id',
                'status_id',
                'priority_id',
                'title',
                'board_position',
                'updated_at',
            ]);

        $columns = $statuses->map(function (TicketStatus $status) use ($tickets, $projectsById, $user) {
            $columnTickets = $tickets
                ->where('status_id', $status->id)
                ->values()
                ->map(function (Ticket $ticket) use ($projectsById, $user) {
                    return [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                        'status_id' => $ticket->status_id,
                        'project_id' => $ticket->project_id,
                        'project_name' => $projectsById[$ticket->project_id] ?? 'Unknown project',
                        'priority' => $ticket->priority ? [
                            'name' => $ticket->priority->name,
                            'slug' => $ticket->priority->slug,
                        ] : null,
                        'assignee' => [
                            'id' => $user->id,
                            'name' => $user->full_name ?: $user->name,
                        ],
                        'updated_at' => optional($ticket->updated_at)->toIso8601String(),
                    ];
                });

            return [
                'id' => $status->id,
                'name' => $status->name,
                'slug' => $status->slug,
                'kind' => 'status',
                'tickets' => $columnTickets,
            ];
        });

        return response()->json([
            'view' => 'my',
            'columns' => $columns,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function create(Projects $project)
    {
        $this->authorizeProject($project);

        $meta = $this->loadMeta($project->company_id);
        $assignees = User::where('company_id', $project->company_id)->orderBy('name')->get();

        $repositories = $this->repositories($project);
        return view('tickets.create', array_merge($meta, compact('project', 'assignees', 'repositories')));
    }

    public function store(Request $request, Projects $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => ['required', Rule::exists('ticket_statuses', 'id')->where('company_id', $project->company_id)],
            'priority_id' => ['required', Rule::exists('ticket_priorities', 'id')->where('company_id', $project->company_id)],
            'category_id' => ['required', Rule::exists('ticket_categories', 'id')->where('company_id', $project->company_id)],
            'type_id' => ['required', Rule::exists('ticket_types', 'id')->where('company_id', $project->company_id)],
            'assignee_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $project->company_id)],
        ]);

        $ticket = Ticket::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status_id' => $data['status_id'],
            'board_position' => $this->nextBoardPosition($project->id, (int) $data['status_id']),
            'priority_id' => $data['priority_id'],
            'category_id' => $data['category_id'],
            'type_id' => $data['type_id'],
            'assignee_id' => $data['assignee_id'] ?? null,
        ]);

        return redirect()->route('admin.projects.tickets.show', [$project->id, $ticket->id])
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Projects $project, Ticket $ticket)
    {
        $this->authorizeProject($project);
        if ($ticket->project_id !== $project->id) {
            abort(404);
        }

        $ticket->load(['status', 'priority', 'category', 'type', 'assignee', 'creator', 'comments.user', 'activities']);
        $meta = $this->loadMeta($project->company_id);
        $assignees = User::where('company_id', $project->company_id)->orderBy('name')->get();

        $repositories = $this->repositories($project);
        return view('tickets.show', array_merge($meta, compact('project', 'ticket', 'assignees', 'repositories')));
    }

    public function update(Request $request, Projects $project, Ticket $ticket)
    {
        $this->authorizeProject($project);
        if ($ticket->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => ['required', Rule::exists('ticket_statuses', 'id')->where('company_id', $project->company_id)],
            'priority_id' => ['required', Rule::exists('ticket_priorities', 'id')->where('company_id', $project->company_id)],
            'category_id' => ['required', Rule::exists('ticket_categories', 'id')->where('company_id', $project->company_id)],
            'type_id' => ['required', Rule::exists('ticket_types', 'id')->where('company_id', $project->company_id)],
            'assignee_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $project->company_id)],
        ]);

        $oldStatusId = (int) $ticket->status_id;
        $newStatusId = (int) $data['status_id'];

        if ($oldStatusId !== $newStatusId) {
            $data['board_position'] = $this->nextBoardPosition($project->id, $newStatusId);
        }

        $ticket->update($data);

        if ($oldStatusId !== $newStatusId) {
            $this->normalizeColumn($project->id, $oldStatusId);
        }

        return redirect()->route('admin.projects.tickets.show', [$project->id, $ticket->id])
            ->with('success', 'Ticket updated successfully.');
    }

    public function moveOnBoard(Request $request, Projects $project, Ticket $ticket)
    {
        $this->authorizeProject($project);
        if ($ticket->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'status_id' => ['required', Rule::exists('ticket_statuses', 'id')->where('company_id', $project->company_id)],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        $targetStatusId = (int) $data['status_id'];
        $targetPosition = (int) $data['position'];
        $sourceStatusId = (int) $ticket->status_id;

        DB::transaction(function () use ($project, $ticket, $sourceStatusId, $targetStatusId, $targetPosition) {
            if ($sourceStatusId !== $targetStatusId) {
                $ticket->update([
                    'status_id' => $targetStatusId,
                ]);
            }

            $targetIds = Ticket::query()
                ->where('project_id', $project->id)
                ->where('status_id', $targetStatusId)
                ->where('id', '!=', $ticket->id)
                ->orderByRaw('COALESCE(board_position, 2147483647)')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $targetPosition = min(max($targetPosition, 0), count($targetIds));
            array_splice($targetIds, $targetPosition, 0, [$ticket->id]);
            $this->persistColumnOrder($targetIds);

            if ($sourceStatusId !== $targetStatusId) {
                $sourceIds = Ticket::query()
                    ->where('project_id', $project->id)
                    ->where('status_id', $sourceStatusId)
                    ->where('id', '!=', $ticket->id)
                    ->orderByRaw('COALESCE(board_position, 2147483647)')
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                $this->persistColumnOrder($sourceIds);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function nextBoardPosition(int $projectId, int $statusId): int
    {
        $max = Ticket::query()
            ->where('project_id', $projectId)
            ->where('status_id', $statusId)
            ->max('board_position');

        return ((int) $max) + 1;
    }

    private function normalizeColumn(int $projectId, int $statusId): void
    {
        $ticketIds = Ticket::query()
            ->where('project_id', $projectId)
            ->where('status_id', $statusId)
            ->orderByRaw('COALESCE(board_position, 2147483647)')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->persistColumnOrder($ticketIds);
    }

    private function persistColumnOrder(array $ticketIds): void
    {
        foreach ($ticketIds as $index => $ticketId) {
            Ticket::where('id', $ticketId)->update([
                'board_position' => $index + 1,
            ]);
        }
    }
}
