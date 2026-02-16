<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('template_id');
            $table->enum('send_type', ['immediate', 'scheduled']);
            $table->timestamp('scheduled_time')->nullable();
            $table->json('target_segment')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'cancelled', 'failed'])->default('draft');
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('communication_templates')->onDelete('restrict');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_campaigns');
    }
};