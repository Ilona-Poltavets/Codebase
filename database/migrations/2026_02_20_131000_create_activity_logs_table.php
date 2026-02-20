<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('event_type', 120)->index();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['project_id', 'event_type', 'created_at'], 'activity_logs_project_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
