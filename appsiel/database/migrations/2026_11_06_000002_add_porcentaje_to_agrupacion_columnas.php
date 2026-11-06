<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPorcentajeToAgrupacionColumnas extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sga_agrupacion_columnas') || Schema::hasColumn('sga_agrupacion_columnas', 'porcentaje')) return;
        Schema::table('sga_agrupacion_columnas', function (Blueprint $t) {
            // Pesos iguales conservan exactamente el comportamiento previo.
            $t->decimal('porcentaje', 10, 4)->default(1);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('sga_agrupacion_columnas', 'porcentaje')) {
            throw new RuntimeException('No se puede eliminar el porcentaje individual sin alterar las ponderaciones configuradas.');
        }
    }
}
