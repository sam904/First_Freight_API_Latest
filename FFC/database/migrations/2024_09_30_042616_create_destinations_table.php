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
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('activated');
            $table->unsignedBigInteger('county_id');
            $table->timestamps();

            // Foreign key constrain
            $table->foreign('county_id')->references('id')->on('counties')->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade'); // Foreign key to countries
            $table->foreignId('state_id')->constrained()->onDelete('cascade'); // Foreign key to states
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
