<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnToTablePwPaginas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_paginas', function(Blueprint $table)
        {
            $table->dropColumn(['logo','email_interno','meta_description']);
            $table->string('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_paginas', function(Blueprint $table)
        {
            $table->string('logo',100);
            $table->string('email_interno',250);
            $table->text('meta_description',100);
            $table->string('favicon',100);
            $table->dropColumn('slug');
        });
    }
}
