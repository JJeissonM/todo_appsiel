<?php

namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrazabilidadRetenciones
{
    public static function tablasDocumentos()
    {
        return ['compras_doc_encabezados', 'teso_doc_encabezados', 'vtas_doc_encabezados', 'contab_doc_encabezados', 'nom_doc_encabezados'];
    }

    public function resolver(array $registro)
    {
        $candidatos = [];
        foreach (static::tablasDocumentos() as $tabla) {
            if (!Schema::hasTable($tabla)) { continue; }
            $query = DB::table($tabla);
            foreach (['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo'] as $campo) {
                if (empty($registro[$campo])) { continue 2; }
                $query->where($campo, $registro[$campo]);
            }
            if (!empty($registro['core_empresa_id'])) {
                $query->where('core_empresa_id', $registro['core_empresa_id']);
            }
            if (!empty($registro['compras_doc_registro_id'])) {
                if ($tabla !== 'compras_doc_encabezados' || !Schema::hasTable('compras_doc_registros')) { continue; }
                $query->whereIn('id', function ($lineas) use ($registro) {
                    $lineas->select('compras_doc_encabezado_id')->from('compras_doc_registros')
                        ->where('id', $registro['compras_doc_registro_id']);
                });
            }
            foreach ($query->limit(2)->get() as $documento) {
                $candidatos[] = [$tabla, $documento];
            }
        }
        if (count($candidatos) !== 1) { return []; }
        list($tabla, $documento) = $candidatos[0];
        $terceroId = isset($documento->core_tercero_id) ? $documento->core_tercero_id : (isset($registro['core_tercero_id']) ? $registro['core_tercero_id'] : null);
        $persona = DB::table('core_terceros')->where('id', $terceroId)
            ->where('core_empresa_id', $documento->core_empresa_id)->value('tipo');
        return [
            'core_empresa_id' => $documento->core_empresa_id, 'fecha' => $documento->fecha,
            'core_tercero_id' => $terceroId,
            'tipo_persona_retenida' => ConceptosDeclaracion::persona($persona),
            'documento_tabla' => $tabla, 'documento_id' => $documento->id
        ];
    }

    public function reconstruir()
    {
        $resultado = ['recuperados' => 0, 'pendientes' => 0];
        $ultimoId = 0;
        do {
            $registros = DB::table('contab_registros_retenciones')->whereNull('documento_id')
                ->where('id', '>', $ultimoId)->orderBy('id')->limit(200)->get();
            foreach ($registros as $registro) {
                $ultimoId = $registro->id;
                $datos = $this->resolver((array)$registro);
                if (!$datos) { $resultado['pendientes']++; continue; }
                DB::table('contab_registros_retenciones')->where('id', $registro->id)->update($datos);
                $resultado['recuperados']++;
            }
        } while (count($registros) === 200);
        return $resultado;
    }
}
