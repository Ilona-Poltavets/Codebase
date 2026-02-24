<?php

use App\Models\Company;
use App\Models\Projects;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;

test('user cannot access project from another tenant', function () {
    $companyA = Company::create([
        'name' => 'Tenant A',
        'domain' => 'tenant-a.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $companyB = Company::create([
        'name' => 'Tenant B',
        'domain' => 'tenant-b.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Tenant A User',
        'company_id' => $companyA->id,
    ]);

    $foreignProject = Projects::create([
        'name' => 'Foreign Project',
        'description' => 'Project from another tenant',
        'company_id' => $companyB->id,
    ]);

    $this->actingAs($user)
        ->get(route('admin.projects.overview', $foreignProject))
        ->assertForbidden();
});

test('nested resources are hidden when they do not belong to project route parameter', function () {
    $company = Company::create([
        'name' => 'Shared Tenant',
        'domain' => 'shared.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Shared Tenant User',
        'company_id' => $company->id,
    ]);

    $projectA = Projects::create([
        'name' => 'Project A',
        'description' => 'First project',
        'company_id' => $company->id,
    ]);

    $projectB = Projects::create([
        'name' => 'Project B',
        'description' => 'Second project',
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
        'name' => 'Medium',
        'slug' => 'medium',
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

    $ticket = Ticket::create([
        'company_id' => $company->id,
        'project_id' => $projectB->id,
        'status_id' => $status->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'type_id' => $type->id,
        'assignee_id' => $user->id,
        'created_by' => $user->id,
        'title' => 'Cross-project ticket',
        'description' => null,
    ]);

    $this->actingAs($user)
        ->get(route('admin.projects.tickets.show', [$projectA, $ticket]))
        ->assertNotFound();
});
