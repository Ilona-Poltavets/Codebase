<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_files', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('name');
            $table->boolean('is_current')->default(true)->after('version');
            $table->index(['project_id', 'folder_id', 'name', 'is_current'], 'project_files_current_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('project_files', function (Blueprint $table) {
            $table->dropIndex('project_files_current_lookup');
            $table->dropColumn(['version', 'is_current']);
        });
    }
};
