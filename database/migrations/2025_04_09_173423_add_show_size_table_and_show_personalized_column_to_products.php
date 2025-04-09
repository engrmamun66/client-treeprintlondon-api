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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_size_table')
                 ->default(1)
                 ->after('status'); // Specify after which column
            
            $table->boolean('show_personalized')
                 ->default(0)
                 ->after('show_size_table');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['show_size_table', 'show_personalized']);
        });
    }
};
