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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['video', 'text', 'quiz', 'assignment', 'file'])->default('text');
            $table->text('content')->nullable(); // For text content or video URL
            $table->string('video_url')->nullable(); // For YouTube or other video platform URLs
            $table->boolean('video_protected')->default(false); // For domain-restricted access
            $table->string('video_duration')->nullable(); // In HH:MM:SS format
            $table->integer('position')->default(0); // For ordering lessons within a module
            $table->integer('duration_minutes')->default(0); // Estimated time to complete
            $table->boolean('is_published')->default(false);
            $table->boolean('is_free_preview')->default(false); // Whether this lesson is available as a preview
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};