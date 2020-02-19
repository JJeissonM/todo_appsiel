<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class NotaCreditoDirecta extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

	public $encabezado_tabla = ['Nota Crédito', 'Fecha', 'Cliente', 'Detalle', 'Valor total', 'Estado', 'Acción'];

	public static function consultar_registros()
	{
        $core_tipo_transaccion_id = 41; // Nota crédito directa
	    return NotaCreditoDirecta::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                    ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->where('vtas_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->select(
                                'vtas_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3' ),
                                'vtas_doc_encabezados.descripcion AS campo4',
                                'vtas_doc_encabezados.valor_total AS campo5',
                                'vtas_doc_encabezados.estado AS campo6',
                                'vtas_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();
	}
}
