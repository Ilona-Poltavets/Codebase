<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wiki_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_page_id')->constrained('wiki_pages')->onDelete('cascade');
            $table->unsignedInteger('version');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['wiki_page_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wiki_page_versions');
    }
};
