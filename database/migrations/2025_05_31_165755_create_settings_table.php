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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index()->comment('Settings category (general, appearance, email, etc.)');
            $table->string('key')->index()->comment('Setting identifier');
            $table->text('value')->nullable()->comment('Setting value');
            $table->string('value_type')->default('string')->comment('Type of value (string, boolean, integer, json, etc.)');
            $table->string('description')->nullable()->comment('Setting description');
            $table->timestamps();
            
            // Make group+key combination unique
            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
