<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('ticket_statuses')->onDelete('restrict');
            $table->foreignId('priority_id')->constrained('ticket_priorities')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('ticket_categories')->onDelete('restrict');
            $table->foreignId('type_id')->constrained('ticket_types')->onDelete('restrict');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('tickets');
    }
};
