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
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('manufacturer')->nullable();
            $table->string('manufacturer_address')->nullable();
            $table->string('ship_to_party')->nullable();
            $table->string('ship_to_party_address')->nullable();
            $table->date('mother_vessel_date')->nullable();
            $table->date('vessel_loaded_date')->nullable();
            $table->string('consolidator_address')->nullable()->after('consolidator');
        });

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->string('country_of_origin')->nullable();
            $table->date('ignate_cutoff_date')->nullable();
        });

        Schema::create('order_hormonizes', function (Blueprint $table) {
            $table->id();
            $table->string('tarriff_schedule_number')->nullable();
            // Foreign key columns
            $table->unsignedBigInteger('order_details_id');
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('order_details_id')->references('id')->on('order_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('manufacturer');
            $table->dropColumn('manufacturer_address');
            $table->dropColumn('ship_to_party');
            $table->dropColumn('ship_to_party_address');
            $table->dropColumn('mother_vessel_date');
            $table->dropColumn('vessel_loaded_date');
            $table->dropColumn('consolidator_address');
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropColumn('country_of_origin');
        });
        Schema::dropIfExists('order_hormonizes');
    }
};
