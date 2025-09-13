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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email'); // Options: super_admin, teacher, student
            $table->string('profile_picture')->nullable()->after('role');
            $table->text('bio')->nullable()->after('profile_picture');
            $table->string('phone')->nullable()->after('bio');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'profile_picture', 'bio', 'phone', 'is_active']);
        });
    }
};