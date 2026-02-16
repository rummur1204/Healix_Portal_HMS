<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('sla_name');
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->integer('first_response_hrs');
            $table->integer('resolution_hrs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['sla_name', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_configurations');
    }
};