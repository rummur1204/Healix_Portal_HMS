<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_status_histories', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('old_status', ['pending', 'onboarding', 'active', 'suspended', 'rejected', 'churned'])->nullable();
            $table->enum('new_status', ['pending', 'onboarding', 'active', 'suspended', 'rejected', 'churned']);
            $table->text('change_reason');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_status_histories');
    }
};