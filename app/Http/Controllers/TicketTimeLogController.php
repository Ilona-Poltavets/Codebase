<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketTimeLog;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class TicketTimeLogController extends Controller
{
    private function authorizeProject(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    public function store(Request $request, Projects $project, Ticket $ticket)
    {
        $this->authorizeProject($project);
        if ($ticket->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'minutes' => 'required|integer|min:1|max:1440',
            'description' => 'nullable|string|max:255',
            'logged_at' => 'nullable|date',
        ]);

        TicketTimeLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'minutes' => $data['minutes'],
            'description' => $data['description'] ?? null,
            'logged_at' => $data['logged_at'] ?? now(),
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'type' => 'time_logged',
            'meta' => [
                'minutes' => (int) $data['minutes'],
                'description' => $data['description'] ?? null,
            ],
        ]);

        ActivityLogger::log($project->id, $request->user()->id, 'ticket.time_logged', [
            'ticket_id' => $ticket->id,
            'ticket_title' => $ticket->title,
            'minutes' => (int) $data['minutes'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.projects.tickets.show', [$project->id, $ticket->id])
            ->with('success', 'Time logged.');
    }
}
