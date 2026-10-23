<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateComprasSyncLogTable extends Migration
{
    public function up()
    {
        // MySQL puede dejar la tabla creada si falla una FK. No borrar sus datos.
        if (!Schema::hasTable('compras_sync_log')) {
            Schema::create('compras_sync_log', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('cufe', 100);
                $table->unsignedInteger('core_empresa_id');
                $table->unsignedInteger('compras_doc_encabezado_id')->nullable();
                $table->string('estado', 20)->default('procesado');
                $table->text('mensaje_error')->nullable();
                $table->string('creado_por', 100)->nullable();
                $table->timestamps();
                $table->unique(['cufe', 'core_empresa_id']);
            });
        }

        $this->agregarRelacion('core_empresa_id', 'core_empresas');
        $this->agregarRelacion('compras_doc_encabezado_id', 'compras_doc_encabezados', true);
    }

    protected function agregarRelacion($columna, $tablaReferenciada, $anularAlEliminar = false)
    {
        // Algunas instalaciones no tienen el módulo de compras.
        if (!Schema::hasTable($tablaReferenciada)) {
            return;
        }

        $conexion = Schema::getConnection();
        $tabla = $conexion->getTablePrefix() . 'compras_sync_log';
        $existe = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $conexion->getDatabaseName())
            ->where('TABLE_NAME', $tabla)
            ->where('COLUMN_NAME', $columna)
            ->where('REFERENCED_TABLE_NAME', $conexion->getTablePrefix() . $tablaReferenciada)
            ->exists();

        if (!$existe) {
            Schema::table('compras_sync_log', function (Blueprint $table) use ($columna, $tablaReferenciada, $anularAlEliminar) {
                $foreign = $table->foreign($columna)->references('id')->on($tablaReferenciada);
                if ($anularAlEliminar) {
                    $foreign->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('compras_sync_log');
    }
}