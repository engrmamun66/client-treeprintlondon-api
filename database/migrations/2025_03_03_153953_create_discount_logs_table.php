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
        Schema::create('discount_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'category' or 'all'
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('discount', 5, 2); // Percentage (e.g., 15.00 for 15%)
            $table->text('applied_to')->nullable(); // JSON or text field to store specific product/category IDs
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_logs');
    }
};
