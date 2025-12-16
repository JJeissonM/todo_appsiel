<?php

namespace App\Tesoreria;

use App\Compras\ComprasDocEncabezado;
use Illuminate\Database\Eloquent\Model;

use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TesoMovimiento extends Model
{
    protected $fillable = ['fecha', 'core_empresa_id', 'core_tercero_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'teso_medio_recaudo_id', 'teso_motivo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'valor_movimiento', 'documento_soporte', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Caja/Banco', 'Tercero', 'Motivo', 'Valor movimiento', 'Detalle'];

    public $vistas = '{"index":"layouts.index3"}';
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function medio_pago()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }

    public function motivo()
    {
        return $this->belongsTo( TesoMotivo::class,'teso_motivo_id');
    }

    public function caja()
    {
        return $this->belongsTo( TesoCaja::class,'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo( TesoCuentaBancaria::class,'teso_cuenta_bancaria_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    } 

    // Para cualquier tipo de transacción
    public static function get_registros_un_documento( $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo )
    {
        return TesoMovimiento::where( [ 
                                'core_tipo_transaccion_id' => $core_tipo_transaccion_id,
                                'core_tipo_doc_app_id' => $core_tipo_doc_app_id,
                                'consecutivo' => $consecutivo
                            ] )
                        ->get();
    }

    // Para cualquier tipo de transacción
    public function get_registro_linea_movimiento( $teso_motivo_id, $valor_movimiento )
    {
        $encabezado = TesoDocEncabezado::where( [ 
                                            'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                            'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                            'consecutivo' => $this->consecutivo
                                        ] )
                                    ->get()->first();

        if ( is_null($encabezado) )
        {
            return null;
        }
        
        return TesoDocRegistro::where( [
                                        ['teso_encabezado_id', '=', $encabezado->id ],
                                        ['teso_motivo_id', '=', $teso_motivo_id ],
                                        ['valor', '=', abs($valor_movimiento) ]
                                    ] )->get()->first();

    }

    public function get_registro_referencia_tercero()
    {
        $codigo_referencia_tercero = json_decode($this->codigo_referencia_tercero,true);

        if ( is_null($codigo_referencia_tercero) )
        {
            return null;
        }

        return app($codigo_referencia_tercero['ruta_modelo'])->find($codigo_referencia_tercero['registro_id']);
    }

    public function get_datos_referencia_tercero()
    {
        $codigo_referencia_tercero = json_decode($this->codigo_referencia_tercero,true);
        
        if ( is_null($codigo_referencia_tercero) )
        {
            return null;
        }
        
        if ( !isset($codigo_referencia_tercero['ruta_modelo']) )
        {
            return null;
        }

        $registro = app($codigo_referencia_tercero['ruta_modelo'])->find($codigo_referencia_tercero['registro_id']);

        $etiqueta = '';
        $valor = '';
        switch ($codigo_referencia_tercero['ruta_modelo'])
        {
            case 'App\Contratotransporte\Vehiculo':
                $etiqueta = 'Placa Vehículo';
                if ( !is_null($registro) )
                {
                    $valor = $registro->placa;
                }
                break;
            
            default:
                # code...
                break;
        }

        return (object)['etiqueta'=>$etiqueta,'valor'=>$valor];
    }

    

    public function enlace_show_documento()
    {
        switch ( $this->core_tipo_transaccion_id )
        {
            case '8':
                $url = 'tesoreria/recaudos/';
                $id_doc_encabezado = TesoDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
            
            case '17':
                $url = 'tesoreria/pagos/';
                $id_doc_encabezado = TesoDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '23':
                $url = 'ventas/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
            
            case '25':
                $url = 'compras/';
                $id_doc_encabezado = ComprasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;

            case '32':
                $url = 'tesoreria/recaudos_cxc/';
                $id_doc_encabezado = TesoDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;

            case '33':
                $url = 'tesoreria/pagos_cxp/';
                $id_doc_encabezado = TesoDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;

            case '36': // Nota credito compras
                $url = 'compras/';
                $id_doc_encabezado = ComprasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '38': // Nota crédito cliente
                $url = 'ventas/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;        
        
            case '41': // Nota crédito directa
                $url = 'ventas/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '47':
                $url = 'pos_factura/';
                $id_doc_encabezado = FacturaPos::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '49': // Factura de estudiantes
                $url = 'ventas/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '52':
                $url = 'fe_factura/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '53': // Nota Crédito Electrónica de Ventas
                $url = 'fe_nota_credito/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        
            case '61': // Nota crédito por valor en compras
                $url = 'compras/';
                $id_doc_encabezado = ComprasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
            
            default:
                $url = 'ventas/';
                $id_doc_encabezado = VtasDocEncabezado::where( [ 
                                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                                        'consecutivo' => $this->consecutivo
                                                    ] )->first()->id;
                break;
        }
        
        if( $this->tipo_documento_app == null )
        {
            dd('Error en Tipo de Documento (tipo_documento_app)', $this);
        }            

        $enlace = '<a href="' . url( $url . $id_doc_encabezado . '?id=' . Input::get('id') . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

        return $enlace;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS campo2';

        $registros = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_movimientos.teso_caja_id')
            ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_movimientos.teso_cuenta_bancaria_id')
            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
            ->where('teso_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'teso_movimientos.fecha AS campo1',
                DB::raw($select_raw),
                DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'teso_motivos.descripcion AS campo5',
                'teso_movimientos.valor_movimiento AS campo6',
                'teso_movimientos.descripcion AS campo7',
                'teso_movimientos.id AS campo8'
            )
            ->where("teso_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.valor_movimiento", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.descripcion", "LIKE", "%$search%")
            ->orderBy('teso_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS DOCUMENTO';

        $string = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_movimientos.teso_caja_id')
            ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_movimientos.teso_cuenta_bancaria_id')
            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
            ->where('teso_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'teso_movimientos.fecha AS FECHA',
                DB::raw($select_raw),
                DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion ) AS CAJA_BANCO'),
                'core_terceros.descripcion AS TERCERO',
                'teso_motivos.descripcion AS MOTIVO',
                'teso_movimientos.valor_movimiento AS VALOR_MOVIMIENTO',
                'teso_movimientos.descripcion AS DETALLE'
            )
            ->where("teso_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.valor_movimiento", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.descripcion", "LIKE", "%$search%")
            ->orderBy('teso_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS DE TESORERIA";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS campo2';

        $registros = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_movimientos.teso_caja_id')
            ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_movimientos.teso_cuenta_bancaria_id')
            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
            ->where('teso_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'teso_movimientos.fecha AS campo1',
                DB::raw($select_raw),
                DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'teso_motivos.descripcion AS campo5',
                'teso_movimientos.valor_movimiento AS campo6',
                'teso_movimientos.descripcion AS campo7',
                'teso_movimientos.id AS campo8'
            )
            ->where("teso_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.valor_movimiento", "LIKE", "%$search%")
            ->orWhere("teso_movimientos.descripcion", "LIKE", "%$search%")
            ->orderBy('teso_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function movimiento_por_tipo_motivo($tipo_movimiento, $fecha_inicial, $fecha_final, $teso_caja_id = null)
    {
        $operador = '>';
        if( $tipo_movimiento == 'salida' )
        {
            $operador = '<';
        }

        $array_wheres = [ ['teso_movimientos.valor_movimiento' , $operador, 0 ] ];
        
        if ( !is_null($teso_caja_id) ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_caja_id' => (int) $teso_caja_id ]);
        }

        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                ->whereBetween('teso_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                                ->where( $array_wheres )
                                ->groupBy('teso_movimientos.teso_motivo_id')
                                ->select(
                                            'teso_motivos.descripcion as motivo',
                                            'teso_motivos.movimiento',
                                            'teso_movimientos.codigo_referencia_tercero',
                                            DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento')
                                        )
                                ->get();
    }

    public static function get_suma_movimientos_menor_a_la_fecha($fecha)
    {
        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->where('teso_movimientos.fecha', '<', $fecha)
            ->sum('teso_movimientos.valor_movimiento');
    }

    public static function get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde )
    {
        $array_wheres = [ ['teso_movimientos.id' ,'>', 0 ] ];
        
        if ( $teso_caja_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_caja_id' => (int) $teso_caja_id ]);
        }
        
        if ( $teso_cuenta_bancaria_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_cuenta_bancaria_id' => (int) $teso_cuenta_bancaria_id ]);
        }

        $saldo_inicial = TesoMovimiento::where( $array_wheres )
                            ->where( 'fecha','<',$fecha_desde )
                            ->select(
                                        DB::raw('sum(valor_movimiento) as valor_movimiento') )
                            ->get()
                            ->first();

        if ( is_null( $saldo_inicial->valor_movimiento ) )
        {
            return 0;
        }

        return $saldo_inicial->valor_movimiento;
    }

    public static function get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta, $tipo_movimiento = null )
    {

        $array_wheres = [ ['teso_movimientos.id' ,'>', 0 ] ];
        
        if ( !is_null($tipo_movimiento) ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_motivos.movimiento' => $tipo_movimiento ]);
        }
        
        if ( $teso_caja_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_caja_id' => (int) $teso_caja_id ]);
        }
        
        if ( $teso_cuenta_bancaria_id != 0 ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_cuenta_bancaria_id' => (int) $teso_cuenta_bancaria_id ]);
        }

        return TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
                            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->where( $array_wheres )
                            ->select(
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                                        'teso_motivos.descripcion AS motivo_descripcion',
                                        'teso_movimientos.fecha',
                                        'teso_movimientos.valor_movimiento',
                                        'teso_movimientos.teso_motivo_id',
                                        'teso_movimientos.descripcion',
                                        'teso_movimientos.codigo_referencia_tercero',
                                        'teso_movimientos.core_tipo_transaccion_id',
                                        'teso_movimientos.core_tipo_doc_app_id',
                                        'teso_movimientos.consecutivo',
                                        'teso_movimientos.teso_caja_id',
                                        'teso_movimientos.teso_cuenta_bancaria_id',
                                        'teso_movimientos.core_tercero_id',
                                        'teso_movimientos.created_at',
                                        'core_terceros.descripcion as tercero_descripcion' )
                            ->orderBy('teso_movimientos.fecha')
                            ->orderBy('teso_movimientos.created_at')
                            ->get();
    }

    public static function get_movimiento2( $fecha_desde, $fecha_hasta, $array_wheres )
    {
        return TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
                            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->where( $array_wheres )
                            ->select(
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                                        'teso_motivos.descripcion AS motivo_descripcion',
                                        'teso_movimientos.fecha',
                                        'teso_movimientos.valor_movimiento',
                                        'teso_movimientos.id',
                                        'teso_movimientos.teso_motivo_id',
                                        'teso_movimientos.descripcion',
                                        'teso_movimientos.codigo_referencia_tercero',
                                        'teso_movimientos.core_tipo_transaccion_id',
                                        'teso_movimientos.core_tipo_doc_app_id',
                                        'teso_movimientos.consecutivo',
                                        'teso_movimientos.teso_caja_id',
                                        'teso_movimientos.teso_cuenta_bancaria_id',
                                        'teso_movimientos.created_at',
                                        'core_terceros.descripcion as tercero_descripcion' )
                            ->orderBy('teso_movimientos.fecha')
                            ->orderBy('teso_movimientos.created_at')
                            ->get();
    }

    public function almacenar_registro_pago_contado( $datos, $registros_medio_pago, $movimiento, $valor_movimiento )
    {
        $signo_unidad = 1;
        if ( $movimiento == 'salida' )
        {
            $signo_unidad = -1;
        }

        if ( empty( $registros_medio_pago ) )
        {
            // Valores por defecto
            $caja = TesoCaja::get()->first();
        
            // Agregar el movimiento a tesorería
            $teso_motivo_id = (int)config('tesoreria.motivo_recibo_caja_id');
            if ( $movimiento == 'salida' )
            {
                $teso_motivo_id = (int)config('tesoreria.motivo_comprobante_egresos_id');
            }
            $motivo = TesoMotivo::find($teso_motivo_id);
            
            if ($motivo == null) {
                $motivo = TesoMotivo::where( 'movimiento', $movimiento )->get()->first();
            }

            $datos['teso_motivo_id'] = $motivo->id;
            $datos['teso_caja_id'] = $caja->id;
            $datos['teso_cuenta_bancaria_id'] = 0;
            $datos['teso_medio_recaudo_id'] = 1;
            $datos['valor_movimiento'] = $valor_movimiento * $signo_unidad;// Motivo de salida, movimiento negativo

            TesoMovimiento::create( $datos );
        }else{

            if (isset( $registros_medio_pago['teso_caja_id'])) { // Un solo registro
                $datos['teso_motivo_id'] = $registros_medio_pago['teso_motivo_id'];
                $datos['teso_caja_id'] = $registros_medio_pago['teso_caja_id'];
                $datos['teso_cuenta_bancaria_id'] = $registros_medio_pago['teso_cuenta_bancaria_id'];
                $datos['teso_medio_recaudo_id'] = $registros_medio_pago['teso_medio_recaudo_id'];
                $datos['valor_movimiento'] = $registros_medio_pago['valor_recaudo'] * $signo_unidad;

                TesoMovimiento::create( $datos );
            }else{
                foreach ($registros_medio_pago as $linea_registro_medio_pago) {

                    $datos['teso_motivo_id'] = $linea_registro_medio_pago['teso_motivo_id'];
                    $datos['teso_caja_id'] = $linea_registro_medio_pago['teso_caja_id'];
                    $datos['teso_cuenta_bancaria_id'] = $linea_registro_medio_pago['teso_cuenta_bancaria_id'];
                    $datos['teso_medio_recaudo_id'] = $linea_registro_medio_pago['teso_medio_recaudo_id'];
                    $datos['valor_movimiento'] = $linea_registro_medio_pago['valor_recaudo'] * $signo_unidad;

                    TesoMovimiento::create( $datos );
                }
            }            
        }
        
    }
}
