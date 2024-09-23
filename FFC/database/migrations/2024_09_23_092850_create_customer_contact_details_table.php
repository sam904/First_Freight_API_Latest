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
        Schema::create('customer_contact_details', function (Blueprint $table) {
            $table->id();
            $table->string('contact_name');
            $table->string('contact_designation');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->string('contact_fax')->nullable();
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
        Schema::dropIfExists('customer_contact_details');
    }
};
