<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // First, check if the subscription_histories table exists
        if (Schema::hasTable('subscription_histories')) {
            
            // Check if subscription_id column exists and is nullable
            Schema::table('subscription_histories', function (Blueprint $table) {
                // Make sure the column is nullable and has the correct type
                $table->unsignedBigInteger('subscription_id')->nullable()->change();
            });

            // Drop any existing foreign keys with common names
            try {
                DB::statement('ALTER TABLE `subscription_histories` DROP FOREIGN KEY `subscription_histories_subscription_id_foreign`');
            } catch (\Exception $e) {
                // Key doesn't exist, continue
            }

            try {
                DB::statement('ALTER TABLE `subscription_histories` DROP FOREIGN KEY `sh_subscription_id_foreign`');
            } catch (\Exception $e) {
                // Key doesn't exist, continue
            }

            try {
                DB::statement('ALTER TABLE `subscription_histories` DROP FOREIGN KEY `subscription_histories_subscription_id_fk`');
            } catch (\Exception $e) {
                // Key doesn't exist, continue
            }

            // Now add the foreign key with a unique name
            if (Schema::hasTable('client_subscriptions')) {
                DB::statement('ALTER TABLE `subscription_histories` ADD CONSTRAINT `sh_sub_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `client_subscriptions` (`id`) ON DELETE SET NULL');
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (Schema::hasTable('subscription_histories')) {
            // Drop the foreign key we added
            try {
                DB::statement('ALTER TABLE `subscription_histories` DROP FOREIGN KEY `sh_sub_id_fk`');
            } catch (\Exception $e) {
                // Key might not exist
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};