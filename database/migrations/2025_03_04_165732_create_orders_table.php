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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->text('billing_address')->nullable();
            $table->unsignedBigInteger('delivery_type_id');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('order_status_id'); // Foreign key for order status
            $table->string('payment_status')->default('pending'); // e.g., 'completed', 'pending'
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        
            // Indexes
            $table->index('order_number', 'orders_order_number_index');
            $table->index('customer_email', 'orders_customer_email_index');
            $table->index('order_status_id', 'orders_order_status_id_index');
            $table->index('created_at', 'orders_created_at_index');
            $table->index('updated_at', 'orders_updated_at_index');
        
            // Foreign key constraints
            $table->foreign('delivery_type_id')
                  ->references('id')
                  ->on('delivery_types')
                  ->onDelete('restrict');
        
            $table->foreign('order_status_id')
                  ->references('id')
                  ->on('order_statuses')
                  ->onDelete('restrict'); // Prevent deletion if the status is in use
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
