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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // Foreign key to the orders table
            $table->unsignedBigInteger('product_id'); // Foreign key to the products table
            $table->unsignedBigInteger('product_size_id'); // Foreign key to the product_sizes table
            $table->unsignedBigInteger('product_color_id'); // Foreign key to the product_colors table
            $table->decimal('unit_price', 10, 2); // Price per unit at the time of purchase
            $table->decimal('discounted_unit_price', 10, 2); //Discounted Price per unit at the time of purchase
            $table->integer('quantity')->default(1); // Quantity of the product, default to 1
            $table->decimal('total_price', 10, 2); // Total price for this item (discounted_unit_price * quantity)
            $table->decimal('discount', 10, 2)->nullable(); // Discount applied to the item
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft deletes

            // Indexes
            $table->index('order_id', 'order_items_order_id_index');
            $table->index('product_id', 'order_items_product_id_index');
            $table->index('product_size_id', 'order_items_product_size_id_index');
            $table->index('created_at', 'order_items_created_at_index');
            $table->index('updated_at', 'order_items_updated_at_index');

            // Unique constraint to prevent duplicate entries
            $table->unique(['order_id', 'product_id', 'product_size_id'], 'order_items_unique');

            // Foreign key constraints
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade'); // Cascade delete if the order is deleted

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade'); // Cascade delete if the product is deleted

            $table->foreign('product_size_id')
                  ->references('id')
                  ->on('product_sizes')
                  ->onDelete('set null'); // Set to null if the product size is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
