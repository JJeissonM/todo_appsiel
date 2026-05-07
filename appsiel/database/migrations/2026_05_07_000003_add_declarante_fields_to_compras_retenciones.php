<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDeclaranteFieldsToComprasRetenciones extends Migration
{
    public function up()
    {
        if (Schema::hasTable('compras_proveedores')) {
            Schema::table('compras_proveedores', function (Blueprint $table) {
                if (!Schema::hasColumn('compras_proveedores', 'declarante_renta')) {
                    $table->string('declarante_renta', 40)->default('declarante');
                }

                if (!Schema::hasColumn('compras_proveedores', 'retencion_fuente_concepto_default_id')) {
                    $table->unsignedInteger('retencion_fuente_concepto_default_id')->default(0);
                }
            });
        }

        if (Schema::hasTable('compras_retencion_fuente_conceptos_anuales')) {
            Schema::table('compras_retencion_fuente_conceptos_anuales', function (Blueprint $table) {
                if (!Schema::hasColumn('compras_retencion_fuente_conceptos_anuales', 'tipo_declarante')) {
                    $table->string('tipo_declarante', 40)->default('cualquiera')->after('tipo_item');
                }
            });
        }

        $this->crearCamposProveedor();
    }

    public function down()
    {
        if (Schema::hasTable('compras_proveedores')) {
            Schema::table('compras_proveedores', function (Blueprint $table) {
                if (Schema::hasColumn('compras_proveedores', 'retencion_fuente_concepto_default_id')) {
                    $table->dropColumn('retencion_fuente_concepto_default_id');
                }

                if (Schema::hasColumn('compras_proveedores', 'declarante_renta')) {
                    $table->dropColumn('declarante_renta');
                }
            });
        }

        if (Schema::hasTable('compras_retencion_fuente_conceptos_anuales')) {
            Schema::table('compras_retencion_fuente_conceptos_anuales', function (Blueprint $table) {
                if (Schema::hasColumn('compras_retencion_fuente_conceptos_anuales', 'tipo_declarante')) {
                    $table->dropColumn('tipo_declarante');
                }
            });
        }
    }

    protected function crearCamposProveedor()
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $campos = [
            [
                'name' => 'declarante_renta',
                'descripcion' => 'Declarante de renta',
                'tipo' => 'select',
                'opciones' => '{"declarante":"Declarante","no_declarante":"No declarante"}',
                'value' => 'declarante',
                'orden' => 21,
            ],
            [
                'name' => 'retencion_fuente_concepto_default_id',
                'descripcion' => 'Concepto retención por defecto',
                'tipo' => 'select',
                'opciones' => 'model_App\\Compras\\RetencionFuenteConceptoAnual',
                'value' => '0',
                'orden' => 22,
            ],
        ];

        $modelos = DB::table('sys_modelos')
            ->where('name_space', 'App\\Compras\\Proveedor')
            ->lists('id');

        foreach ($campos as $campo) {
            $campoId = DB::table('sys_campos')->where('name', $campo['name'])->value('id');
            if (!$campoId) {
                $campoId = DB::table('sys_campos')->insertGetId([
                    'descripcion' => $campo['descripcion'],
                    'tipo' => $campo['tipo'],
                    'name' => $campo['name'],
                    'opciones' => $campo['opciones'],
                    'value' => $campo['value'],
                    'atributos' => '',
                    'definicion' => '',
                    'requerido' => 0,
                    'editable' => 1,
                    'unico' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            foreach ($modelos as $modeloId) {
                $existe = DB::table('sys_modelo_tiene_campos')
                    ->where('core_modelo_id', $modeloId)
                    ->where('core_campo_id', $campoId)
                    ->exists();

                if (!$existe) {
                    DB::table('sys_modelo_tiene_campos')->insert([
                        'orden' => $campo['orden'],
                        'core_modelo_id' => $modeloId,
                        'core_campo_id' => $campoId,
                    ]);
                }
            }
        }
    }
}
