<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTesoMediosRecaudoDestinosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('teso_medios_recaudo_destinos')) {
            return;
        }

        Schema::create('teso_medios_recaudo_destinos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teso_medio_recaudo_id');
            $table->unsignedInteger('teso_caja_id')->nullable();
            $table->unsignedInteger('teso_cuenta_bancaria_id')->nullable();
            $table->string('estado')->default('Activo');
            $table->timestamps();

            $table->foreign('teso_medio_recaudo_id', 'fk_teso_mr_destinos_medio')
                ->references('id')
                ->on('teso_medios_recaudo')
                ->onDelete('cascade');

            $table->foreign('teso_caja_id', 'fk_teso_mr_destinos_caja')
                ->references('id')
                ->on('teso_cajas')
                ->onDelete('cascade');

            $table->foreign('teso_cuenta_bancaria_id', 'fk_teso_mr_destinos_cuenta')
                ->references('id')
                ->on('teso_cuentas_bancarias')
                ->onDelete('cascade');

            $table->unique(
                ['teso_medio_recaudo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id'],
                'teso_mr_destinos_unique'
            );
        });

        $this->insertar_relaciones_por_defecto();
    }

    public function down()
    {
        if (!Schema::hasTable('teso_medios_recaudo_destinos')) {
            return;
        }

        Schema::table('teso_medios_recaudo_destinos', function (Blueprint $table) {
            $table->dropUnique('teso_mr_destinos_unique');
            $table->dropForeign('fk_teso_mr_destinos_medio');
            $table->dropForeign('fk_teso_mr_destinos_caja');
            $table->dropForeign('fk_teso_mr_destinos_cuenta');
        });

        Schema::dropIfExists('teso_medios_recaudo_destinos');
    }

    protected function insertar_relaciones_por_defecto()
    {
        if (!Schema::hasTable('teso_medios_recaudo_destinos')) {
            return;
        }

        $medios = DB::table('teso_medios_recaudo')
            ->select('id', 'comportamiento')
            ->get();

        if ($medios->isEmpty()) {
            return;
        }

        $cajas = DB::table('teso_cajas')
            ->where('estado', 'Activo')
            ->pluck('id')
            ->toArray();

        $cuentas = DB::table('teso_cuentas_bancarias')
            ->where('estado', 'Activo')
            ->pluck('id')
            ->toArray();

        $ahora = date('Y-m-d H:i:s');
        $registros = [];

        foreach ($medios as $medio) {
            if ($medio->comportamiento === 'Tarjeta bancaria') {
                foreach ($cuentas as $cuentaId) {
                    $registros[] = [
                        'teso_medio_recaudo_id' => $medio->id,
                        'teso_caja_id' => null,
                        'teso_cuenta_bancaria_id' => $cuentaId,
                        'estado' => 'Activo',
                        'created_at' => $ahora,
                        'updated_at' => $ahora
                    ];
                }

                continue;
            }

            foreach ($cajas as $cajaId) {
                $registros[] = [
                    'teso_medio_recaudo_id' => $medio->id,
                    'teso_caja_id' => $cajaId,
                    'teso_cuenta_bancaria_id' => null,
                    'estado' => 'Activo',
                    'created_at' => $ahora,
                    'updated_at' => $ahora
                ];
            }
        }

        if (!empty($registros)) {
            DB::table('teso_medios_recaudo_destinos')->insert($registros);
        }
    }
}
