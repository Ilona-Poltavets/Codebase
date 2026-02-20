<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_repositories', function (Blueprint $table) {
            $table->string('vcs_type', 10)->default('git')->after('slug');
            $table->index('vcs_type');
        });
    }

    public function down(): void
    {
        Schema::table('project_repositories', function (Blueprint $table) {
            $table->dropIndex(['vcs_type']);
            $table->dropColumn('vcs_type');
        });
    }
};
