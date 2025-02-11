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
        Schema::create('followers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('follower_id');
            $table->uuid('following_id');
            $table->enum('status', ['pending', 'accepted'])->default('pending');
            $table->timestamps();

            $table->foreign('follower_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('following_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Ensure a user can't follow another user multiple times
            $table->unique(['follower_id', 'following_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
};
