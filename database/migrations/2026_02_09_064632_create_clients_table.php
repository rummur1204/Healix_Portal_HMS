<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_code')->unique();
            $table->string('organization_name');
            $table->unsignedBigInteger('organization_type_id')->nullable();
            $table->string('primary_contact_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone');
            $table->string('address_country')->nullable();
            $table->string('address_city')->nullable();
            $table->text('address_line')->nullable();
            $table->string('tax_reg_id')->nullable();
            $table->enum('preferred_contact_channel', ['email', 'sms', 'both'])->default('email');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->enum('status', ['pending', 'onboarding', 'active', 'suspended', 'rejected', 'churned'])->default('pending');
            $table->boolean('do_not_email')->default(false);
            $table->boolean('do_not_sms')->default(false);
            $table->boolean('do_not_market')->default(false);
            $table->timestamps();
            
            $table->foreign('organization_type_id')->references('id')->on('organization_types')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};