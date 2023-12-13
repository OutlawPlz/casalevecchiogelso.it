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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->ulid('uid')->unique();
            $table->tinyText('first_name');
            $table->tinyText('last_name');
            $table->tinyText('email')->index();
            $table->tinyText('phone');
            $table->date('check_in')->index();
            $table->date('check_out')->index();
            $table->tinyText('preparation_time');
            $table->tinyInteger('guest_count')->unsigned();
            $table->string('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
