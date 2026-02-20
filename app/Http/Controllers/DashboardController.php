<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $projectsQuery = Projects::query()->orderByDesc('updated_at');
        if (! $user->hasRole('admin')) {
            $projectsQuery->where('company_id', $user->company_id);
        }

        $projects = (clone $projectsQuery)
            ->withCount('tickets')
            ->limit(8)
            ->get();

        $ticketsQuery = Ticket::query()->with(['project', 'status', 'priority', 'assignee']);
        if (! $user->hasRole('admin')) {
            $ticketsQuery->where('company_id', $user->company_id);
        }

        $companyIdForStatuses = $user->company_id ?: (int) (clone $projectsQuery)->value('company_id');
        $statuses = TicketStatus::query()
            ->where('company_id', $companyIdForStatuses)
            ->where('is_active', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'slug']);

        $statusCounts = (clone $ticketsQuery)
            ->selectRaw('status_id, COUNT(*) as aggregate')
            ->groupBy('status_id')
            ->pluck('aggregate', 'status_id');

        $statusBreakdown = $statuses->map(function (TicketStatus $status) use ($statusCounts) {
            return [
                'name' => $status->name,
                'slug' => $status->slug,
                'count' => (int) ($statusCounts[$status->id] ?? 0),
            ];
        });

        $totalTickets = (clone $ticketsQuery)->count();
        $myTicketsCount = (clone $ticketsQuery)->where('assignee_id', $user->id)->count();
        $doneStatusIds = $statuses->whereIn('slug', ['done', 'closed', 'resolved'])->pluck('id');
        $doneTicketsCount = $doneStatusIds->count()
            ? (clone $ticketsQuery)->whereIn('status_id', $doneStatusIds)->count()
            : 0;

        $recentTickets = (clone $ticketsQuery)->orderByDesc('updated_at')->limit(8)->get();
        $myQueue = (clone $ticketsQuery)
            ->where('assignee_id', $user->id)
            ->whereNotIn('status_id', $doneStatusIds)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'projects' => $projects,
            'statusBreakdown' => $statusBreakdown,
            'totalTickets' => $totalTickets,
            'myTicketsCount' => $myTicketsCount,
            'doneTicketsCount' => $doneTicketsCount,
            'recentTickets' => $recentTickets,
            'myQueue' => $myQueue,
            'boardsProject' => $projects->first(),
        ]);
    }
}
