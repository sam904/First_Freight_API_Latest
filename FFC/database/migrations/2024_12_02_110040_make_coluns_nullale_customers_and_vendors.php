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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->nullable()->change();
            $table->string('company_tax_id')->nullable()->change();
        });
        Schema::table('customer_finance_details', function (Blueprint $table) {
            $table->string('finance_name')->nullable()->change();
            $table->string('finance_designation')->nullable()->change();
            $table->string('finance_phone')->nullable()->change();
            $table->string('finance_email')->nullable()->change();
            $table->string('finance_fax')->nullable()->change();
        });
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('company_tax_id')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_account_number')->nullable()->change();
            $table->string('bank_routing')->nullable()->change();
            $table->string('bank_address')->nullable()->change();
            $table->string('bank_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropColumn('ignate_cutoff_date');
        });
    }
};
