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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip_address', 45)->index(); // IPv6 support
            $table->string('type')->default('login')->index(); // 'login' or 'registration'
            $table->integer('attempts')->default(1);
            $table->timestamp('last_attempt_at')->useCurrent();
            $table->timestamp('locked_until')->nullable(); // When lockout expires
            $table->timestamps();
            
            // Composite index for efficient lookups
            $table->index(['email', 'ip_address', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
