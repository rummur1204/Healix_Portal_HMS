<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_request_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->unique();
            $table->enum('business_value', ['low', 'medium', 'high']);
            $table->enum('estimated_effort', ['small', 'medium', 'large', 'xlarge']);
            $table->string('target_release')->nullable();
            $table->enum('approval_status', ['proposed', 'approved', 'planned', 'delivered', 'rejected'])->default('proposed');
            $table->string('external_link')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_request_details');
    }
};