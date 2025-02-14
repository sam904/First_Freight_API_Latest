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
        Schema::create('order_delivery_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_delivery_id');
            $table->unsignedBigInteger('delivery_status_id');
            $table->timestamps();
            $table->foreign('order_delivery_id')->references('id')->on('order_deliveries')->onDelete('cascade');
            $table->foreign('delivery_status_id')->references('id')->on('order_status_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_statuses');
    }
};
