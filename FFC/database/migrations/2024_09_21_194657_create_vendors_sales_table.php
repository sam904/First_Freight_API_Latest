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
        Schema::create('vendors_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sales_name');
            $table->string('sales_designation');
            $table->string('sales_phone');
            $table->string('sales_email');
            $table->string('sales_fax')->nullable();
            $table->unsignedBigInteger('vendors_id'); // INT NOT NULL
            $table->timestamps();

            // If you want to add foreign key constraint with 'users' table
            $table->foreign('vendors_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors_sales');
    }
};
