<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('date_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_phone', 32);
            $table->date('confirmation_date');
            $table->string('confirmation_time', 40);
            $table->string('chosen_option', 40);
            $table->string('status', 30)->default('pending');
            $table->string('whatsapp_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_confirmations');
    }
};
