<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');

        $permissions = DB::table('permissions')->pluck('id', 'name');

        $rolePermissions = [
            'admin' => array_values($permissions->toArray()),
            'member' => [
                $permissions['view_project'],
            ],
            'owner' => [
                $permissions['manage_companies'],
                $permissions['invite_users'],
                $permissions['manage_billing'],
                $permissions['manage_users'],
                $permissions['create_project'],
                $permissions['view_activity'],
            ],
            'manager' => [
                $permissions['create_project'],
                $permissions['edit_project'],
                $permissions['delete_project'],
                $permissions['view_project'],
                $permissions['assign_ticket'],
                $permissions['view_ticket'],
                $permissions['create_ticket'],
                $permissions['view_activity'],
            ],
            'developer' => [
                $permissions['view_project'],
                $permissions['view_ticket'],
                $permissions['create_ticket'],
                $permissions['edit_ticket'],
                $permissions['move_card'],
                $permissions['view_board'],
                $permissions['log_time'],
            ],
        ];

        foreach ($rolePermissions as $role => $perms) {
            foreach ($perms as $permissionId) {
                DB::table('permission_role')->insert([
                    'role_id' => $roles[$role],
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
}
