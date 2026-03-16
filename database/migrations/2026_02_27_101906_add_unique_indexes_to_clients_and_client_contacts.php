<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    

public function up()
{
    // Schema::table('clients', function (Blueprint $table) {
    //     $table->unique('organization_name');
    //     // $table->unique('client_code');
    // });

    Schema::table('client_contacts', function (Blueprint $table) {
        // $table->unique('email');
        $table->unique('phone');
    });
}

public function down()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->dropUnique(['organization_name']);
        $table->dropUnique(['client_code']);
    });

    Schema::table('client_contacts', function (Blueprint $table) {
        $table->dropUnique(['email']);
        $table->dropUnique(['phone']);
    });
}

    /**
     * Reverse the migrations.
     */
    
};
