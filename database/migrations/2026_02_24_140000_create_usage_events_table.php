<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('event_type', 120)->index();
            $table->string('resource_type', 80)->nullable()->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('billable_units')->default(1);
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['company_id', 'occurred_at'], 'usage_events_company_occurred_idx');
            $table->index(['company_id', 'event_type', 'occurred_at'], 'usage_events_company_event_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};
