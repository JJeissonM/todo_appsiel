<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Inventarios\InvDocRegistro;

class DevolucionVentas extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    public $encabezado_tabla = ['Fecha','Documento','Bodega','Tercero','Detalle','Estado','Acción'];

    public static function consultar_registros()
    {
        $core_tipo_transaccion_id = 34; // Devolución
        $registros = DevolucionVentas::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->where('inv_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->select('inv_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2' ),
                                'inv_bodegas.descripcion AS campo3',
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo4' ),
                                'inv_doc_encabezados.descripcion AS campo5',
                                'inv_doc_encabezados.estado AS campo6',
                                'inv_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function crear_nueva( $datos, $encabezado_remision_id, $parametros = null )
    {

        // Paso 1: Crear encabezado
        if ( is_null( $parametros ) )
        {
            // Llamar a los parámetros del archivo de configuración
            $parametros = config('ventas');
        }

        $datos['core_tipo_transaccion_id'] = $parametros['dvc_tipo_transaccion_id'];
        $datos['core_tipo_doc_app_id'] = $parametros['dvc_tipo_doc_app_id'];
        
        $datos['inv_bodega_id'] = InvDocEncabezado::find( $encabezado_remision_id )->inv_bodega_id;
        $datos['estado'] = 'Facturada';
        $encabezado_documento = $this->crear_encabezado( $parametros['dvc_modelo_id'], $datos );

        // Paso 2
        // El campo lineas_registros se crea con base en las líneas de regitros de la Remisión de ventas
        $lineas_registros = $this->obtener_lineas_registros( $datos, $encabezado_remision_id );
        $this->crear_lineas_registros( $datos, $encabezado_documento, $lineas_registros );

        // Paso 3
        $this->contabilizar( $encabezado_documento );

        return $encabezado_documento;
    }

    public function obtener_lineas_registros( $datos, $encabezado_remision_id )
    {
        $lineas_registros = [];

        // Obtener registros de la remisión de la factura de ventas
        // Se harán la devoluciones a cada línea de estos registros (si se le ingresó cantidad a devolver)
        $registros_rm = InvDocRegistro::where( 'inv_doc_encabezado_id', $encabezado_remision_id )->get();
        $l = 0; // Contador para las lineas a devolver
        $regs = 0; // Contador para los registro de la remisión
        foreach ($registros_rm as $linea)
        {
            $cantidad_devolver = (float)$datos['cantidad_devolver'][$regs];
            
            if ( $cantidad_devolver > 0)
            {
                $linea_devolucion = $linea->toArray();
                $linea_devolucion['cantidad'] = $cantidad_devolver;
                $linea_devolucion['inv_motivo_id'] = (int)explode('-', $datos['motivos_ids'][$l])[0]; // El input del formulario trae los motivos en formato ID-descripcion, se toma solo el ID
                $linea_devolucion['costo_total'] = $cantidad_devolver * $linea['costo_unitario'];

                $lineas_registros[$l] = (object)( $linea_devolucion );

                $l++;
            }
            $regs++;  
        }

        return $lineas_registros;
    }

}
