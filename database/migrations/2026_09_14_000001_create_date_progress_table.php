<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('date_progress', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->unsignedTinyInteger('last_step')->default(1);
            $table->string('last_step_name', 40)->default('invite');
            $table->json('steps_completed')->nullable();
            $table->date('chosen_date')->nullable();
            $table->string('chosen_time', 40)->nullable();
            $table->string('chosen_option', 40)->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('abandoned')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_progress');
    }
};
