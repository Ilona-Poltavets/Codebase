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

        $ticket->update($data);

        return redirect()->route('admin.projects.tickets.show', [$project->id, $ticket->id])
            ->with('success', 'Ticket updated successfully.');
    }
}
