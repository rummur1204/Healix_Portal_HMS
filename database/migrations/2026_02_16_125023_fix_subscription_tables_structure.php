<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============ 1. FIX CLIENT_SUBSCRIPTIONS TABLE ============
        Schema::table('client_subscriptions', function (Blueprint $table) {
            // Add missing fields
            $table->string('subscription_id')->unique()->after('id');
            $table->date('renewal_date')->nullable()->after('end_date');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            $table->unsignedBigInteger('cancelled_by_user_id')->nullable()->after('cancellation_reason');
            $table->unsignedBigInteger('created_by_user_id')->after('cancelled_by_user_id');
            
            // Add foreign keys
            $table->foreign('cancelled_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });

        // ============ 2. DROP RENEWAL_REMINDERS TABLE ============
        Schema::dropIfExists('renewal_reminders');

        // ============ 3. DROP OLD SUBSCRIPTION_HISTORY TABLE ============
        Schema::dropIfExists('subscription_history');

        // ============ 4. CREATE NEW SUBSCRIPTION_HISTORIES TABLE ============
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('client_id');
            $table->string('action'); // created, renewed, upgraded, downgraded, cancelled, suspended, reactivated, status_changed
            
            // Track plan changes
            $table->unsignedBigInteger('old_plan_id')->nullable();
            $table->unsignedBigInteger('new_plan_id')->nullable();
            
            // Track status changes
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            
            // Track price changes
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            
            // Track date changes
            $table->date('old_end_date')->nullable();
            $table->date('new_end_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by_user_id');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('subscription_id')->references('id')->on('client_subscriptions')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('old_plan_id')->references('id')->on('subscription_plans')->onDelete('set null');
            $table->foreign('new_plan_id')->references('id')->on('subscription_plans')->onDelete('set null');
            $table->foreign('performed_by_user_id')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index(['subscription_id', 'created_at']);
            $table->index(['client_id', 'created_at']);
            $table->index('action');
        });

        // ============ 5. CREATE PAYMENT_RECORDS TABLE ============
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank', 'online', 'other']);
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by_user_id');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('subscription_id')->references('id')->on('client_subscriptions')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('recorded_by_user_id')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index(['subscription_id', 'payment_date']);
            $table->index(['client_id', 'payment_date']);
            $table->index('status');
        });

        // ============ 6. UPDATE SUBSCRIPTION_PLANS TABLE (OPTIONAL) ============
        // Only if you want to add slug - you said NO, so skipping
    }

    public function down(): void
    {
        // Reverse the changes if needed
        Schema::dropIfExists('payment_records');
        Schema::dropIfExists('subscription_histories');
        
        // Recreate renewal_reminders table (if you need to rollback)
        Schema::create('renewal_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->integer('reminder_days');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->foreign('subscription_id')->references('id')->on('client_subscriptions')->onDelete('cascade');
        });
        
        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by_user_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn([
                'subscription_id',
                'renewal_date',
                'cancelled_at',
                'cancellation_reason',
                'cancelled_by_user_id',
                'created_by_user_id'
            ]);
        });
    }
};