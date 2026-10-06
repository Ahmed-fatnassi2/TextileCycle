<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_batches', function (Blueprint $table) {
            $table->id();
            $table->string('material_type');
            $table->decimal('weight', 8, 2);
            $table->string('quality_grade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_batches');
    }
};