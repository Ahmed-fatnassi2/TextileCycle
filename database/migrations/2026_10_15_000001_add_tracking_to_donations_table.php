<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('category')->default('Vêtements');
            $table->uuid('tracking_token')->nullable()->unique();
        });

        Schema::create('donation_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('donations')->orderBy('id')->each(function (object $donation): void {
            $now = now();
            DB::table('donations')
                ->where('id', $donation->id)
                ->update(['tracking_token' => (string) Str::uuid()]);
            DB::table('donation_status_histories')->insert([
                'donation_id' => $donation->id,
                'from_status' => null,
                'to_status' => $donation->status,
                'changed_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_status_histories');

        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn(['category', 'tracking_token']);
        });
    }
};
