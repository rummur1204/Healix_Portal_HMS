<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('report_name');
            $table->enum('format', ['csv', 'excel', 'pdf']);
            $table->json('filters_used')->nullable();
            $table->string('file_path');
            $table->integer('record_count')->default(0);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('report_templates')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_executions');
    }
};