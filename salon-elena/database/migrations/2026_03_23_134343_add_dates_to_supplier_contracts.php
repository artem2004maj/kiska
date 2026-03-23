<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_contracts', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable()->after('status');
            $table->dateTime('received_at')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_contracts', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'received_at']);
        });
    }
};