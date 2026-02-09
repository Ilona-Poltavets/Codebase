<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
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
            'body' => 'required|string',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('admin.projects.tickets.show', [$project->id, $ticket->id])
            ->with('success', 'Comment added.');
    }
}
