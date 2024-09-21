<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // INT NOT NULL
            $table->text('access_token');
            $table->dateTime('access_token_expires_at')->nullable();
            $table->text('refresh_token');
            $table->dateTime('refresh_token_expires_at')->nullable();
            $table->string('status')->default('activated');
            $table->timestamps();

            // Indexing
            $table->index('user_id'); // Adding index to user_id
            $table->index('status'); // Adding index to user_id

            // If you want to add foreign key constraint with 'users' table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
