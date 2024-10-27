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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id')->unique();
            $table->string('url')->nullable();
            $table->string('imdb_code')->nullable();
            $table->string('title')->nullable();
            $table->string('title_english')->nullable();
            $table->string('title_long')->nullable();
            $table->string('slug')->nullable();
            $table->year('year')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('runtime')->nullable();
            $table->text('summary')->nullable();
            $table->text('description_full')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('yt_trailer_code')->nullable();
            $table->string('language')->nullable();
            $table->string('mpa_rating')->nullable();
            $table->string('background_image')->nullable();
            $table->string('background_image_original')->nullable();
            $table->string('small_cover_image')->nullable();
            $table->string('medium_cover_image')->nullable();
            $table->string('large_cover_image')->nullable();
            $table->string('state')->nullable();
            $table->timestamp('date_uploaded')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
