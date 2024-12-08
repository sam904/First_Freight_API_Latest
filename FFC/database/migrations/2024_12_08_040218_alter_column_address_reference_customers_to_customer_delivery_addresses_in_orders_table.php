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
        Schema::table('orders', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['address_id']);
            $table->unsignedBigInteger('address_id')->nullable()->change();

            // Add the new foreign key
            $table->foreign('address_id')
                ->references('id')
                ->on('customer_delivery_addresses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['address_id']);

            // Revert the column to NOT NULL
            $table->unsignedBigInteger('address_id')->nullable(false)->change();

            // Re-add the original foreign key referencing the customers table
            $table->foreign('address_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });
    }
};
