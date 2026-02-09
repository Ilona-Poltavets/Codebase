<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
            $table->unique(['company_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('ticket_priorities');
        Schema::dropIfExists('ticket_statuses');
    }
};
