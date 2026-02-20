<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\ProjectRepository;
use App\Models\Projects;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketTimeLog;
use App\Models\TicketType;
use App\Models\User;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->seedRoles();
        $admin = $this->seedAdmin($roles['admin']);
        $company = $this->seedCompany($admin);
        $users = $this->seedCompanyUsers($company, $roles);
        $meta = $this->seedTicketMeta($company->id);
        $projects = $this->seedProjects($company, $users);

        foreach ($projects as $project) {
            $this->seedRepository($project, $admin);
            $this->seedWiki($project, $admin);
            $this->seedProjectTickets($project, $users, $meta);
        }
    }

    private function seedRoles(): array
    {
        $map = [];
        foreach (['admin', 'owner', 'manager', 'developer', 'member'] as $name) {
            $map[$name] = Role::firstOrCreate(['name' => $name]);
        }

        return $map;
    }

    private function seedAdmin(Role $adminRole): User
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'admin123');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'admin'),
                'full_name' => env('ADMIN_FULL_NAME', 'Platform Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        return $admin;
    }

    private function seedCompany(User $admin): Company
    {
        $name = 'Mercuria Demo';
        $company = Company::updateOrCreate(
            ['name' => $name],
            [
                'description' => 'Demo company seeded for full product testing.',
                'domain' => Company::normalizeDomain('mercuria-demo', $name),
                'owner_id' => $admin->id,
                'plan' => 'pro',
            ]
        );

        if ((int) $admin->company_id !== (int) $company->id) {
            $admin->company_id = $company->id;
            $admin->save();
        }

        return $company;
    }

    private function seedCompanyUsers(Company $company, array $roles): array
    {
        $profiles = [
            ['email' => 'owner@mercuria.local', 'name' => 'owner', 'full_name' => 'Alice Owner', 'role' => 'owner'],
            ['email' => 'manager@mercuria.local', 'name' => 'manager', 'full_name' => 'Marta Manager', 'role' => 'manager'],
            ['email' => 'dev1@mercuria.local', 'name' => 'dev1', 'full_name' => 'Dan Developer', 'role' => 'developer'],
            ['email' => 'dev2@mercuria.local', 'name' => 'dev2', 'full_name' => 'Nina Developer', 'role' => 'developer'],
            ['email' => 'dev3@mercuria.local', 'name' => 'dev3', 'full_name' => 'Oleg Developer', 'role' => 'developer'],
            ['email' => 'member@mercuria.local', 'name' => 'member', 'full_name' => 'Milo Member', 'role' => 'member'],
        ];

        $result = [];
        foreach ($profiles as $profile) {
            $user = User::updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'full_name' => $profile['full_name'],
                    'company_id' => $company->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->roles()->syncWithoutDetaching([$roles[$profile['role']]->id]);
            $result[$profile['name']] = $user;
        }

        return $result;
    }

    private function seedTicketMeta(int $companyId): array
    {
        $statuses = [
            ['name' => 'New', 'slug' => 'new', 'sort' => 1],
            ['name' => 'In progress', 'slug' => 'in_progress', 'sort' => 2],
            ['name' => 'In review', 'slug' => 'in_review', 'sort' => 3],
            ['name' => 'Done', 'slug' => 'done', 'sort' => 4],
        ];
        $priorities = [
            ['name' => 'Critical', 'slug' => 'critical', 'sort' => 1],
            ['name' => 'High', 'slug' => 'high', 'sort' => 2],
            ['name' => 'Normal', 'slug' => 'normal', 'sort' => 3],
            ['name' => 'Low', 'slug' => 'low', 'sort' => 4],
        ];
        $categories = [
            ['name' => 'General', 'slug' => 'general', 'sort' => 1],
            ['name' => 'Backend', 'slug' => 'backend', 'sort' => 2],
            ['name' => 'Frontend', 'slug' => 'frontend', 'sort' => 3],
            ['name' => 'Infrastructure', 'slug' => 'infrastructure', 'sort' => 4],
        ];
        $types = [
            ['name' => 'Feature', 'slug' => 'feature', 'sort' => 1],
            ['name' => 'Bug', 'slug' => 'bug', 'sort' => 2],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'sort' => 3],
        ];

        $upsert = function (string $modelClass, array $rows) use ($companyId): array {
            $ids = [];
            foreach ($rows as $row) {
                $record = $modelClass::updateOrCreate(
                    ['company_id' => $companyId, 'slug' => $row['slug']],
                    [
                        'name' => $row['name'],
                        'sort' => $row['sort'],
                        'is_active' => true,
                    ]
                );
                $ids[$row['slug']] = $record->id;
            }

            return $ids;
        };

        return [
            'statuses' => $upsert(TicketStatus::class, $statuses),
            'priorities' => $upsert(TicketPriority::class, $priorities),
            'categories' => $upsert(TicketCategory::class, $categories),
            'types' => $upsert(TicketType::class, $types),
        ];
    }

    private function seedProjects(Company $company, array $users): array
    {
        $projectDefinitions = [
            ['name' => 'Mercuria Core', 'description' => 'Main product and task workflows.'],
            ['name' => 'Client Portal', 'description' => 'External user-facing portal for company clients.'],
            ['name' => 'Internal Tools', 'description' => 'Automation and internal dashboards for operations.'],
        ];

        $projects = [];
        foreach ($projectDefinitions as $definition) {
            $project = Projects::updateOrCreate(
                ['company_id' => $company->id, 'name' => $definition['name']],
                ['description' => $definition['description']]
            );

            $project->users()->syncWithoutDetaching([
                $users['manager']->id,
                $users['dev1']->id,
                $users['dev2']->id,
                $users['dev3']->id,
                $users['member']->id,
            ]);

            $projects[] = $project;
        }

        return $projects;
    }

    private function seedRepository(Projects $project, User $admin): void
    {
        $name = $project->name.' Repository';
        ProjectRepository::updateOrCreate(
            ['project_id' => $project->id, 'name' => $name],
            [
                'created_by' => $admin->id,
                'slug' => Str::slug($project->name).'-repo',
                'vcs_type' => 'git',
                'path' => '/repos/'.Str::slug($project->name),
                'default_branch' => 'main',
            ]
        );
    }

    private function seedWiki(Projects $project, User $admin): void
    {
        $page = WikiPage::updateOrCreate(
            ['project_id' => $project->id, 'slug' => 'project-handbook'],
            [
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
                'title' => 'Project Handbook',
                'content' => 'Seeded handbook page for testing wiki features and versioning.',
            ]
        );

        WikiPageVersion::updateOrCreate(
            ['wiki_page_id' => $page->id, 'version' => 1],
            [
                'title' => $page->title,
                'content' => $page->content,
                'edited_by' => $admin->id,
            ]
        );
    }

    private function seedProjectTickets(Projects $project, array $users, array $meta): void
    {
        $templates = [
            ['title' => 'Board setup and permissions', 'status' => 'new', 'priority' => 'high', 'category' => 'infrastructure', 'type' => 'feature', 'assignee' => 'dev1'],
            ['title' => 'Fix ticket status sync edge case', 'status' => 'in_progress', 'priority' => 'critical', 'category' => 'backend', 'type' => 'bug', 'assignee' => 'dev2'],
            ['title' => 'Improve card design on board', 'status' => 'in_review', 'priority' => 'normal', 'category' => 'frontend', 'type' => 'maintenance', 'assignee' => 'dev3'],
            ['title' => 'Prepare release checklist', 'status' => 'done', 'priority' => 'low', 'category' => 'general', 'type' => 'feature', 'assignee' => 'manager'],
            ['title' => 'Document API usage for project', 'status' => 'new', 'priority' => 'normal', 'category' => 'general', 'type' => 'maintenance', 'assignee' => null],
            ['title' => 'Optimize project activity feed query', 'status' => 'in_progress', 'priority' => 'high', 'category' => 'backend', 'type' => 'feature', 'assignee' => 'dev1'],
        ];

        $positionByStatus = [];

        foreach ($templates as $index => $item) {
            $statusId = $meta['statuses'][$item['status']];
            $positionByStatus[$statusId] = ($positionByStatus[$statusId] ?? 0) + 1;
            $assigneeId = $item['assignee'] ? $users[$item['assignee']]->id : null;

            $ticket = Ticket::updateOrCreate(
                ['project_id' => $project->id, 'title' => '['.$project->name.'] '.$item['title']],
                [
                    'company_id' => $project->company_id,
                    'status_id' => $statusId,
                    'board_position' => $positionByStatus[$statusId],
                    'priority_id' => $meta['priorities'][$item['priority']],
                    'category_id' => $meta['categories'][$item['category']],
                    'type_id' => $meta['types'][$item['type']],
                    'assignee_id' => $assigneeId,
                    'created_by' => $users['manager']->id,
                    'description' => 'Seeded demo ticket #'.($index + 1).' for '.$project->name.'.',
                ]
            );

            $this->seedTicketDetails($ticket, $users);
        }
    }

    private function seedTicketDetails(Ticket $ticket, array $users): void
    {
        TicketComment::updateOrCreate(
            ['ticket_id' => $ticket->id, 'user_id' => $users['manager']->id],
            ['body' => 'Please validate this task in demo environment.']
        );

        TicketComment::updateOrCreate(
            ['ticket_id' => $ticket->id, 'user_id' => $users['dev1']->id],
            ['body' => 'Work in progress, updates are posted in the activity feed.']
        );

        TicketActivity::updateOrCreate(
            ['ticket_id' => $ticket->id, 'type' => 'seeded_created'],
            [
                'user_id' => $users['manager']->id,
                'meta' => ['source' => 'ProjectDemoSeeder'],
            ]
        );

        TicketTimeLog::updateOrCreate(
            ['ticket_id' => $ticket->id, 'user_id' => $users['dev1']->id, 'description' => 'Initial implementation'],
            [
                'minutes' => 45,
                'logged_at' => now()->subDays(rand(1, 7)),
            ]
        );

        ActivityLog::updateOrCreate(
            [
                'project_id' => $ticket->project_id,
                'event_type' => 'ticket.seeded.'.$ticket->id,
                'created_at' => now()->startOfDay(),
            ],
            [
                'user_id' => $users['manager']->id,
                'details' => [
                    'ticket_id' => $ticket->id,
                    'ticket_title' => $ticket->title,
                ],
            ]
        );
    }
}
