<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'stripe_customer_id')) {
                $table->string('stripe_customer_id', 120)->nullable()->unique()->after('plan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'stripe_customer_id')) {
                $table->dropUnique(['stripe_customer_id']);
                $table->dropColumn('stripe_customer_id');
            }
        });
    }
};
