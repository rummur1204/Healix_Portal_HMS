<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_types', function (Blueprint $table) {
            // Add color column if it doesn't exist
            if (!Schema::hasColumn('organization_types', 'color')) {
                $table->string('color')->default('#14b8a6')->after('description');
            }
            
            // Add icon column if it doesn't exist
            if (!Schema::hasColumn('organization_types', 'icon')) {
                $table->string('icon')->default('building')->after('color');
            }
            
            // Add display_order column if it doesn't exist
            if (!Schema::hasColumn('organization_types', 'display_order')) {
                $table->integer('display_order')->default(0)->after('icon');
            }
            
            // Add created_by_user_id column if it doesn't exist
            if (!Schema::hasColumn('organization_types', 'created_by_user_id')) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('display_order');
                $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organization_types', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn([
                'color',
                'icon', 
                'display_order',
                'created_by_user_id'
            ]);
        });
    }
};