<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('uuid', 64);
            $table->string('name', 120);
            $table->string('platform', 50)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->string('pairing_code_hash', 64)->nullable()->index();
            $table->timestamp('pairing_code_expires_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'uuid']);
            $table->index(['company_id', 'user_id']);
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->enum('token_type', ['access', 'refresh'])->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();
            $table->index(['device_id', 'token_type', 'revoked_at'], 'device_tokens_device_type_revoked_idx');
        });

        Schema::create('time_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->string('timezone', 64)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'user_id', 'started_at'], 'time_sessions_company_user_started_idx');
        });

        Schema::create('time_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('time_sessions')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->unsignedInteger('seconds')->default(0);
            $table->unsignedTinyInteger('activity_level')->nullable();
            $table->boolean('is_idle')->default(false)->index();
            $table->string('app_name', 190)->nullable();
            $table->string('window_title', 190)->nullable();
            $table->string('url', 2048)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['session_id', 'started_at']);
            $table->index(['company_id', 'user_id', 'started_at'], 'time_segments_company_user_started_idx');
        });

        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('time_sessions')->nullOnDelete();
            $table->foreignId('segment_id')->nullable()->constrained('time_segments')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('disk', 40)->default('local');
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('sha256', 64)->nullable()->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_blurred')->default(false);
            $table->timestamp('taken_at')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'user_id', 'taken_at'], 'screenshots_company_user_taken_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
        Schema::dropIfExists('time_segments');
        Schema::dropIfExists('time_sessions');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('devices');
    }
};
