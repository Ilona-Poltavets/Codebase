<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            // Companies
            ['name' => 'manage_companies'],
            ['name' => 'invite_users'],
            ['name' => 'manage_billing'],

            // Users
            ['name' => 'manage_users'],
            ['name' => 'edit_profile'],

            // Projects
            ['name' => 'create_project'],
            ['name' => 'edit_project'],
            ['name' => 'delete_project'],
            ['name' => 'view_project'],

            // Tickets
            ['name' => 'create_ticket'],
            ['name' => 'edit_ticket'],
            ['name' => 'delete_ticket'],
            ['name' => 'assign_ticket'],
            ['name' => 'view_ticket'],

            // Boards (Trello)
            ['name' => 'view_board'],
            ['name' => 'move_card'],

            // Wiki
            ['name' => 'create_wiki'],
            ['name' => 'edit_wiki'],
            ['name' => 'delete_wiki'],
            ['name' => 'view_wiki'],

            // Files
            ['name' => 'upload_file'],
            ['name' => 'delete_file'],
            ['name' => 'view_file'],

            // Time Tracking
            ['name' => 'log_time'],
            ['name' => 'view_time_reports'],

            // Activity
            ['name' => 'view_activity'],
        ]);
    }
}
