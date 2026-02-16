<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('event_type', [
                'profile_created', 'profile_updated', 'status_changed', 
                'subscription_added', 'subscription_updated', 'ticket_created',
                'ticket_updated', 'version_deployed', 'communication_sent',
                'note_added', 'task_added', 'task_completed', 'document_uploaded'
            ]);
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_timeline_events');
    }
};