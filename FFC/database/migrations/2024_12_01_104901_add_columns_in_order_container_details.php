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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('overweight');
        });

        Schema::table('order_container_details', function (Blueprint $table) {

            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');

            $table->string('overweight')->nullable();
            $table->unsignedBigInteger('order_details_id')->after('id')->nullable();
            $table->foreign('order_details_id')->references('id')->on('order_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_container_details', function (Blueprint $table) {
            $table->string('overweight')->nullable();
        });
        Schema::table('order_container_details', function (Blueprint $table) {
            $table->string('overweight')->nullable();
        });
    }
};
