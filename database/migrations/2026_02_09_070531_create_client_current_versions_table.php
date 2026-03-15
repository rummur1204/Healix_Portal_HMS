<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_current_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->unique();
            $table->unsignedBigInteger('version_id');
            $table->string('current_app_version');
            $table->string('current_db_version');
            $table->unsignedBigInteger('last_deployment_id')->nullable();
            $table->date('last_deployment_date')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('version_id')->references('id')->on('product_versions')->onDelete('restrict');
            $table->foreign('last_deployment_id')->references('id')->on('client_deployments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_current_versions');
    }
};