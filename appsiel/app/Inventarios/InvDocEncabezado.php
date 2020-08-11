<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Inventarios\InvProducto;
use App\Inventarios\InvDocRegistro;

use App\Contabilidad\Impuesto;
use App\Ventas\Cliente;
use App\Compras\Proveedor;

class InvDocEncabezado extends Model
{
    //protected $table = 'inv_doc_encabezados'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_tercero_id','inv_bodega_id','documento_soporte','descripcion','estado','creado_por','modificado_por','hora_inicio','hora_finalizacion'];

    public $encabezado_tabla = ['Fecha','Documento','Bodega','Tercero','Detalle','Estado','Acción'];

    public static function consultar_registros()
    {   
        /*
            Tipos de transacciones de inventarios
            1 = Entrada Almacén
            2 = Transferencia   
            3 = Salida de inventario
            4 = Fabricación
            28 = Ajuste de inventarios
            10 = Saldos iniciales (Contabilidad)
            Hay otras transacciones de inventarios elaboradas desde otras aplicaciones. Por tanto no se visualizan aquí.
        */
        $core_tipos_transacciones_ids = [ 1, 2, 3, 4, 28, 10 ];

    	return InvDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->whereIn('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipos_transacciones_ids)
                    ->select(
                                'inv_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2' ),
                                'inv_bodegas.descripcion AS campo3',
                                DB::raw( 'core_terceros.descripcion AS campo4' ),
                                'inv_doc_encabezados.descripcion AS campo5',
                                'inv_doc_encabezados.estado AS campo6',
                                'inv_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();
    }

    public function movimientos()
    {
        return $this->hasMany('App\Inventarios\InvMovimiento');
    }

    public static function get_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3';

        $registro = InvDocEncabezado::where('inv_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->select('inv_doc_encabezados.fecha AS campo1',DB::raw($select_raw),DB::raw($select_raw2),'inv_doc_encabezados.descripcion AS campo4','inv_doc_encabezados.documento_soporte AS campo5','inv_doc_encabezados.descripcion AS campo6','inv_bodegas.descripcion AS campo7','inv_doc_encabezados.core_tipo_transaccion_id AS campo8','inv_doc_encabezados.id AS campo9')
                    ->get()
                    ->toArray();

        return $registro;
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        return InvDocEncabezado::where('inv_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->select(
                                'inv_doc_encabezados.id',
                                'inv_doc_encabezados.core_empresa_id',
                                'inv_doc_encabezados.core_tercero_id',
                                'inv_doc_encabezados.core_tipo_transaccion_id',
                                'inv_doc_encabezados.core_tipo_doc_app_id',
                                'inv_doc_encabezados.consecutivo',
                                'inv_doc_encabezados.fecha',
                                'inv_doc_encabezados.descripcion',
                                'inv_doc_encabezados.hora_inicio',
                                'inv_doc_encabezados.inv_bodega_id',
                                'inv_doc_encabezados.estado',
                                'inv_doc_encabezados.creado_por',
                                'inv_doc_encabezados.modificado_por',
                                'inv_doc_encabezados.created_at',
                                'inv_doc_encabezados.hora_finalizacion',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'core_terceros.descripcion AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get()
                    ->first();
    }

    public static function get_documentos_por_transaccion( $core_tipo_transaccion_id, $core_tercero_id, $estado)
    {
        $documentos = InvDocEncabezado::where( 'inv_doc_encabezados.core_tercero_id', $core_tercero_id )
                                    ->where( 'inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id )
                                    ->where( 'inv_doc_encabezados.estado', $estado )
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                                    ->select(
                                                'inv_doc_encabezados.id',
                                                'inv_doc_encabezados.core_empresa_id',
                                                'inv_doc_encabezados.core_tercero_id',
                                                'inv_doc_encabezados.core_tipo_transaccion_id',
                                                'inv_doc_encabezados.core_tipo_doc_app_id',
                                                'inv_doc_encabezados.consecutivo',
                                                'inv_doc_encabezados.fecha',
                                                'inv_doc_encabezados.descripcion',
                                                'inv_doc_encabezados.hora_inicio',
                                                'inv_doc_encabezados.inv_bodega_id',
                                                'inv_doc_encabezados.estado',
                                                'inv_doc_encabezados.creado_por',
                                                'inv_doc_encabezados.modificado_por',
                                                'inv_doc_encabezados.created_at',
                                                'inv_doc_encabezados.hora_finalizacion',
                                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero_nombre_completo' ),
                                                'core_terceros.numero_identificacion',
                                                'core_terceros.direccion1',
                                                'core_terceros.telefono1'
                                            )
                                    ->get();


        $cliente = Cliente::where( 'core_tercero_id', $core_tercero_id )->get()->first();
        if ( is_null($cliente) )
        {
            $cliente_id = 0;
        }else{
            $cliente_id = $cliente->id;
        }

        $proveedor = Proveedor::where( 'core_tercero_id', $core_tercero_id )->get()->first();
        if ( is_null($proveedor) )
        {
            $proveedor_id = 0;
        }else{
            $proveedor_id = $proveedor->id;
        }


        foreach ($documentos as $un_documento)
        {
            $registros = InvDocRegistro::where('inv_doc_encabezado_id', $un_documento->id)->get();
            $total_documento_mas_iva = 0;
            foreach ($registros as $un_registro)
            {
                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, $proveedor_id, $cliente_id );

                $precio_total = $un_registro->costo_total * ( 1 + $tasa_impuesto  / 100 );

                $total_documento_mas_iva += $precio_total;
            }

            $un_documento->total_documento = $registros->sum('costo_total');
            $un_documento->total_documento_mas_iva = $total_documento_mas_iva;
        }

        return $documentos;
    }
}
