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
            $table->string('seal')->nullable();
            $table->string('weight')->nullable();
            $table->string('pallets')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('streamship_line')->nullable();
            $table->date('discharge_date')->nullable();
            $table->date('cargo_ready_date')->nullable();
            $table->string('master_bl')->nullable();
            $table->string('house_bl')->nullable();
            $table->string('freight_location')->nullable();
            $table->string('vessel_voyage')->nullable();
            $table->string('firm_code')->nullable();
            $table->string('commodity')->nullable();
            $table->string('special_instructions')->nullable();
            $table->string('upload_documents')->nullable();
            $table->string('notes')->nullable();
            $table->date('si_cut_off')->nullable();
            $table->date('vgm_cut_off')->nullable();
            $table->date('cy_cut_off')->nullable();
            $table->string('isf')->nullable();
            $table->date('isf_date')->nullable();
            $table->string('isf_no')->nullable();
            $table->string('isf_confirmation')->nullable();
            $table->string('custom_filed')->nullable();
            $table->date('custom_clearance_date')->nullable();
            $table->string('custom_confirmation')->nullable();
            $table->string('last_free_day')->nullable();
            $table->string('dangerous_goods')->nullable();
            $table->string('freight_prepaid')->nullable();
            $table->string('all_inclusive_rate')->nullable();
            $table->string('hts_code')->nullable();
            $table->string('consolidator')->nullable();
            $table->string('importer_of_record_name')->nullable();
            $table->string('importer_of_record_number')->nullable();

            // Fee & Charges
            $table->string('msc_chassis')->nullable();
            $table->string('pierpass_fees')->nullable();
            $table->string('clean_truck_fees')->nullable();
            $table->string('accessorial_charges')->nullable();

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
        Schema::dropIfExists('order_details');
    }
};
