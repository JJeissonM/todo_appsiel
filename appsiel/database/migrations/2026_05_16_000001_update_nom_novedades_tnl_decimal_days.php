<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateNomNovedadesTnlDecimalDays extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_novedades_tnl')) {
            return;
        }

        DB::statement("SET SESSION sql_mode=''");
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_dias_tnl` DECIMAL(12,6) NOT NULL');
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_horas_tnl` DECIMAL(12,3) NOT NULL');
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_dias_amortizados` DECIMAL(12,6) NOT NULL');
    }

    public function down()
    {
        if (!Schema::hasTable('nom_novedades_tnl')) {
            return;
        }

        DB::statement("SET SESSION sql_mode=''");
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_dias_tnl` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_horas_tnl` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `nom_novedades_tnl` MODIFY `cantidad_dias_amortizados` INT(11) NOT NULL');
    }
}
