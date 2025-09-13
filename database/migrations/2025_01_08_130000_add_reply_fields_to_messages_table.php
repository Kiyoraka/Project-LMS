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
            $table->foreignId('parent_message_id')->nullable()->constrained('messages')->onDelete('cascade');
            $table->enum('message_type', ['original', 'reply'])->default('original');
            $table->json('reply_chain')->nullable(); // Store reply hierarchy for threading
            
            // Index for better performance on reply queries
            $table->index('parent_message_id');
            $table->index('message_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['parent_message_id']);
            $table->dropIndex(['parent_message_id']);
            $table->dropIndex(['message_type']);
            $table->dropColumn(['parent_message_id', 'message_type', 'reply_chain']);
        });
    }
}; 