<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOperadorPilaTerceroToNomPilaDatosEmpresa extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_pila_datos_empresa')) {
            return;
        }

        $columnaAnterior = null;

        if (Schema::hasColumn('nom_pila_datos_empresa', 'rep_legal_core_tercero_id')) {
            $columnaAnterior = 'rep_legal_core_tercero_id';
        } elseif (Schema::hasColumn('nom_pila_datos_empresa', 'actividad_economica_ciiu')) {
            $columnaAnterior = 'actividad_economica_ciiu';
        } elseif (Schema::hasColumn('nom_pila_datos_empresa', 'administradora_riesgos_laborales_id')) {
            $columnaAnterior = 'administradora_riesgos_laborales_id';
        }

        Schema::table('nom_pila_datos_empresa', function (Blueprint $table) use ($columnaAnterior) {
            if (!Schema::hasColumn('nom_pila_datos_empresa', 'operador_pila_core_tercero_id')) {
                $campo = $table->integer('operador_pila_core_tercero_id')
                    ->unsigned()
                    ->default(0);

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
            if (Schema::hasColumn('nom_pila_datos_empresa', 'operador_pila_core_tercero_id')) {
                $table->dropColumn('operador_pila_core_tercero_id');
            }
        });

        $this->retirarCampoDelCrud();
    }

    protected function registrarCampoEnCrud()
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $campoId = DB::table('sys_campos')->where('name', 'operador_pila_core_tercero_id')->value('id');

        if (!$campoId) {
            $campoId = DB::table('sys_campos')->insertGetId([
                'descripcion' => 'Operador PILA CxP',
                'tipo' => 'select',
                'name' => 'operador_pila_core_tercero_id',
                'opciones' => 'model_App\\Core\\Tercero',
                'value' => '0',
                'atributos' => '{"class":"combobox"}',
                'definicion' => 'Tercero al cual se generará la cuenta por pagar de la PILA. Si se deja en cero, la CxP se genera a nombre de cada entidad.',
                'requerido' => 0,
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
                'orden' => 46,
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

        $campoId = DB::table('sys_campos')->where('name', 'operador_pila_core_tercero_id')->value('id');

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
