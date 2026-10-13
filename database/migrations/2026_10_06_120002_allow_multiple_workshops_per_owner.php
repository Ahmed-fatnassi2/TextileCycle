<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->index('owner_id', 'workshops_owner_id_index');
            $table->dropUnique('workshops_owner_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->unique('owner_id');
            $table->dropIndex('workshops_owner_id_index');
        });
    }
};