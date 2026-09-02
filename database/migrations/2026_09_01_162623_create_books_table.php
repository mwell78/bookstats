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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->integer('pages')->nullable();
            $table->string('format')->nullable();
            $table->string('status')->default('Ungelesen'); // Standardwert aktualisiert
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('notes')->nullable();
            $table->string('published_year')->nullable();
            $table->string('genre')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
