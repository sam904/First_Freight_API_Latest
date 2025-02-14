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
            $table->string('payment_term')->nullable();
            $table->string('upload_w9')->nullable()->change();
            $table->string('void_check')->nullable()->change();
            $table->string('upload_insurance_certificate')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('payment_term');
            $table->string('upload_w9')->nullable(false)->change();
            $table->string('void_check')->nullable(false)->change();
            $table->string('upload_insurance_certificate')->nullable(false)->change();
        });
    }
};
