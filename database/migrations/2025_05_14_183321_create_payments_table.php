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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('change_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_intent')->unique();
            $table->string('charge')->index();
            $table->string('customer')->index();
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('amount_refunded')->default(0);
            $table->unsignedInteger('fee')->default(0);
            $table->string('status');
            $table->string('receipt_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
