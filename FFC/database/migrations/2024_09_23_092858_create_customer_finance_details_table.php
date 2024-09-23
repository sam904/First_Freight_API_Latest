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
        Schema::create('customer_finance_details', function (Blueprint $table) {
            $table->id();
            $table->string('finance_name');
            $table->string('finance_designation');
            $table->string('finance_phone');
            $table->string('finance_email');
            $table->string('finance_fax')->nullable();
            $table->unsignedBigInteger('customer_id'); // INT NOT NULL
            $table->timestamps();

            // If you want to add foreign key constraint with 'users' table
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_finance_details');
    }
};
