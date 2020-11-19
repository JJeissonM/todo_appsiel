<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class RemisionVentas extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    public $encabezado_tabla = ['Fecha','Documento','Bodega','Tercero','Detalle','Estado','Acción'];

    public static function consultar_registros()
    {
    	$core_tipo_transaccion_id = 24; // Remisión de ventas
        return RemisionVentas::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->where('inv_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->select(
                                'inv_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2' ),
                                'inv_bodegas.descripcion AS campo3',
                                'core_terceros.descripcion AS campo4',
                                'inv_doc_encabezados.descripcion AS campo5',
                                'inv_doc_encabezados.estado AS campo6',
                                'inv_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();
    }

    public function crear_nueva( $datos, $parametros = null )
    {
        // Paso 1: Crear encabezado
        if ( is_null( $parametros ) )
        {
            // Llamar a los parámetros del archivo de configuración
            $parametros = config('ventas');
        }

        $datos['core_tipo_transaccion_id'] = $parametros['rm_tipo_transaccion_id'];
        $datos['core_tipo_doc_app_id'] = $parametros['rm_tipo_doc_app_id'];
        
        $datos['estado'] = 'Facturada';
        $encabezado_documento = $this->crear_encabezado( $parametros['rm_modelo_id'], $datos );

        // Paso 2
        // El campo lineas_registros viene del request
        $lineas_registros = json_decode( $datos['lineas_registros'] );
        $this->crear_lineas_registros( $datos, $encabezado_documento, $lineas_registros );

        // Paso 3
        $this->contabilizar( $encabezado_documento );

        return $encabezado_documento;
    }

}
