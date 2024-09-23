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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_type');
            $table->string('company_name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('zip_code');
            $table->string('company_tax_id');
            $table->string('mc_number');
            $table->string('scac_number');
            $table->string('us_dot_number');
            $table->string('upload_w9');
            $table->string('void_check');
            $table->string('upload_insurance_certificate');
            $table->date('date_of_expiration');
            $table->string('bank_name');
            $table->bigInteger('bank_account_number');
            $table->string('bank_routing');
            $table->string('bank_address');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
