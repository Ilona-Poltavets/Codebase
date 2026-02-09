<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketMetaSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            $this->seedStatuses($company->id);
            $this->seedPriorities($company->id);
            $this->seedCategories($company->id);
            $this->seedTypes($company->id);
        }
    }

    private function seedStatuses(int $companyId): void
    {
        $items = [
            ['name' => 'New', 'slug' => 'new', 'sort' => 1],
            ['name' => 'In progress', 'slug' => 'in_progress', 'sort' => 2],
            ['name' => 'In review', 'slug' => 'in_review', 'sort' => 3],
            ['name' => 'Done', 'slug' => 'done', 'sort' => 4],
            ['name' => 'Invalid', 'slug' => 'invalid', 'sort' => 5],
        ];

        foreach ($items as $item) {
            TicketStatus::updateOrCreate(
                ['company_id' => $companyId, 'slug' => $item['slug']],
                ['name' => $item['name'], 'sort' => $item['sort'], 'is_active' => true]
            );
        }
    }

    private function seedPriorities(int $companyId): void
    {
        $items = [
            ['name' => 'Critical', 'slug' => 'critical', 'sort' => 1],
            ['name' => 'High', 'slug' => 'high', 'sort' => 2],
            ['name' => 'Normal', 'slug' => 'normal', 'sort' => 3],
            ['name' => 'Low', 'slug' => 'low', 'sort' => 4],
        ];

        foreach ($items as $item) {
            TicketPriority::updateOrCreate(
                ['company_id' => $companyId, 'slug' => $item['slug']],
                ['name' => $item['name'], 'sort' => $item['sort'], 'is_active' => true]
            );
        }
    }

    private function seedCategories(int $companyId): void
    {
        $items = [
            ['name' => 'General', 'slug' => 'general', 'sort' => 1],
            ['name' => 'API', 'slug' => 'api', 'sort' => 2],
            ['name' => 'Cosmetic', 'slug' => 'cosmetic', 'sort' => 3],
            ['name' => 'Refactoring', 'slug' => 'refactoring', 'sort' => 4],
            ['name' => 'Security', 'slug' => 'security', 'sort' => 5],
        ];

        foreach ($items as $item) {
            TicketCategory::updateOrCreate(
                ['company_id' => $companyId, 'slug' => $item['slug']],
                ['name' => $item['name'], 'sort' => $item['sort'], 'is_active' => true]
            );
        }
    }

    private function seedTypes(int $companyId): void
    {
        $items = [
            ['name' => 'Feature', 'slug' => 'feature', 'sort' => 1],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'sort' => 2],
            ['name' => 'Bug', 'slug' => 'bug', 'sort' => 3],
        ];

        foreach ($items as $item) {
            TicketType::updateOrCreate(
                ['company_id' => $companyId, 'slug' => $item['slug']],
                ['name' => $item['name'], 'sort' => $item['sort'], 'is_active' => true]
            );
        }
    }
}
