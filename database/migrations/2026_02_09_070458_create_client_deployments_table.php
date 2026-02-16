<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_deployments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('version_id');
            $table->date('deployment_date');
            $table->unsignedBigInteger('deployed_by_user_id');
            $table->enum('deployment_type', ['new_install', 'upgrade', 'hotfix', 'rollback']);
            $table->enum('result', ['success', 'failed', 'partial'])->default('success');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('version_id')->references('id')->on('product_versions')->onDelete('restrict');
            $table->foreign('deployed_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_deployments');
    }
};