<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_name');
            $table->date('release_date');
            $table->enum('environment', ['production', 'staging', 'development']);
            $table->text('release_notes')->nullable();
            $table->string('build_id')->nullable();
            $table->string('app_version')->nullable();
            $table->string('db_schema_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_versions');
    }
};