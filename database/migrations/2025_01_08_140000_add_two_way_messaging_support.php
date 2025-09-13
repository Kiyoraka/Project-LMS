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
        Schema::table('messages', function (Blueprint $table) {
            // Add sender and recipient fields for two-way messaging
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            
            // Add index for better performance
            $table->index(['recipient_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
        });
        
        // Modify teacher_id to be nullable in a separate statement
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Remove the new columns
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropIndex(['recipient_id', 'created_at']);
            $table->dropIndex(['sender_id', 'created_at']);
            $table->dropColumn(['sender_id', 'recipient_id']);
            
            // Make teacher_id required again
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade')->change();
        });
    }
}; 