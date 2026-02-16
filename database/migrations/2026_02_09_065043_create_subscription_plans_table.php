<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly', 'custom']);
            $table->decimal('price', 10, 2);
            $table->json('modules_included')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_branches')->nullable();
            $table->enum('support_level', ['standard', 'premium'])->default('standard');
            $table->text('notes')->nullable();
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();
            
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};