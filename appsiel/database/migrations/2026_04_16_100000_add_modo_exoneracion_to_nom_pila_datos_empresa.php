<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddModoExoneracionToNomPilaDatosEmpresa extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_pila_datos_empresa')) {
            return;
        }

        $columnaAnterior = null;

        if (Schema::hasColumn('nom_pila_datos_empresa', 'porcentaje_afp_empresa')) {
            $columnaAnterior = 'porcentaje_afp_empresa';
        } elseif (Schema::hasColumn('nom_pila_datos_empresa', 'porcentaje_eps_empresa')) {
            $columnaAnterior = 'porcentaje_eps_empresa';
        } elseif (Schema::hasColumn('nom_pila_datos_empresa', 'porcentaje_caja_compensacion')) {
            $columnaAnterior = 'porcentaje_caja_compensacion';
        }

        Schema::table('nom_pila_datos_empresa', function (Blueprint $table) use ($columnaAnterior) {
            if (!Schema::hasColumn('nom_pila_datos_empresa', 'modo_exoneracion_aportes')) {
                $campo = $table->string('modo_exoneracion_aportes', 10)
                    ->default('auto');

                if ($columnaAnterior) {
                    $campo->after($columnaAnterior);
                }
            }
        });

        $this->registrarCampoEnCrud();
    }

    public function down()
    {
        if (!Schema::hasTable('nom_pila_datos_empresa')) {
            return;
        }

        Schema::table('nom_pila_datos_empresa', function (Blueprint $table) {
            if (Schema::hasColumn('nom_pila_datos_empresa', 'modo_exoneracion_aportes')) {
                $table->dropColumn('modo_exoneracion_aportes');
            }
        });

        $this->retirarCampoDelCrud();
    }

    protected function registrarCampoEnCrud()
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $campoId = DB::table('sys_campos')->where('name', 'modo_exoneracion_aportes')->value('id');

        if (!$campoId) {
            $campoId = DB::table('sys_campos')->insertGetId([
                'descripcion' => 'Modo exoneración aportes',
                'tipo' => 'select',
                'name' => 'modo_exoneracion_aportes',
                'opciones' => '{"auto":"Automático","si":"Sí aplica","no":"No aplica"}',
                'value' => 'auto',
                'atributos' => '',
                'definicion' => 'Define si la empresa aplica exoneración de salud, SENA e ICBF para trabajadores que no superen 10 SMMLV. Use No aplica para colegios, entidades sin derecho a exoneración o empresas que deban aportar completo.',
                'requerido' => 1,
                'editable' => 1,
                'unico' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $modeloId = DB::table('sys_modelos')
            ->where('name_space', 'App\\Nomina\\PilaDatosEmpresa')
            ->value('id');

        if (!$modeloId || !$campoId) {
            return;
        }

        $existeRelacion = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modeloId)
            ->where('core_campo_id', $campoId)
            ->exists();

        if (!$existeRelacion) {
            DB::table('sys_modelo_tiene_campos')->insert([
                'orden' => 29,
                'core_modelo_id' => $modeloId,
                'core_campo_id' => $campoId,
            ]);
        }
    }

    protected function retirarCampoDelCrud()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        $campoId = DB::table('sys_campos')->where('name', 'modo_exoneracion_aportes')->value('id');

        if (!$campoId) {
            return;
        }

        if (Schema::hasTable('sys_modelo_tiene_campos')) {
            DB::table('sys_modelo_tiene_campos')
                ->where('core_campo_id', $campoId)
                ->delete();
        }

        DB::table('sys_campos')
            ->where('id', $campoId)
            ->delete();
    }
}
