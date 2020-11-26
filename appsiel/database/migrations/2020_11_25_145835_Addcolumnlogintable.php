<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnlogintable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_logins', function (Blueprint $table) {
            $table->string('ondas', 5)->after('imagen');
            $table->string('tipo_fondo', 20)->after('ondas');
            $table->string('fondo')->after('tipo_fondo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_logins', function (Blueprint $table) {
            $table->dropColumn('ondas');
            $table->dropColumn('tipo_fondo');
            $table->dropColumn('fondo');
        });
    }
}
