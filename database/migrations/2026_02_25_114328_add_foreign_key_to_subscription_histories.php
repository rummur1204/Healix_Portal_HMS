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
        // First, make sure the column is nullable
        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id')->nullable()->change();
        });
        
        // Add the foreign key constraint with ON DELETE SET NULL
        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('client_subscriptions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key if it exists
        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
        });
    }
};