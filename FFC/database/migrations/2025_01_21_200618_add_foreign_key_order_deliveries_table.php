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
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('receiver_name_id')->nullable();
            $table->unsignedBigInteger('receiver_address_id')->nullable();

            // Adding foreign key constraints
            $table->foreign('receiver_name_id')->references('id')->on('receiver_names')->onDelete('cascade');
            $table->foreign('receiver_address_id')->references('id')->on('receiver_addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['receiver_name_id']);
            $table->dropForeign(['receiver_address_id']);
            $table->dropColumn(['receiver_name_id', 'receiver_address_id']);
        });
    }
};
