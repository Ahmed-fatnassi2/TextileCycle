<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('id');
            $table->string('category', 20)->default('Mixte')->after('quantity_items');
            $table->text('description')->nullable()->after('category');
            // Nullable en base pour les anciennes demandes ; obligatoires dans les formulaires
            $table->string('delivery_address')->nullable()->after('description');
            $table->string('delivery_city', 100)->nullable()->after('delivery_address');
        });

        // Référence des demandes existantes : DON-<année de soumission>-<numéro>
        DB::table('donations')->whereNull('reference')->orderBy('id')->each(function ($donation) {
            $year = $donation->created_at ? substr($donation->created_at, 0, 4) : date('Y');
            DB::table('donations')->where('id', $donation->id)->update([
                'reference' => sprintf('DON-%s-%04d', $year, $donation->id),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'category', 'description', 'delivery_address', 'delivery_city']);
        });
    }
};
