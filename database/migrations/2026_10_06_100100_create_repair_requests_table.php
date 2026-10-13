<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->text('item_description');
            $table->enum('problem_type', ['Fermeture éclair', 'Trou', 'Ourlet']);
            $table->decimal('cost', 8, 2)->default(0);
            $table->enum('status', ['En attente', 'Devis proposé', 'Devis accepté', 'Devis refusé', 'En cours', 'Prêt à récupérer', 'Réparé', 'Récupéré'])->default('En attente');
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
