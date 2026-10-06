<?php

use App\Models\RepairRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->enum('status', RepairRequest::statuses())
                ->default(RepairRequest::STATUS_PENDING)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->enum('status', ['En attente', 'En cours', 'Réparé'])
                ->default('En attente')
                ->change();
        });
    }
};
