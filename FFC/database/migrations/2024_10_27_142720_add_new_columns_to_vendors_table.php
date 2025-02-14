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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('bank_country_id')->nullable();
            $table->string('bank_swift_code')->nullable();
            $table->string('bank_iban_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->dropColumn(['date_of_expiration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('bank_country_id');
            $table->dropColumn('bank_swift_code');
            $table->dropColumn('bank_iban_number');
            $table->dropColumn('bank_ifsc_code');
            $table->dropColumn('date_of_expiration');
        });
    }
};
