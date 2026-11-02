<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip on databases that already ran this under its old name (xxxx_create_deposit_points_table).
        if (Schema::hasTable('deposit_points')) {
            return;
        }

        Schema::create('deposit_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->integer('capacity')->default(100);
            $table->enum('state', ['Ouvert', 'Plein', 'En maintenance', 'Fermé'])
                  ->default('Ouvert');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_points');
    }
};