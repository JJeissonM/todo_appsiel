<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Inventarios\InvProducto;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvMotivo;

use App\Contabilidad\Impuesto;
use App\Contabilidad\ContabMovimiento;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\Cliente;
use App\Compras\Proveedor;

class InvDocEncabezado extends Model
{
    //protected $table = 'inv_doc_encabezados'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','core_tipo_doc_app_id', 'vtas_doc_encabezado_origen_id', 'consecutivo','fecha','core_tercero_id', 'inv_bodega_id', 'bodega_destino_id','documento_soporte','descripcion','estado','creado_por','modificado_por','hora_inicio','hora_finalizacion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Bodega', 'Tercero', 'Detalle', 'Estado'];

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( InvDocRegistro::class, 'inv_doc_encabezado_id' );
    }

    public function movimientos()
    {
        return $this->hasMany('App\Inventarios\InvMovimiento');
    }

    public function documento_ventas_padre()
    {
        return VtasDocEncabezado::find( $this->vtas_doc_encabezado_origen_id );
    }

    public function proveedor()
    {
        return Proveedor::where( 'core_tercero_id', $this->core_tercero_id )->get()->first();
    }

    public function cliente()
    {
        return Cliente::where( 'core_tercero_id', $this->core_tercero_id )->get()->first();
    }

    public function enlace_show_documento()
    {
        $app_id = 8;
        
        $enlace = '<a href="' . url( 'inventarios/' . $this->id . '?id=' . $app_id . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

        return $enlace;
    }

    public static function consultar_registros($nro_registros, $search)
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
        $core_tipos_transacciones_ids = [1, 2, 3, 4, 28, 10];

        return InvDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->whereIn('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipos_transacciones_ids)
            ->select(
                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                'inv_bodegas.descripcion AS campo3',
                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS campo4'),
                'inv_doc_encabezados.descripcion AS campo5',
                'inv_doc_encabezados.estado AS campo6',
                'inv_doc_encabezados.id AS campo7'
            )
            ->where("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")")'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipos_transacciones_ids = [1, 2, 3, 4, 28, 10];
        $string = InvDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->whereIn('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipos_transacciones_ids)
            ->select(
                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                'inv_bodegas.descripcion AS campo3',
                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS campo4'),
                'inv_doc_encabezados.descripcion AS campo5',
                'inv_doc_encabezados.estado AS campo6',
                'inv_doc_encabezados.id AS campo7'
            )
            ->where("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")")'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENCABEZADO INVENTARIOS";
    }

    public function crear_encabezado( $modelo_id, $datos )
    {
        $datos['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        return $encabezado_documento->crear_nuevo( $datos );
    }

    /*
            Deprecated. Use App\Inventarios\Services\InvDocumentsService@crear_lineas_registros
    */
    public function crear_lineas_registros( $datos, $doc_encabezado, array $lineas_registros)
    {
        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            $cantidad = (float)$lineas_registros[$i]->cantidad;
            $costo_total = (float)$lineas_registros[$i]->costo_total;

            $motivo = InvMotivo::find( (int)$lineas_registros[$i]->inv_motivo_id );

            // Cuando el motivo de la transacción es de salida, 
            // las cantidades y costos totales restan del movimiento ( negativo )
            if ( $motivo->movimiento == 'salida' )
            {
                $cantidad = (float)$lineas_registros[$i]->cantidad * -1;
                $costo_total = (float)$lineas_registros[$i]->costo_total * -1;
            }

            $linea_datos = 
                            ['inv_bodega_id' => (int)$lineas_registros[$i]->inv_bodega_id] +
                            ['inv_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id] +
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
    /*
            Deprecated. Use App\Inventarios\Services\InvDocumentsService@crear_lineas_registros
    */
    public function contabilizar( $encabezado_documento )
    {
        $lineas_registros = $encabezado_documento->lineas_registros;
        
        if( is_null($lineas_registros) )
        {
            return 0;
        }

        foreach ($lineas_registros as $linea)
        {
            if ( $linea->item->tipo != 'producto')
            {
                continue; // Si no es un producto, saltar la contabilización de abajo.
            }

            $datos = $encabezado_documento->toArray() + $linea->toArray();

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            $valor_debito = abs( $linea->costo_total );
            $valor_credito = 0;

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $linea->motivo->movimiento == 'salida') {
                $valor_debito = 0;
                $valor_credito = abs( $linea->costo_total );
            }        
            
            $cta_inventarios_id = $linea->item->grupo_inventario->cta_inventarios_id; // Dada por el Grupo de Inventarios
            $cta_contrapartida_id = $linea->motivo->cta_contrapartida_id; // Dada por el Motivo de Inventarios

            $this->contabilizar_registro( $datos, $cta_inventarios_id, $valor_debito, $valor_credito);
            // Se invierten los valores Débito y Crédito
            $this->contabilizar_registro( $datos, $cta_contrapartida_id, $valor_credito, $valor_debito);
        }
    }

    /*
            Deprecated. Use App\Inventarios\Services\InvDocumentsService@crear_lineas_registros
    */
    public function contabilizar_registro( $datos, $contab_cuenta_id, $valor_debito, $valor_credito )
    {
        ContabMovimiento::create(   
                                    $datos + 
                                    [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                                    [ 'valor_debito' => $valor_debito] + 
                                    [ 'valor_credito' => ($valor_credito * -1) ] + 
                                    [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                                );
    }

    public static function get_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3';

        $registro = InvDocEncabezado::where('inv_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->select(
                                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                                DB::raw($select_raw),
                                DB::raw($select_raw2),
                                'inv_doc_encabezados.descripcion AS campo4',
                                'inv_doc_encabezados.documento_soporte AS campo5',
                                'inv_doc_encabezados.descripcion AS campo6',
                                'inv_bodegas.descripcion AS campo7',
                                'inv_doc_encabezados.core_tipo_transaccion_id AS campo8',
                                'inv_doc_encabezados.id AS campo9',
                                'inv_doc_encabezados.creado_por AS campo10')
                    ->get()
                    ->toArray();

        return $registro;
    }

    public static function get_registro2($core_tipo_transaccion_id,$core_tipo_doc_app_id,$consecutivo)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3';

        $registro = InvDocEncabezado::where([
            ['core_tipo_transaccion_id','=',$core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=',$core_tipo_doc_app_id],
            ['consecutivo','=',$consecutivo]
        ])
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->select(
                                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                                DB::raw($select_raw),
                                DB::raw($select_raw2),
                                'inv_doc_encabezados.descripcion AS campo4',
                                'inv_doc_encabezados.documento_soporte AS campo5',
                                'inv_doc_encabezados.descripcion AS campo6',
                                'inv_bodegas.descripcion AS campo7',
                                'inv_doc_encabezados.core_tipo_transaccion_id AS campo8',
                                'inv_doc_encabezados.id AS campo9',
                                'inv_doc_encabezados.creado_por AS campo10',
                                'inv_doc_encabezados.consecutivo AS campo11',
                                'inv_doc_encabezados.core_tipo_doc_app_id AS campo12')
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
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->select(
                                'inv_doc_encabezados.id',
                                'inv_doc_encabezados.core_empresa_id',
                                'inv_doc_encabezados.core_tercero_id',
                                'inv_doc_encabezados.core_tipo_transaccion_id',
                                'inv_doc_encabezados.core_tipo_doc_app_id',
                                'inv_doc_encabezados.consecutivo',
                                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS fecha'),
                                'inv_doc_encabezados.descripcion',
                                'inv_doc_encabezados.hora_inicio',
                                'inv_doc_encabezados.inv_bodega_id',
                                'inv_doc_encabezados.documento_soporte',
                                'inv_doc_encabezados.vtas_doc_encabezado_origen_id',
                                'inv_doc_encabezados.estado',
                                'inv_doc_encabezados.creado_por',
                                'inv_doc_encabezados.modificado_por',
                                'inv_doc_encabezados.created_at',
                                'inv_doc_encabezados.hora_finalizacion',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.digito_verificacion',
                                'core_terceros.direccion1',
                                'core_terceros.codigo_ciudad',
                                'core_terceros.telefono1',
                                'inv_bodegas.descripcion AS bodega_descripcion'
                            )
                    ->get()
                    ->first();
    }

    public static function get_documentos_por_transaccion( $core_tipo_transaccion_id, $core_tercero_id, $estado)
    {
        $documentos = InvDocEncabezado::where( [
                                                ['inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id ],
                                                ['inv_doc_encabezados.core_tercero_id', $core_tercero_id],
                                                ['inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id],
                                                ['inv_doc_encabezados.estado', $estado]
                                            ] )
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                                    ->select(
                                                'inv_doc_encabezados.id',
                                                'inv_doc_encabezados.core_empresa_id',
                                                'inv_doc_encabezados.core_tercero_id',
                                                'inv_doc_encabezados.core_tipo_transaccion_id',
                                                'inv_doc_encabezados.core_tipo_doc_app_id',
                                                'inv_doc_encabezados.consecutivo',
                                                DB::raw('DATE_FORMAT(inv_doc_encabezados.fecha,"%d-%m-%Y") AS fecha'),
                                                'inv_doc_encabezados.descripcion',
                                                'inv_doc_encabezados.hora_inicio',
                                                'inv_doc_encabezados.inv_bodega_id',
                                                'inv_doc_encabezados.vtas_doc_encabezado_origen_id',
                                                'inv_doc_encabezados.estado',
                                                'inv_doc_encabezados.creado_por',
                                                'inv_doc_encabezados.modificado_por',
                                                'inv_doc_encabezados.created_at',
                                                'inv_doc_encabezados.hora_finalizacion',
                                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                                DB::raw('CONCAT(core_terceros.descripcion," (",core_terceros.razon_social,")") AS tercero_nombre_completo'),
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
