<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddGentilicioToCorePaises extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('core_paises') || Schema::hasColumn('core_paises', 'gentilicio')) {
            return;
        }

        Schema::table('core_paises', function (Blueprint $table) {
            $table->string('gentilicio', 100)->nullable()->after('descripcion');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('core_paises') || !Schema::hasColumn('core_paises', 'gentilicio')) {
            return;
        }

        Schema::table('core_paises', function (Blueprint $table) {
            $table->dropColumn('gentilicio');
        });
    }
}
