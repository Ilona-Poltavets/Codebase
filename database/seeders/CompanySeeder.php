<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $admin = User::where('email', $adminEmail)->first() ?? User::first();

        if (! $admin) {
            return;
        }

        $company = Company::updateOrCreate(
            ['name' => 'Test Company'],
            [
                'description' => 'Test company for demo data.',
                'domain' => 'test-company',
                'owner_id' => $admin->id,
                'plan' => 'free',
            ]
        );

        $admin->company_id = $company->id;
        $admin->save();
    }
}
