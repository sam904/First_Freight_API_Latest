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
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->date('order_sent_date')->nullable();
            $table->string('is_vendor_confirmed')->nullable();
            $table->date('picked_up_date')->nullable();
            $table->date('schedule_date')->nullable();
            $table->date('empty_return_on_date')->nullable();
            $table->date('empty_pick_up_cutoff_date')->nullable();
            $table->date('transit_time')->nullable();
            $table->string('mode')->nullable();
            $table->string('free_days')->nullable();

            //foreign key
            $table->unsignedBigInteger('order_details_id');
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('port_of_loading_id');
            $table->unsignedBigInteger('port_of_discharge_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('transhipment_port_id')->nullable();
            $table->unsignedBigInteger('delivery_created_by');
            $table->timestamps();

            $table->foreign('order_details_id')->references('id')->on('order_details')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->foreign('port_of_loading_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('port_of_discharge_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('transhipment_port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('delivery_created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
