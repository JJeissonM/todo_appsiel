<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Contabilidad\ContabMovimiento;

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

            // Modelo del encabezado del documento
            $rm_modelo_id = $parametros['rm_modelo_id'];
            $rm_tipo_transaccion_id = $parametros['rm_tipo_transaccion_id'];
            $rm_tipo_doc_app_id = $parametros['rm_tipo_doc_app_id'];

            $datos['core_tipo_transaccion_id'] = $rm_tipo_transaccion_id;
            $datos['core_tipo_doc_app_id'] = $rm_tipo_doc_app_id;
        }
        
        $datos['estado'] = 'Facturada';
        $encabezado_documento = $this->crear_encabezado( $rm_modelo_id, $datos );


        // Paso 2
        $lineas_registros = json_decode( $datos['lineas_registros'] );
        $this->crear_lineas_registros( $datos, $encabezado_documento, $lineas_registros );

        // Paso 3
        $this->contabilizar( $encabezado_documento );

        return $encabezado_documento;
    }

    public function crear_encabezado( $modelo_id, $datos )
    {
        $datos['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        return $encabezado_documento->crear_nuevo( $datos );
    }

    public function crear_lineas_registros( $datos, $doc_encabezado, array $lineas_registros)
    {

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            // Movimiento de salida de inventarios (negativos)
            $cantidad = (float)$lineas_registros[$i]->cantidad * -1;
            $costo_total = (float)$lineas_registros[$i]->costo_total * -1;

            $linea_datos = ['inv_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['costo_unitario' => (float)$lineas_registros[$i]->costo_unitario] +
                            ['cantidad' => $cantidad] +
                            ['costo_total' => $costo_total];

            InvDocRegistro::create(
                                    $datos +
                                    $linea_datos +
                                    ['inv_doc_encabezado_id' => $doc_encabezado->id]
                                );

            // Solo se almacena el movimiento para productos almacenables
            $tipo_producto = InvProducto::find($lineas_registros[$i]->inv_producto_id)->tipo;
            if ( $tipo_producto == 'producto' )
            {
                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                InvMovimiento::create(
                                        $datos +
                                        $linea_datos +
                                        ['inv_doc_encabezado_id' => $doc_encabezado->id]
                                    );
            }    
        }
    }


    /*
        Cuentas de Inventarios vs Costo de ventas
        Aplica a productos almacenables
    */
    public function contabilizar( $encabezado_documento ) {
        $lineas_registros = $encabezado_documento->lineas_registros;

        foreach ($lineas_registros as $linea) {
            if ( $linea->item->tipo != 'producto') {
                // Si no es un producto, saltar la contabilización de abajo.
                continue;
            }

            // Cta. DB (Costo de ventas) Dada por el Motivo de Inventarios
            $cta_contrapartida_id = $linea->motivo->cta_contrapartida_id;
            $valor_debito = abs( $linea->costo_total );
            ContabMovimiento::create( $encabezado_documento->toArray() + 
                                        $linea->toArray() + 
                                        [ 'contab_cuenta_id' => $cta_contrapartida_id ] +
                                        [ 'valor_debito' => $valor_debito ] + 
                                        [ 'valor_credito' => 0 ] + 
                                        [ 'valor_saldo' => $valor_debito ]
                                    );
        
            // Cta. CR (Inventarios) Dada por el Grupo de Inventarios
            $cta_inventarios_id = $linea->item->grupo_inventario->cta_inventarios_id;
            $valor_credito = abs( $linea->costo_total );
            ContabMovimiento::create( $encabezado_documento->toArray() + 
                                        $linea->toArray() + 
                                        [ 'contab_cuenta_id' => $cta_inventarios_id ] +
                                        [ 'valor_debito' => 0 ] + 
                                        [ 'valor_credito' => ( $valor_credito * -1 ) ] + 
                                        [ 'valor_saldo' => ( $valor_credito * -1 ) ]
                                    );

        }
    }

}
