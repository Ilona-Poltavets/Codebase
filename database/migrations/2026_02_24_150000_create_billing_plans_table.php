<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->string('description')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('usd');
            $table->enum('interval', ['month', 'year'])->default('month');
            $table->string('stripe_price_id', 120)->nullable()->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('billing_plans')->insert([
            [
                'code' => 'free',
                'name' => 'Free',
                'description' => 'Starter plan',
                'price_cents' => 0,
                'currency' => 'usd',
                'interval' => 'month',
                'stripe_price_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'description' => 'Pro plan',
                'price_cents' => 2900,
                'currency' => 'usd',
                'interval' => 'month',
                'stripe_price_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pro_enterprise',
                'name' => 'Enterprise',
                'description' => 'Enterprise plan',
                'price_cents' => 9900,
                'currency' => 'usd',
                'interval' => 'month',
                'stripe_price_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
