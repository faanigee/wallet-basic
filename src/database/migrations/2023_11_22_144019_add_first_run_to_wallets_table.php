<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('trx_balance', 64, 0)->default(0)->after('balance');
            $table->integer('first_run')->default(0)->after('trx_balance');

            $table->index(['first_run'], 'first_run_ind');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('trx_balance');
            $table->dropColumn('first_run');
        });
    }
};
