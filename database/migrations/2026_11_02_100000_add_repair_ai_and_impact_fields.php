<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->text('quality_note')->nullable();
            $table->timestamp('quality_checked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->string('item_category')->default('autre')->index();
            $table->decimal('co2_saved_kg', 8, 2)->nullable();
            $table->decimal('waste_saved_kg', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropIndex(['item_category']);
            $table->dropColumn(['quality_score', 'quality_note', 'quality_checked_at', 'started_at', 'ready_at', 'item_category', 'co2_saved_kg', 'waste_saved_kg']);
        });
    }
};
