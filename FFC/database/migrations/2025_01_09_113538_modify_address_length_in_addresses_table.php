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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address', 1000)->change();
        });
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->string('delivery_address', 1000)->change();
        });
        Schema::table('customer_shipping_addresses', function (Blueprint $table) {
            $table->string('shipping_address', 1000)->change();
        });
        Schema::table('customer_warehouse_addresses', function (Blueprint $table) {
            $table->string('warehouse_address', 1000)->change();
        });
        Schema::table('order_details', function (Blueprint $table) {
            DB::statement("UPDATE order_details SET shipper_address = '' WHERE shipper_address IS NULL");
            $table->string('shipper_address', 800)->change();
            DB::statement("UPDATE order_details SET consignee_address = '' WHERE consignee_address IS NULL");
            $table->string('consignee_address', 800)->change();
            DB::statement("UPDATE order_details SET buyer_address = '' WHERE buyer_address IS NULL");
            $table->string('buyer_address', 800)->change();
            DB::statement("UPDATE order_details SET consolidator_address = '' WHERE consolidator_address IS NULL");
            $table->string('consolidator_address', 800)->change();
            DB::statement("UPDATE order_details SET manufacturer_address = '' WHERE manufacturer_address IS NULL");
            $table->string('manufacturer_address', 800)->change();
            DB::statement("UPDATE order_details SET ship_to_party_address = '' WHERE ship_to_party_address IS NULL");
            $table->string('ship_to_party_address', 800)->change();
            DB::statement("UPDATE order_details SET freight_location = '' WHERE freight_location IS NULL");
            $table->string('freight_location', 800)->change();
            DB::statement("UPDATE order_details SET upload_documents = '' WHERE upload_documents IS NULL");
            $table->longText('upload_documents')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address', 1000)->change();
        });
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->string('delivery_address', 1000)->change();
        });
        Schema::table('customer_shipping_addresses', function (Blueprint $table) {
            $table->string('shipping_address', 1000)->change();
        });
        Schema::table('customer_warehouse_addresses', callback: function (Blueprint $table) {
            $table->string('warehouse_address', 1000)->change();
        });
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('shipper_address', 800)->change();
            $table->string('consignee_address', 800)->change();
            $table->string('buyer_address', 800)->change();
            $table->string('consolidator_address', 800)->change();
            $table->string('manufacturer_address', 800)->change();
            $table->string('ship_to_party_address', 800)->change();
            $table->string('freight_location', 800)->change();
            $table->text('upload_documents')->change();
        });
    }
};
