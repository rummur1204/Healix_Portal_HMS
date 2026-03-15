<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('sender_user_id');
            $table->enum('channel', ['email', 'sms']);
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed', 'bounced']);
            $table->integer('recipients_count')->default(1);
            $table->timestamp('sent_at')->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('communication_campaigns')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('communication_templates')->onDelete('set null');
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};