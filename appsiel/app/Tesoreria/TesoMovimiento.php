<?php

namespace App\Tesoreria;

use App\Compras\ComprasDocEncabezado;
use App\Tesoreria\Services\PdvResolver;
use App\Traits\FiltraRegistrosPorUsuario;
use Illuminate\Database\Eloquent\Model;

use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\CierreEncabezado;
use App\VentasPos\FacturaPos;
use App\VentasPos\Pdv;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use App\Traits\HasTurnoOperativo;
use App\Core\Services\TurnoAssignmentResolver;
use App\Core\Services\TurnoManager;

class TesoMovimiento extends Model
{
    use FiltraRegistrosPorUsuario, HasTurnoOperativo;

    protected $fillable = ['fecha', 'core_empresa_id', 'core_tercero_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'turno_operativo_id', 'teso_medio_recaudo_id', 'teso_motivo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'pdv_id', 'valor_movimiento', 'documento_soporte', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    protected function turnoModuleName()
    {
        return 'tesoreria';
    }

    protected function deferTurnoAssignment()
    {
        return true;
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Caja/Banco', 'Tercero', 'Motivo', 'Valor movimiento', 'Detalle','F. creación'];

    public $vistas = '{"index":"layouts.index3"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ( !Schema::hasColumn($model->getTable(), 'pdv_id') ) {
                // Instalaciones históricas sin la columna PDV aún deben respetar
                // un contexto propagado o fallar si el módulo fue activado globalmente.
                app(TurnoAssignmentResolver::class)->assign($model, 'tesoreria');
                return;
            }

            $pdv_id = PdvResolver::resolveFromArray($model->getAttributes());
            $model->pdv_id = $pdv_id;

            app(TurnoAssignmentResolver::class)->assign($model, 'tesoreria', $pdv_id);

            $advancedMode = !is_null($pdv_id)
                && app(TurnoManager::class)->enabledForPdv($model->core_empresa_id, $pdv_id, 'tesoreria');

            if (empty($model->turno_operativo_id) && !$advancedMode && !$model->isDirty('created_at')) {
                $model->sincronizarCreatedAtConUltimoCierre();
            }
        });

        static::updating(function ($model) {
            if ( !Schema::hasColumn($model->getTable(), 'pdv_id') ) {
                return;
            }

            $pdvId = PdvResolver::normalize($model->pdv_id);
            $advancedMode = !is_null($pdvId)
                && app(TurnoManager::class)->enabledForPdv($model->core_empresa_id, $pdvId, 'tesoreria');
            if (empty($model->turno_operativo_id) && !$advancedMode) {
                $model->sincronizarCreatedAtConUltimoCierre();
            }
        });
    }

    /**
     * Ubica el movimiento dentro del ultimo cierre del PDV para su fecha contable.
     * Si no existe un cierre, Eloquent conserva el created_at normal del movimiento.
     */
    public function sincronizarCreatedAtConUltimoCierre()
    {
        $pdv_id = PdvResolver::normalize($this->pdv_id);
        $fecha = substr(trim((string)$this->fecha), 0, 10);

        if ( is_null($pdv_id) || $fecha == '' ) {
            return $this;
        }

        $created_at_cierre = CierreEncabezado::where('pdv_id', $pdv_id)
            ->where('fecha', $fecha)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->value('created_at');

        if ( !is_null($created_at_cierre) ) {
            $this->created_at = $created_at_cierre;
        }

        return $this;
    }
    
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

    public function pdv()
    {
        return $this->belongsTo(Pdv::class, 'pdv_id');
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
        $app_id = Input::get('id');
        if ( !is_null( $this->tipo_transaccion ) )
        {
            $app_id = $this->tipo_transaccion->core_app_id;
        }

        $enlace = '<a href="' . url( 'enlace_show_documento/' . $app_id . '/' . $this->core_tipo_transaccion_id . '/' . $this->core_tipo_doc_app_id . '/' . $this->consecutivo ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

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
                'teso_movimientos.created_at AS campo8',
                'teso_movimientos.id AS campo9'
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
                'teso_movimientos.created_at AS campo8',
                'teso_movimientos.id AS campo9'
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

    public static function movimiento_por_tipo_motivo($tipo_movimiento, $fecha_inicial, $fecha_final, $teso_caja_id = null, $creado_por = null, $pdv_id = 0, $fecha_hora_apertura = null, $fecha_hora_cierre = null, $turno_operativo_id = null)
    {
        if (!is_null($turno_operativo_id)) {
            $fecha_hora_apertura = null;
            $fecha_hora_cierre = null;
        } elseif ( self::usarMovimientosTesoreriaPorHora() ) {
            $fecha_hora_apertura = self::normalizarFechaHoraFiltro($fecha_hora_apertura);
            $fecha_hora_cierre = self::normalizarFechaHoraFiltro($fecha_hora_cierre);
        } else {
            $fecha_hora_apertura = null;
            $fecha_hora_cierre = null;
        }

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

        $query = TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                ->where( $array_wheres );

        if (!is_null($turno_operativo_id)) {
            // En modo turnos la FK es autoritativa; la fecha solo conserva valor contable.
            $query->where('teso_movimientos.turno_operativo_id', (int)$turno_operativo_id);
        } else {
            $query->whereBetween('teso_movimientos.fecha', [ $fecha_inicial, $fecha_final ] );
        }

        if (is_null($turno_operativo_id) && (int)$pdv_id != 0) {
            $query = self::aplicarFiltroPdv($query, (int)$pdv_id, (int)$teso_caja_id, 0);
        }

        if ( !is_null($fecha_hora_apertura) && $fecha_hora_apertura != '' ) {
            $query->where('teso_movimientos.created_at', '>=', $fecha_hora_apertura);
        }

        if ( !is_null($fecha_hora_cierre) && $fecha_hora_cierre != '' ) {
            $query->where('teso_movimientos.created_at', '<=', $fecha_hora_cierre);
        }

        // Un arqueo de PDV debe incluir todos los movimientos de ese punto de venta,
        // incluso cuando no existe una apertura/cierre registrada para el día.
        $filtrarPorUsuario = is_null($turno_operativo_id) && (int)$pdv_id == 0;

        if ( $filtrarPorUsuario ) {
            if ( !is_null($creado_por) )
            {
                $empresa_id = Auth::check() ? Auth::user()->empresa_id : null;
                $userFiltro = self::obtenerUsuarioFiltroPorEmail($creado_por, $empresa_id);

                if ( is_null($userFiltro) || !self::usuarioTieneRolPrivilegiado($userFiltro, self::rolesSinFiltro()) )
                {
                    $emails = self::obtenerEmailsFiltroPorEmail($creado_por, $empresa_id);
                    if ( !empty($emails) ) {
                        $query->whereIn('teso_movimientos.creado_por', $emails);
                    }
                }
            }else{
                $query = self::aplicarFiltroCreadoPor($query, 'teso_movimientos.creado_por');
            }
        }

        return $query->groupBy('teso_movimientos.teso_motivo_id')
                    ->select(
                                'teso_motivos.descripcion as motivo',
                                'teso_motivos.movimiento',
                                'teso_movimientos.codigo_referencia_tercero',
                                DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento')
                            )
                    ->get();
    }

    public static function usarMovimientosTesoreriaPorHora()
    {
        return (int)config('tesoreria.usar_movimientos_tesoreria_por_hora', 1) === 1;
    }

    protected static function normalizarFechaHoraFiltro($fecha_hora)
    {
        if (is_null($fecha_hora)) {
            return null;
        }

        $fecha_hora = trim(str_replace('T', ' ', $fecha_hora));

        if ($fecha_hora == '' || substr($fecha_hora, 0, 10) == '0000-00-00') {
            return null;
        }

        return $fecha_hora;
    }

    public static function get_suma_movimientos_menor_a_la_fecha($fecha)
    {
        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->where('teso_movimientos.fecha', '<', $fecha)
            ->sum('teso_movimientos.valor_movimiento');
    }

    public static function get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $pdv_id = 0 )
    {
        $query = TesoMovimiento::query()->where('teso_movimientos.id', '>', 0);

        if ( $teso_caja_id != 0 )
        {
            $query->where('teso_movimientos.teso_caja_id', (int) $teso_caja_id);
        }

        if ( $teso_cuenta_bancaria_id != 0 )
        {
            $query->where('teso_movimientos.teso_cuenta_bancaria_id', (int) $teso_cuenta_bancaria_id);
        }

        self::aplicarFiltroPdv($query, $pdv_id, $teso_caja_id, $teso_cuenta_bancaria_id);

        $saldo_inicial = $query->where('teso_movimientos.fecha', '<', $fecha_desde)
                            ->select(DB::raw('sum(valor_movimiento) as valor_movimiento'))
                            ->get()
                            ->first();

        if ( is_null( $saldo_inicial->valor_movimiento ) )
        {
            return 0;
        }

        return $saldo_inicial->valor_movimiento;
    }

    public static function get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta, $tipo_movimiento = null, $pdv_id = 0 )
    {
        $query = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
                            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
                            ->where('teso_movimientos.id', '>', 0)
                            ->whereBetween('teso_movimientos.fecha', [$fecha_desde, $fecha_hasta]);

        if ( !is_null($tipo_movimiento) )
        {
            $query->where('teso_motivos.movimiento', $tipo_movimiento);
        }

        if ( $teso_caja_id != 0 )
        {
            $query->where('teso_movimientos.teso_caja_id', (int) $teso_caja_id);
        }

        if ( $teso_cuenta_bancaria_id != 0 )
        {
            $query->where('teso_movimientos.teso_cuenta_bancaria_id', (int) $teso_cuenta_bancaria_id);
        }

        self::aplicarFiltroPdv($query, $pdv_id, $teso_caja_id, $teso_cuenta_bancaria_id);

        return $query->select(
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
                                        'teso_movimientos.pdv_id',
                                        'teso_movimientos.teso_cuenta_bancaria_id',
                                        'teso_movimientos.core_tercero_id',
                                        'teso_movimientos.created_at',
                                        'core_terceros.descripcion as tercero_descripcion' )
                            ->orderBy('teso_movimientos.fecha')
                            ->orderBy('teso_movimientos.created_at')
                            ->get();
    }

    public static function aplicarFiltroPdv($query, $pdv_id, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        $pdv_id = (int)$pdv_id;
        if ( $pdv_id == 0 ) {
            return $query;
        }

        $teso_caja_id = (int)$teso_caja_id;
        $teso_cuenta_bancaria_id = (int)$teso_cuenta_bancaria_id;
        $pdv = Pdv::find($pdv_id);

        return $query->where(function ($subquery) use ($pdv_id, $pdv, $teso_caja_id, $teso_cuenta_bancaria_id) {
            $subquery->where('teso_movimientos.pdv_id', $pdv_id);

            if ( self::debeIncluirMovimientosLegacyPdv($pdv, $teso_caja_id, $teso_cuenta_bancaria_id) ) {
                $subquery->orWhere(function ($legacyQuery) use ($pdv) {
                    $legacyQuery->whereNull('teso_movimientos.pdv_id')
                                ->where('teso_movimientos.teso_caja_id', (int)$pdv->caja_default_id);
                });
            }
        });
    }

    protected static function debeIncluirMovimientosLegacyPdv($pdv, $teso_caja_id, $teso_cuenta_bancaria_id)
    {
        if ( is_null($pdv) || (int)$pdv->caja_default_id == 0 || (int)$teso_cuenta_bancaria_id != 0 ) {
            return false;
        }

        return (int)$teso_caja_id == 0 || (int)$teso_caja_id == (int)$pdv->caja_default_id;
    }

    public static function aplicarFiltroEntreFechasHoras($query, $fecha_desde, $fecha_hasta, $hora_desde = null, $hora_hasta = null)
    {
        $hora_desde = self::normalizarHora($hora_desde);
        $hora_hasta = self::normalizarHora($hora_hasta, true);

        if (is_null($hora_desde) && is_null($hora_hasta)) {
            return $query->whereBetween('teso_movimientos.fecha', [$fecha_desde, $fecha_hasta]);
        }

        return $query->where(function ($query) use ($fecha_desde, $fecha_hasta, $hora_desde, $hora_hasta) {
            if ($fecha_desde == $fecha_hasta) {
                $query->where('teso_movimientos.fecha', $fecha_desde);
                self::aplicarHorasCreatedAt($query, $hora_desde, $hora_hasta);
                return;
            }

            $query->where(function ($query) use ($fecha_desde, $hora_desde) {
                $query->where('teso_movimientos.fecha', $fecha_desde);
                self::aplicarHorasCreatedAt($query, $hora_desde, null);
            })->orWhere(function ($query) use ($fecha_desde, $fecha_hasta) {
                $query->where('teso_movimientos.fecha', '>', $fecha_desde)
                    ->where('teso_movimientos.fecha', '<', $fecha_hasta);
            })->orWhere(function ($query) use ($fecha_hasta, $hora_hasta) {
                $query->where('teso_movimientos.fecha', $fecha_hasta);
                self::aplicarHorasCreatedAt($query, null, $hora_hasta);
            });
        });
    }

    public static function aplicarFiltroAntesDeFechaHora($query, $fecha_desde, $hora_desde = null)
    {
        $hora_desde = self::normalizarHora($hora_desde);

        if (is_null($hora_desde)) {
            return $query->where('teso_movimientos.fecha', '<', $fecha_desde);
        }

        return $query->where(function ($query) use ($fecha_desde, $hora_desde) {
            $query->where('teso_movimientos.fecha', '<', $fecha_desde)
                ->orWhere(function ($query) use ($fecha_desde, $hora_desde) {
                    $query->where('teso_movimientos.fecha', $fecha_desde)
                        ->whereTime('teso_movimientos.created_at', '<', $hora_desde);
                });
        });
    }

    protected static function aplicarHorasCreatedAt($query, $hora_desde, $hora_hasta)
    {
        if (!is_null($hora_desde)) {
            $query->whereTime('teso_movimientos.created_at', '>=', $hora_desde);
        }

        if (!is_null($hora_hasta)) {
            $query->whereTime('teso_movimientos.created_at', '<=', $hora_hasta);
        }
    }

    public static function normalizarHora($hora, $es_hora_hasta = false)
    {
        if (is_null($hora) || trim((string)$hora) == '') {
            return null;
        }

        $hora = trim((string)$hora);
        if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
            return $hora . ($es_hora_hasta ? ':59' : ':00');
        }

        if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $hora)) {
            return $hora;
        }

        return null;
    }

    public static function get_movimiento2( $fecha_desde, $fecha_hasta, $array_wheres, $user_id = 0, $pdv_id = 0, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0, $hora_desde = null, $hora_hasta = null )
    {
        $query = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
                                ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
                                ->where( $array_wheres );

        self::aplicarFiltroEntreFechasHoras($query, $fecha_desde, $fecha_hasta, $hora_desde, $hora_hasta);

        self::aplicarFiltroPdv($query, $pdv_id, $teso_caja_id, $teso_cuenta_bancaria_id);

        if ( (int)$user_id != 0 )
        {
            $user = self::obtenerUsuarioFiltro((int)$user_id, Auth::user()->empresa_id);
            if ( !is_null($user) ) {
                $query = self::aplicarFiltroCreadoPorUsuarioSeleccionado($query, $user, 'teso_movimientos.creado_por');
            }
        }

        $query = self::aplicarFiltroCreadoPor($query, 'teso_movimientos.creado_por');

        return $query->select(
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
                            'teso_movimientos.pdv_id',
                            'teso_movimientos.teso_cuenta_bancaria_id',
                            'teso_movimientos.creado_por',
                            'teso_movimientos.created_at',
                            'core_terceros.descripcion as tercero_descripcion'
                        )
                        ->orderBy('teso_movimientos.fecha')
                        ->orderBy('teso_movimientos.created_at')
                        ->orderBy('teso_movimientos.id')
                        ->get();
    }

    public static function usuario_tiene_restriccion_movimientos()
    {
        $user = Auth::user();

        if (is_null($user) || empty($user->email)) {
            return false;
        }

        return !self::usuarioTieneRolPrivilegiado($user, self::rolesSinFiltro());
    }

    public static function get_saldo_inicial2( $fecha_desde, $array_wheres, $user_id = 0, $pdv_id = 0, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0, $hora_desde = null )
    {
        $query = TesoMovimiento::where( $array_wheres );

        self::aplicarFiltroAntesDeFechaHora($query, $fecha_desde, $hora_desde);

        self::aplicarFiltroPdv($query, $pdv_id, $teso_caja_id, $teso_cuenta_bancaria_id);

        if ( (int)$user_id != 0 )
        {
            $user = self::obtenerUsuarioFiltro((int)$user_id, Auth::user()->empresa_id);
            if ( !is_null($user) ) {
                $query = self::aplicarFiltroCreadoPorUsuarioSeleccionado($query, $user, 'teso_movimientos.creado_por');
            }
        }

        $query = self::aplicarFiltroCreadoPor($query, 'teso_movimientos.creado_por');

        $saldo_inicial = $query->select( DB::raw('sum(valor_movimiento) as valor_movimiento') )
                            ->get()
                            ->first();

        if ( is_null( $saldo_inicial->valor_movimiento ) )
        {
            return 0;
        }

        return $saldo_inicial->valor_movimiento;
    }

    /**
     * Calcula el saldo de una caja justo antes del inicio de un arqueo.
     * Sin apertura toma los movimientos anteriores al día. Cuando el manejo
     * por horas está activo, incluye también los movimientos del día que sean
     * anteriores a la hora de apertura.
     */
    public static function calcularSaldoInicialArqueo($empresa_id, $teso_caja_id, $fecha, $fecha_hora_apertura = null)
    {
        $query = TesoMovimiento::where('teso_movimientos.core_empresa_id', (int)$empresa_id)
            ->where('teso_movimientos.teso_caja_id', (int)$teso_caja_id);

        $hora_apertura = null;
        if (self::usarMovimientosTesoreriaPorHora()) {
            $fecha_hora_apertura = self::normalizarFechaHoraFiltro($fecha_hora_apertura);
            if (!is_null($fecha_hora_apertura) && substr($fecha_hora_apertura, 0, 10) == $fecha) {
                $hora_apertura = substr($fecha_hora_apertura, 11, 8);
            }
        }

        self::aplicarFiltroAntesDeFechaHora($query, $fecha, $hora_apertura);

        return (float)$query->sum('teso_movimientos.valor_movimiento');
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
