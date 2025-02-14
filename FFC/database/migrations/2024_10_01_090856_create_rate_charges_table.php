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
        Schema::create('rate_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_name')->nullable();
            $table->bigInteger('amount')->unsigned()->nullable();
            $table->timestamps();
            $table->foreignId('rate_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_charges');
    }
};
