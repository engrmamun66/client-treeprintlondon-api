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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->tinyInteger('type_of_service')->default(3)->comment('1 = same day delivery, 2 = Next day delivery, 3 = Standard Delivery (3-7 Days)');
            $table->string('delivery_date')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->text('requirements')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = pending, 2 = processing, 3 = completed, 4 = cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
