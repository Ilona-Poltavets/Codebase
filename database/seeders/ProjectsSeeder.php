<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Projects;
use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::where('name', 'Test Company')->first();
        if (! $company) {
            return;
        }

        Projects::updateOrCreate(
            ['name' => 'Test Project', 'company_id' => $company->id],
            ['description' => 'Test project for demo data.']
        );
    }
}
