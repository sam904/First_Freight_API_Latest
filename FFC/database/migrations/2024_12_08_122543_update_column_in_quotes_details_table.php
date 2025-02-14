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
            $table->renameColumn('port_id', 'port_of_loading_id');
            $table->unsignedBigInteger('port_of_discharge_id')->nullable()->after('port_of_loading_id');
            $table->foreign('port_of_loading_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('port_of_discharge_id')->references('id')->on('ports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropForeign(['port_of_discharge_id']);
            $table->dropForeign(['port_of_loading_id']);
            $table->dropColumn('port_of_discharge_id');
            $table->renameColumn('port_of_loading_id', 'port_id');
        });
    }
};
