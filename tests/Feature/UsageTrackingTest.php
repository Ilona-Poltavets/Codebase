<?php

use App\Models\Company;
use App\Models\Projects;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Support\UsageReportService;
use App\Support\UsageTracker;

test('login creates usage event', function () {
    $company = Company::create([
        'name' => 'Usage Login Co',
        'domain' => 'usage-login.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Usage Login User',
        'company_id' => $company->id,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('usage_events', [
        'event_type' => 'auth.login',
        'company_id' => $company->id,
        'user_id' => $user->id,
        'resource_type' => 'user',
        'resource_id' => $user->id,
    ]);
});

test('ticket creation creates usage event', function () {
    [$company, $user, $project, $status, $priority, $category, $type] = usageContext();

    $response = $this->actingAs($user)->post(
        route('admin.projects.tickets.store', ['project' => $project->id]),
        [
            'title' => 'Usage tracked ticket',
            'description' => 'Created for usage events',
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'category_id' => $category->id,
            'type_id' => $type->id,
            'assignee_id' => $user->id,
        ]
    );

    $ticket = Ticket::where('project_id', $project->id)
        ->where('title', 'Usage tracked ticket')
        ->firstOrFail();

    $response->assertRedirect(route('admin.projects.tickets.show', [$project->id, $ticket->id], false));

    $this->assertDatabaseHas('usage_events', [
        'event_type' => 'ticket.created',
        'company_id' => $company->id,
        'user_id' => $user->id,
        'project_id' => $project->id,
        'resource_type' => 'ticket',
        'resource_id' => $ticket->id,
    ]);
});

test('ticket comment creation creates usage event', function () {
    [$company, $user, $project, $status, $priority, $category, $type] = usageContext();

    $ticket = Ticket::create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status_id' => $status->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'type_id' => $type->id,
        'assignee_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Ticket with comment usage',
        'description' => null,
    ]);

    $this->actingAs($user)->post(
        route('admin.projects.tickets.comments.store', [$project->id, $ticket->id]),
        ['body' => 'Usage tracking comment']
    )->assertRedirect(route('admin.projects.tickets.show', [$project->id, $ticket->id], false));

    $comment = TicketComment::where('ticket_id', $ticket->id)
        ->where('body', 'Usage tracking comment')
        ->firstOrFail();

    $this->assertDatabaseHas('usage_events', [
        'event_type' => 'ticket.comment.created',
        'company_id' => $company->id,
        'user_id' => $user->id,
        'project_id' => $project->id,
        'resource_type' => 'ticket_comment',
        'resource_id' => $comment->id,
    ]);
});

test('billing summary aggregates usage units by company and event', function () {
    $company = Company::create([
        'name' => 'Usage Billing Co',
        'domain' => 'usage-billing.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Usage Billing User',
        'company_id' => $company->id,
    ]);

    UsageTracker::log('ticket.created', $company->id, $user->id, null, 'ticket', 11, 1, 2);
    UsageTracker::log('ticket.created', $company->id, $user->id, null, 'ticket', 12, 1, 2);
    UsageTracker::log('ticket.comment.created', $company->id, $user->id, null, 'ticket_comment', 21, 3, 3);

    $summary = app(UsageReportService::class)
        ->billingSummary(now()->subDay(), now()->addDay(), $company->id)
        ->keyBy('event_type');

    expect((int) $summary['ticket.created']['events_count'])->toBe(2);
    expect((int) $summary['ticket.created']['total_billable_units'])->toBe(4);
    expect((int) $summary['ticket.comment.created']['events_count'])->toBe(1);
    expect((int) $summary['ticket.comment.created']['total_quantity'])->toBe(3);
});

function usageContext(): array
{
    $company = Company::create([
        'name' => 'Usage Co',
        'domain' => 'usage.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Usage User',
        'company_id' => $company->id,
    ]);

    $project = Projects::create([
        'name' => 'Usage Project',
        'description' => 'Project for usage tests',
        'company_id' => $company->id,
    ]);

    $status = TicketStatus::create([
        'company_id' => $company->id,
        'name' => 'Open',
        'slug' => 'open',
        'sort' => 1,
        'is_active' => true,
    ]);

    $priority = TicketPriority::create([
        'company_id' => $company->id,
        'name' => 'Normal',
        'slug' => 'normal',
        'sort' => 1,
        'is_active' => true,
    ]);

    $category = TicketCategory::create([
        'company_id' => $company->id,
        'name' => 'General',
        'slug' => 'general',
        'sort' => 1,
        'is_active' => true,
    ]);

    $type = TicketType::create([
        'company_id' => $company->id,
        'name' => 'Task',
        'slug' => 'task',
        'sort' => 1,
        'is_active' => true,
    ]);

    return [$company, $user, $project, $status, $priority, $category, $type];
}
