<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApmPrintStatusesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('apm_print_statuses')) {
            return;
        }

        Schema::create('apm_print_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->unique();
            $table->string('description', 120);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('apm_print_statuses');
    }
}
