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
            $table->unsignedBigInteger('address_id')->nullable()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
            $table->unsignedBigInteger('port_of_loading_id')->nullable()->change();
            $table->unsignedBigInteger('port_of_discharge_id')->nullable()->change();
            $table->unsignedBigInteger('destination_id')->nullable()->change();
            $table->unsignedBigInteger('vendor_id')->nullable()->change();
            $table->unsignedBigInteger('transhipment_port_id')->nullable()->change();
            $table->unsignedBigInteger('delivery_created_by')->nullable()->change();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->string("sort_level")->nullable();
            $table->unsignedBigInteger('service_type_id')->nullable()->after('order_id');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes in orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable(false)->change();
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
        // Revert changes in order_deliveries table
        Schema::table('order_deliveries', function (Blueprint $table): void {
            $table->unsignedBigInteger('service_type_id')->after('id');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->unsignedBigInteger('port_of_loading_id')->nullable(false)->change();
            $table->unsignedBigInteger('port_of_discharge_id')->nullable(false)->change();
            $table->unsignedBigInteger('destination_id')->nullable(false)->change();
            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
            $table->unsignedBigInteger('transhipment_port_id')->nullable(false)->change();
            $table->unsignedBigInteger('delivery_created_by')->nullable(false)->change();
        });
        // Revert changes in order_details table
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('service_type_id');
        });
    }
};
