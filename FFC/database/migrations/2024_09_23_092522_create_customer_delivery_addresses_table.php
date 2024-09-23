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
        Schema::create('customer_delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_name');
            $table->string('delivery_address');
            $table->string('delivery_city');
            $table->string('delivery_state');
            $table->string('delivery_country');
            $table->string('delivery_zip_code');
            $table->unsignedBigInteger('customer_id'); // INT NOT NULL
            $table->timestamps();

            // If you want to add foreign key constraint with 'users' table
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_delivery_addresses');
    }
};
