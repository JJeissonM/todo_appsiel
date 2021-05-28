<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;

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

    // Para cualquier tipo de transacción
    public static function get_registros_un_documento( $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo )
    {
        return TesoDocEncabezado::where( [ 
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

        $registro = app($codigo_referencia_tercero['ruta_modelo'])->find($codigo_referencia_tercero['registro_id']);

        $etiqueta = '';
        $valor = '';
        switch ($codigo_referencia_tercero['ruta_modelo'])
        {
            case 'App\Contratotransporte\Vehiculo':
                $etiqueta = 'Placa Vehículo';
                $valor = $registro->placa;
                break;
            
            default:
                # code...
                break;
        }

        return (object)['etiqueta'=>$etiqueta,'valor'=>$valor];
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
        $array_wheres = [ ['teso_motivos.movimiento' ,'=', $tipo_movimiento ] ];
        
        if ( !is_null($teso_caja_id) ) 
        {
            $array_wheres = array_merge($array_wheres, ['teso_movimientos.teso_caja_id' => (int) $teso_caja_id ]);
        }

        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                ->whereBetween('teso_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                                ->where( $array_wheres )
                                ->where('teso_motivos.teso_tipo_motivo', '<>', 'Traslado')
                                ->groupBy('teso_movimientos.teso_motivo_id')
                                ->select(
                                            'teso_motivos.descripcion as motivo',
                                            'teso_motivos.movimiento',
                                            'teso_movimientos.codigo_referencia_tercero',
                                            DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento')
                                        )
                                ->get()
                                ->toArray();
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
            $datos['teso_motivo_id'] = TesoMotivo::where( 'movimiento', $movimiento )->get()->first()->id;
            $datos['teso_caja_id'] = $caja->id;
            $datos['teso_cuenta_bancaria_id'] = 0;
            $datos['teso_medio_recaudo_id'] = 1;
            $datos['valor_movimiento'] = $valor_movimiento * $signo_unidad;// Motivo de salida, movimiento negativo
        }else{
            // WARNING!!! Por ahora solo se está aceptando un solo medio de pago
            $datos['teso_motivo_id'] = $registros_medio_pago['teso_motivo_id'];
            $datos['teso_caja_id'] = $registros_medio_pago['teso_caja_id'];
            $datos['teso_cuenta_bancaria_id'] = $registros_medio_pago['teso_cuenta_bancaria_id'];
            $datos['teso_medio_recaudo_id'] = $registros_medio_pago['teso_medio_recaudo_id'];
            $datos['valor_movimiento'] = $registros_medio_pago['valor_recaudo'] * $signo_unidad;
        }

        TesoMovimiento::create( $datos );
    }
}
