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
        Schema::table('orders', function (Blueprint $table) {
            // Add coupon_id foreign key
            $table->unsignedBigInteger('coupon_id')
                  ->nullable()
                  ->after('order_status_id');
                  
            // Add discount amount field
            $table->decimal('discount_amount', 10, 2)
                  ->default(0)
                  ->after('shipping_cost');
                  
            // Add original total field (before discount)
            $table->decimal('original_total', 10, 2)
                  ->default(0) 
                  ->after('total');
                  
            // Add foreign key constraint for coupon
            $table->foreign('coupon_id')
                  ->references('id')
                  ->on('coupons')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['coupon_id']);
            
            // Then drop the columns
            $table->dropColumn([
                'coupon_id',
                'discount_amount',
                'original_total'
            ]);
        });
    }
};