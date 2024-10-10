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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->string('expiry');
            $table->bigInteger('freight')->unsigned();
            $table->bigInteger('fsc')->unsigned()->nullable();
            $table->string('status')->default('activated');
            $table->timestamps();

            // Foreign Constrain
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
