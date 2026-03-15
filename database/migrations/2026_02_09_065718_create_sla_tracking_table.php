<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->unique();
            $table->timestamp('first_response_due')->nullable();
            $table->timestamp('first_response_actual')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->timestamp('resolution_actual')->nullable();
            $table->boolean('is_breached')->default(false);
            $table->string('breach_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_tracking');
    }
};