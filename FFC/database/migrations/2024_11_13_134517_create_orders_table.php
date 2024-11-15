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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('received_date')->nullable();
            $table->string("address")->nullable();
            $table->string('container_no')->nullable();
            $table->string('bl')->nullable();
            $table->string('po')->nullable();
            $table->string('cpo')->nullable();
            $table->string('seal')->nullable();
            $table->string('last_free_day')->nullable();
            $table->string('container_size')->nullable();
            $table->string('weight')->nullable();
            $table->string('overweight')->nullable();
            $table->string('pallets')->nullable();
            $table->string('streamship_line')->nullable();
            $table->date('discharge_date')->nullable();
            $table->string('freight_location')->nullable();
            $table->string('firm_code')->nullable();
            $table->string('vessel_voyage')->nullable();
            $table->date('eta')->nullable();
            $table->string('commodity')->nullable();
            $table->string('special_instructions')->nullable();
            $table->string('notes')->nullable();
            $table->string('upload_documents')->nullable();
            $table->string('free_days')->nullable();
            $table->date('delivery_order_sent_date')->nullable();
            $table->string('delivery_vendor_confirm')->nullable();
            $table->date('delivery_picked_up_date')->nullable();
            $table->date('delivery_schedule_date')->nullable();
            $table->date('delivery_empty_return_date')->nullable();
            $table->string('msc_chassis')->nullable();
            $table->string('pierpass_fees')->nullable();
            $table->string('clean_truck_fees')->nullable();
            $table->string('accessorial_charges')->nullable();
            // $table->string('status')->default('active');

            // Foreign key columns
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('port_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
