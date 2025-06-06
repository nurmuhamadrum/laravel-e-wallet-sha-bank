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
        Schema::create('operator_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the operator card');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Status of the operator card');
            $table->string('thumbnail')->nullable()->comment('Thumbnail image for the operator card');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_cards');
    }
};
