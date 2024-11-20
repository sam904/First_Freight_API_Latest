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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->string('shipper')->nullable();
            $table->string('shipper_address')->nullable();
            $table->string('consignee')->nullable();
            $table->string('consignee_address')->nullable();
            $table->string('buyer')->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('notify_party')->nullable();
            $table->date('booking_request_sent_date')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('bc_sent_to_shipper')->nullable();
            $table->string('bl')->nullable();
            $table->string('seal')->nullable();
            $table->string('weight')->nullable();
            $table->integer('pallets')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('streamship_line')->nullable();
            $table->date('discharge_date')->nullable();
            $table->date('cargo_ready_date')->nullable();
            $table->string('vessel_voyage')->nullable();
            $table->string('commodity')->nullable();
            $table->date('si_cut_off')->nullable();
            $table->date('vgm_cut_off')->nullable();
            $table->date('cy_cut_off')->nullable();
            $table->date('isf')->nullable();
            $table->date('isf_date')->nullable();
            $table->string('isf_no')->nullable();
            $table->string('custom_filed')->nullable();
            $table->string('last_free_day')->nullable();
            $table->string('freight_location')->nullable();
            $table->string('dangerous_goods')->nullable();
            $table->string('freight_prepaid')->nullable();
            $table->string('all_inclusive_rate')->nullable();
            $table->string('hts_code')->nullable();
            $table->string('firm_code')->nullable();
            $table->string('special_instructions')->nullable();
            $table->string('notes')->nullable();
            $table->string('upload_documents')->nullable();

            // Delivery && Pickup
            $table->date('delivery_order_sent_date')->nullable();
            $table->string('delivery_vendor_confirm')->nullable();
            $table->date('delivery_picked_up_date')->nullable();
            $table->date('delivery_schedule_date')->nullable();
            $table->date('delivery_empty_return_date')->nullable();
            $table->date('delivery_empty_pick_up_cutoff_date')->nullable();
            $table->date('transmit_time')->nullable();
            $table->string('delivery_mode')->nullable();
            $table->string('delivery_free_days')->nullable();

            // Fee & Charges
            $table->string('msc_chassis')->nullable();
            $table->double('pierpass_fees')->nullable();
            $table->double('clean_truck_fees')->nullable();
            $table->double('accessorial_charges')->nullable();

            // Foreign key columns
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('port_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('transhipment_port_id')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('transhipment_port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
