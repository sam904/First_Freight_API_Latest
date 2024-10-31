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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable(false)->change();
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
        });

        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_state')->nullable(false)->change();
            $table->unsignedBigInteger('delivery_country')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable(false)->change();
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
        });
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_state')->nullable(false)->change();
            $table->unsignedBigInteger('delivery_country')->nullable(false)->change();
        });
    }
};
