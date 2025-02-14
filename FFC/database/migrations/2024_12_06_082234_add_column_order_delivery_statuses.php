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
        Schema::table('order_delivery_statuses', function (Blueprint $table) {
            $table->boolean('current_status')->default(false)->after('delivery_status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_delivery_statuses', function (Blueprint $table) {
            $table->dropColumn('current_status');
        });
    }
};
