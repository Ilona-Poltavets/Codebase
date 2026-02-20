<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('board_position')->nullable()->after('status_id');
            $table->index(['project_id', 'status_id', 'board_position'], 'tickets_project_status_board_position_idx');
        });

        $groups = DB::table('tickets')
            ->select('project_id', 'status_id')
            ->groupBy('project_id', 'status_id')
            ->get();

        foreach ($groups as $group) {
            $ids = DB::table('tickets')
                ->where('project_id', $group->project_id)
                ->where('status_id', $group->status_id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $index => $id) {
                DB::table('tickets')
                    ->where('id', $id)
                    ->update(['board_position' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_project_status_board_position_idx');
            $table->dropColumn('board_position');
        });
    }
};
