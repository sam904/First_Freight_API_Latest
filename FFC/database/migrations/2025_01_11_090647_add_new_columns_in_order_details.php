<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('ein')->nullable();
            $table->longText('inco')->nullable();
            $table->string('customer_rate')->nullable();
            $table->string('pallet_dimensions')->nullable();
            $table->string('cubic_meter')->nullable();
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->integer('transit_time')->change()->nullable();
            $table->dateTime('schedule_date')->change()->nullable();
            $table->unsignedBigInteger('rail_ramp_id')->nullable();
            $table->foreign('rail_ramp_id')->references('id')->on('ports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('ein');
            $table->dropColumn('inco');
            $table->dropColumn('customer_rate');
            $table->dropColumn('pallet_dimensions');
            $table->dropColumn('cubic_meter');
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->date('transit_time')->change()->nullable();
            $table->date('schedule_date')->change()->nullable();
        });
    }
};
