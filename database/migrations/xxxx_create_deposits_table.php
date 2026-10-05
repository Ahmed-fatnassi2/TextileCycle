<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('deposit_point_id')->constrained()->onDelete('cascade');
            $table->decimal('weight_kg', 8, 2);
            $table->enum('status', ['Déposé', 'Trié', 'Rejeté'])->default('Déposé');
            $table->enum('state', ['Neuf', 'Bon état', 'Usé', 'Déchiré'])->default('Bon état');
            $table->date('deposit_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};