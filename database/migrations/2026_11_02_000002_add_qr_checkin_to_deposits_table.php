<?php

use App\Models\Deposit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New "En attente" status: a citizen booked a deposit but has not dropped it off yet.
        Schema::table('deposits', function (Blueprint $table) {
            $table->enum('status', ['En attente', 'Déposé', 'Trié', 'Rejeté'])
                ->default('Déposé')
                ->change();
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->string('code', 16)->nullable()->unique()->after('id');
            $table->timestamp('checked_in_at')->nullable()->after('deposit_date');
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')
                ->constrained('users')->nullOnDelete();
        });

        // Give every existing deposit a QR code.
        DB::table('deposits')->whereNull('code')->orderBy('id')->each(function ($deposit) {
            DB::table('deposits')->where('id', $deposit->id)->update(['code' => Deposit::generateCode()]);
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'checked_in_at']);
        });

        DB::table('deposits')->where('status', 'En attente')->update(['status' => 'Déposé']);

        Schema::table('deposits', function (Blueprint $table) {
            $table->enum('status', ['Déposé', 'Trié', 'Rejeté'])->default('Déposé')->change();
        });
    }
};
