<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_technical_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->unique();
            $table->boolean('remote_access_enabled')->default(false);
            $table->string('anydesk_address')->nullable();
            $table->string('anydesk_password')->nullable();
            $table->date('server_setup_date')->nullable();
            $table->date('server_activation_date')->nullable();
            $table->date('last_activation_update_date')->nullable();
            $table->date('activation_expiry_date')->nullable();
            $table->integer('windows_rearm_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('subnet_mask')->nullable();
            $table->string('gateway')->nullable();
            $table->string('primary_dns')->nullable();
            $table->string('secondary_dns')->nullable();
            $table->boolean('internet_available')->default(false);
            $table->boolean('firewall_in_use')->default(false);
            $table->string('firewall_brand')->nullable();
            $table->string('router_model')->nullable();
            $table->string('router_admin_password')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->text('disk_configuration')->nullable();
            $table->decimal('available_free_space_gb', 10, 2)->nullable();
            $table->boolean('backup_configured')->default(false);
            $table->enum('backup_type', ['local', 'cloud', 'hybrid'])->nullable();
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly', 'manual', 'automated'])->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_technical_info');
    }
};