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
        Schema::create('quote_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_detail_id');
            $table->string('charge_name');
            $table->decimal('amount', 10, 2);  // To store amount with decimal precision
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('quote_detail_id')->references('id')->on('quote_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_charges');
    }
};
