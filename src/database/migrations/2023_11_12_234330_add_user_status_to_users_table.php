<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('wallet_status', ['active', 'banned', 'underinvestigation', 'defaulter'])->default('active')->after('id');
            $table->index(['wallet_status'], 'wallet_status_ind');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_status');
        });
    }
};
