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
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            // Drop the existing string columns
            $table->dropColumn(['delivery_state', 'delivery_country']);
        });

        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            // Add new unsigned big integer columns
            $table->unsignedBigInteger('delivery_state')->nullable()->after('delivery_city');
            $table->unsignedBigInteger('delivery_country')->nullable()->after('delivery_state');

            // Add foreign key constraints
            $table->foreign('delivery_state')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('delivery_country')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            // Drop the foreign keys and unsigned big integer columns
            $table->dropForeign(['delivery_state']);
            $table->dropForeign(['delivery_country']);
            $table->dropColumn(['delivery_state', 'delivery_country']);

            // Re-add the original string columns
            $table->string('delivery_state')->after('id');
            $table->string('delivery_country')->after('delivery_state');
        });
    }
};
