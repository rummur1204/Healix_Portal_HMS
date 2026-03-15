<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('plan_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('trial_end_date')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'suspended'])->default('trial');
            $table->enum('payment_method', ['cash', 'bank', 'online', 'other'])->nullable();
            $table->enum('payment_status', ['paid', 'unpaid', 'partial'])->default('unpaid');
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('invoice_reference')->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_subscriptions');
    }
};