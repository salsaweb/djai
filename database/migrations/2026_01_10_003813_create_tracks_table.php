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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('spotify_id')->unique();
            $table->string('name');
            $table->text('artists')->nullable(); // JSON array of artist names
            $table->string('album')->nullable();
            $table->string('album_art_url')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('spotify_url');
            $table->string('preview_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
