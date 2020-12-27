<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class InvFabricacion extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'inv_bodega_id', 'documento_soporte', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'hora_inicio', 'hora_finalizacion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Bodega', 'Tercero', 'Detalle', 'Estado'];

    public $urls_acciones = '{"create":"inventarios/create","store":"inventarios","update":"inventarios/id_fila","show":"inventarios/id_fila","imprimir":"transaccion_print/id_fila"}';

    public static function consultar_registros($nro_registros)
    {
        $core_tipo_transaccion_id = 4;

        return InvFabricacion::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                'inv_bodegas.descripcion AS campo3',
                DB::raw('core_terceros.descripcion AS campo4'),
                'inv_doc_encabezados.descripcion AS campo5',
                'inv_doc_encabezados.estado AS campo6',
                'inv_doc_encabezados.id AS campo7'
            )
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
}
