<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('client_tasks', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('client_id');

    $table->string('title');
     $table->text('description')->nullable();

    $table->date('due_date')->nullable();

    $table->enum('status', ['open', 'done'])->default('open');

    $table->unsignedBigInteger('assigned_to_user_id')->nullable();

    $table->timestamp('reminder_at')->nullable();

    $table->unsignedBigInteger('created_by_user_id');

    $table->timestamps();

    $table->foreign('client_id')
        ->references('id')->on('clients')
        ->onDelete('cascade');

    $table->foreign('assigned_to_user_id')
        ->references('id')->on('users')
        ->onDelete('set null');

    $table->foreign('created_by_user_id')
        ->references('id')->on('users')
        ->onDelete('restrict');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tasks');
    }
};