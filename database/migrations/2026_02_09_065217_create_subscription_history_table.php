<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->enum('change_type', ['upgrade', 'downgrade', 'renewal', 'status_change', 'payment_update']);
            $table->string('changed_field');
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('change_by_user_id');
            $table->timestamps();
            
            $table->foreign('subscription_id')->references('id')->on('client_subscriptions')->onDelete('cascade');
            $table->foreign('change_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_history');
    }
};