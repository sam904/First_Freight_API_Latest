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
        Schema::table(
            'quote_details',
            function (Blueprint $table) {
                // Drop the existing foreign key
                $table->dropForeign(['port_of_loading_id']);
                $table->unsignedBigInteger('port_of_loading_id')->nullable()->change();
                $table->foreign('port_of_loading_id')->references('id')->on('ports')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropForeign(['port_of_loading_id']);
            $table->unsignedBigInteger('port_of_loading_id')->nullable(false)->change();
            $table->foreign('port_of_loading_id')->references('id')->on('ports')->onDelete('cascade');
        });
    }
};
