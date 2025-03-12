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
            $table->string('payment_id'); // PayPal transaction ID
            $table->string('payer_id'); // PayPal payer ID
            $table->string('order_id'); // Your custom order ID
            $table->string('amount');
            $table->string('currency');
            $table->string('status'); // e.g., 'completed', 'failed'
            $table->string('payment_method'); // e.g., 'Credit card', 'Paypal'
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
