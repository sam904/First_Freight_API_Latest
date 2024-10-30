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
            $table->unsignedBigInteger('rate_id')->nullable()->change();
            $table->unsignedBigInteger('port_id')->nullable()->after('rate_id');
            $table->unsignedBigInteger('destination_id')->nullable()->after('port_id');

            // Add foreign key constraints
            $table->foreign('port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->unsignedBigInteger('rate_id')->nullable(false)->change();
            $table->dropForeign(['port_id']);
            $table->dropForeign(['destination_id']);
            $table->dropColumn(['port_id', 'destination_id']);
        });
    }
};
