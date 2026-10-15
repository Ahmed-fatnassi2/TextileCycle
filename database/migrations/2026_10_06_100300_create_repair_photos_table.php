<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_request_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->enum('type', ['Avant', 'Après'])->default('Avant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_photos');
    }
};
