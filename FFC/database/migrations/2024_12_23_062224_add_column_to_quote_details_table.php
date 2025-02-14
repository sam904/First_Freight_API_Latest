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
        Schema::table('quote_details', function (Blueprint $table) {
            $table->string('fsc_amount')->nullable();
            $table->string('dry_fsc')->nullable();
            $table->string('quote_tab_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropColumn('fsc_amount');
            $table->dropColumn('dry_fsc');
            $table->dropColumn('quote_tab_name');
        });
    }
};
