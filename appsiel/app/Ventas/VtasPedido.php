<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class VtasPedido extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    public $encabezado_tabla = ['Fecha','Documento','Cliente','Detalle','Acción'];

    public static function consultar_registros()
    {
        $core_tipo_transaccion_id = 42;
        return VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                    ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->where('vtas_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->select(
                                'vtas_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3' ),
                                'vtas_doc_encabezados.descripcion AS campo4',
                                'vtas_doc_encabezados.id AS campo5')
                    ->get()
                    ->toArray();
                    /*
                    ->leftJoin('vtas_doc_registros', 'vtas_doc_registros.vtas_doc_encabezado_id', '=', 'vtas_doc_encabezados.id')
                                DB::raw( 'SUM(vtas_doc_registros.precio_total) AS campo5' ),
                    */
    }
}
