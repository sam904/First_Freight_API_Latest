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
        Schema::create('order_container_details', function (Blueprint $table) {
            $table->id();
            $table->string('container_no')->nullable();
            $table->string('container_size')->nullable();
            $table->string('po')->nullable();
            $table->string('cpo')->nullable();

            // Foreign key columns
            $table->unsignedBigInteger('order_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_container_details');
    }
};
