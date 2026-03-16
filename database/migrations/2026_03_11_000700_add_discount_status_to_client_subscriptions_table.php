<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('client_subscriptions', 'discount_status')) {
                $table->string('discount_status')->default('none')->after('discount');
            }
        });
    }

    public function down()
    {
        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->dropColumn('discount_status');
        });
    }
};