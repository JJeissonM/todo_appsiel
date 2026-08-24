<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFechaToCalificacionesEncabezadosCrud extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Calificaciones\\EncabezadoCalificacion')
            ->value('id');

        if ($modeloId == 0) {
            return;
        }

        $campoId = (int)DB::table('sys_campos')
            ->where('name', 'fecha')
            ->where('tipo', 'fecha')
            ->where('descripcion', 'Fecha actividad encabezado')
            ->orderBy('id')
            ->value('id');

        if ($campoId == 0) {
            $campoId = DB::table('sys_campos')->insertGetId($this->onlyExistingColumns('sys_campos', [
                'descripcion' => 'Fecha actividad encabezado',
                'tipo' => 'fecha',
                'name' => 'fecha',
                'opciones' => '',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]));
        }

        $camposFechaAnteriores = DB::table('sys_modelo_tiene_campos as mc')
            ->join('sys_campos as c', 'c.id', '=', 'mc.core_campo_id')
            ->where('mc.core_modelo_id', $modeloId)
            ->where('c.name', 'fecha')
            ->where('mc.core_campo_id', '<>', $campoId)
            ->lists('mc.core_campo_id');

        if (count($camposFechaAnteriores) > 0) {
            DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modeloId)
                ->whereIn('core_campo_id', $camposFechaAnteriores)
                ->delete();
        }

        $existeRelacion = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modeloId)
            ->where('core_campo_id', $campoId)
            ->exists();

        if (!$existeRelacion) {
            DB::table('sys_modelo_tiene_campos')->insert($this->onlyExistingColumns('sys_modelo_tiene_campos', [
                'core_modelo_id' => $modeloId,
                'core_campo_id' => $campoId,
                'orden' => 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]));
        }

        if (Schema::hasTable('sga_calificaciones_encabezados') && Schema::hasColumn('sga_calificaciones_encabezados', 'fecha')) {
            Schema::table('sga_calificaciones_encabezados', function ($table) {
                $table->date('fecha')->nullable()->change();
            });

            $registros = DB::table('sga_calificaciones_encabezados')
                ->where('fecha', '0000-00-00')
                ->select('id', 'curso_id', 'asignatura_id', 'created_at')
                ->get();

            foreach ($registros as $registro) {
                $fecha = null;

                if (!is_null($registro->curso_id) || !is_null($registro->asignatura_id)) {
                    $fecha = empty($registro->created_at)
                        ? date('Y-m-d')
                        : substr((string)$registro->created_at, 0, 10);
                }

                DB::table('sga_calificaciones_encabezados')
                    ->where('id', $registro->id)
                    ->update(['fecha' => $fecha]);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Calificaciones\\EncabezadoCalificacion')
            ->value('id');
        $campoId = (int)DB::table('sys_campos')
            ->where('name', 'fecha')
            ->where('tipo', 'fecha')
            ->where('descripcion', 'Fecha actividad encabezado')
            ->orderBy('id')
            ->value('id');

        if ($modeloId > 0 && $campoId > 0) {
            DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modeloId)
                ->where('core_campo_id', $campoId)
                ->delete();
        }
    }

    protected function onlyExistingColumns($table, array $data)
    {
        $resultado = [];

        foreach ($data as $columna => $valor) {
            if (Schema::hasColumn($table, $columna)) {
                $resultado[$columna] = $valor;
            }
        }

        return $resultado;
    }
}
