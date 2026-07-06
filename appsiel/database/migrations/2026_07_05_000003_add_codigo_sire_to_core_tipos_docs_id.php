<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCodigoSireToCoreTiposDocsId extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('core_tipos_docs_id')) {
            return;
        }

        if (!Schema::hasColumn('core_tipos_docs_id', 'codigo_sire')) {
            Schema::table('core_tipos_docs_id', function (Blueprint $table) {
                $table->integer('codigo_sire')->unsigned()->nullable()->after('abreviatura');
                $table->index('codigo_sire', 'core_tipos_docs_id_codigo_sire_idx');
            });
        }

        $this->upsertTipoDocumento(41, 'Pasaporte', 'PAS', 3);
        $this->upsertTipoDocumento(22, 'Cedula de extranjeria', 'CE', 5);
        $this->upsertTipoDocumento(null, 'Carne diplomatico', 'CD', 46);
        $this->upsertTipoDocumento(42, 'Documento de identificacion extranjero', 'DNI', 10);
        $this->upsertTipoDocumento(null, 'Permiso por proteccion temporal', 'PPT', 52);
    }

    public function down()
    {
        if (!Schema::hasTable('core_tipos_docs_id') || !Schema::hasColumn('core_tipos_docs_id', 'codigo_sire')) {
            return;
        }

        if ($this->indexExists('core_tipos_docs_id', 'core_tipos_docs_id_codigo_sire_idx')) {
            Schema::table('core_tipos_docs_id', function (Blueprint $table) {
                $table->dropIndex('core_tipos_docs_id_codigo_sire_idx');
            });
        }

        Schema::table('core_tipos_docs_id', function (Blueprint $table) {
            $table->dropColumn('codigo_sire');
        });
    }

    private function upsertTipoDocumento($id, $descripcion, $abreviatura, $codigoSire)
    {
        $query = DB::table('core_tipos_docs_id');
        $registro = null;

        if (!is_null($id)) {
            $registro = $query->where('id', $id)->first();
        }

        if (is_null($registro)) {
            $registro = DB::table('core_tipos_docs_id')
                ->where('abreviatura', $abreviatura)
                ->orWhere('codigo_sire', $codigoSire)
                ->first();
        }

        if (is_null($registro)) {
            $data = array(
                'descripcion' => $descripcion,
                'abreviatura' => $abreviatura,
                'codigo_sire' => $codigoSire
            );

            if (!is_null($id) && !$this->documentIdExists($id)) {
                $data['id'] = $id;
            } else {
                $data['id'] = $this->nextDocumentId();
            }

            $data = $this->addTimestamps($data, true);
            DB::table('core_tipos_docs_id')->insert($data);
            return;
        }

        $registro = $this->moveZeroIdRecord($registro);

        $data = $this->addTimestamps(array(
            'codigo_sire' => $codigoSire
        ), false);

        DB::table('core_tipos_docs_id')
            ->where('id', $registro->id)
            ->update($data);
    }

    private function documentIdExists($id)
    {
        return DB::table('core_tipos_docs_id')->where('id', $id)->count() > 0;
    }

    private function nextDocumentId()
    {
        $id = (int) DB::table('core_tipos_docs_id')->max('id') + 1;

        while ($this->documentIdExists($id)) {
            $id++;
        }

        return $id;
    }

    private function moveZeroIdRecord($registro)
    {
        if ((int) $registro->id !== 0) {
            return $registro;
        }

        $newId = $this->nextDocumentId();

        DB::table('core_tipos_docs_id')
            ->where('id', 0)
            ->where('abreviatura', $registro->abreviatura)
            ->update(array('id' => $newId));

        return DB::table('core_tipos_docs_id')->where('id', $newId)->first();
    }

    private function addTimestamps($data, $isNew)
    {
        $now = date('Y-m-d H:i:s');

        if ($isNew && Schema::hasColumn('core_tipos_docs_id', 'created_at')) {
            $data['created_at'] = $now;
        }

        if (Schema::hasColumn('core_tipos_docs_id', 'updated_at')) {
            $data['updated_at'] = $now;
        }

        return $data;
    }

    private function indexExists($table, $index)
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count() > 0;
    }
}
