<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique(
                ['organization_name', 'primary_contact_email'],
                'clients_org_email_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_org_email_unique');
        });
    }
};
