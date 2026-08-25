<?php

namespace App\Tesoreria;

use App\Http\Controllers\Tesoreria\ArqueoCajaController;
use App\Core\Services\TurnoManager;
use App\Core\TurnoOperativo;
use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use App\Traits\FiltraRegistrosPorUsuario;
use App\VentasPos\Pdv;
use App\VentasPos\Services\CashRegisterShiftService;
use App\Traits\HasTurnoOperativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class ArqueoCaja extends Model
{
    use HasTurnoOperativo;
    const PERMISO_BLOQUEO_MOVIMIENTOS_SISTEMA = 'vtas_pos_bloqueo_ver_movimientos_sistema_en_arqueo_caja';
    const PERMISO_RECALCULAR_SALDO_INICIAL = 'teso_arqueo_caja_recalcular_saldo_inicial';

    use FiltraRegistrosPorUsuario;

    protected $table = 'teso_arqueos_caja';

    protected $fillable = [
        'fecha', 'core_empresa_id', 'teso_caja_id', 'pdv_id', 'turno_operativo_id', 'fecha_hora_apertura', 'fecha_hora_cierre', 'total_billetes', 'billetes_contados',
        'base', 'total_monedas', 'monedas_contadas', 'otros_saldos', 'detalle_otros_saldos', 'lbl_total_efectivo',
        'lbl_total_sistema', 'total_saldo', 'detalles_mov_entradas', 'total_mov_entradas', 'detalles_mov_salidas', 'total_mov_salidas', 'observaciones', 'estado', 'creado_por', 'modificado_por'
    ];

    protected function turnoModuleName()
    {
        return 'tesoreria';
    }

    /**
     * El arqueo es una operación de control sobre un turno ya identificado, no
     * una operación normal nueva. Por eso puede apuntar explícitamente al turno
     * cerrado/auditado, sin habilitar esa excepción para otros modelos.
     */
    public function allowsHistoricalTurnoAssignment()
    {
        return true;
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'No.', 'Caja', 'Observaciones', 'Total saldo', 'Estado'];

    public function caja()
    {
        return $this->belongsTo('App\Tesoreria\TesoCaja','teso_caja_id');
    }

    public static function usuario_tiene_bloqueo_movimientos_sistema($user = null)
    {
        $user = is_null($user) ? Auth::user() : $user;

        if (is_null($user) || !Schema::hasTable('permissions')) {
            return false;
        }

        $permission = Permission::where('name', self::PERMISO_BLOQUEO_MOVIMIENTOS_SISTEMA)->first();

        return !is_null($permission) && $user->hasPermissionTo($permission);
    }

    public static function usuario_puede_recalcular_saldo_inicial($user = null)
    {
        $user = is_null($user) ? Auth::user() : $user;

        if (is_null($user) || !Schema::hasTable('permissions')) {
            return false;
        }

        $permission = Permission::where('name', self::PERMISO_RECALCULAR_SALDO_INICIAL)->first();

        return !is_null($permission) && $user->hasPermissionTo($permission);
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $query = ArqueoCaja::leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_arqueos_caja.teso_caja_id')
            ->select(
                'teso_arqueos_caja.fecha AS campo1',
                'teso_arqueos_caja.id AS campo2',
                'teso_cajas.descripcion AS campo3',
                'teso_arqueos_caja.observaciones AS campo4',
                'teso_arqueos_caja.total_saldo AS campo5',
                'teso_arqueos_caja.estado AS campo6',
                'teso_arqueos_caja.id AS campo7'
            );

        $query = self::aplicarFiltroCreadoPor($query, 'teso_arqueos_caja.creado_por');

        $query->where(function($q) use ($search) {
            $q->where("teso_arqueos_caja.fecha", "LIKE", "%$search%")
                ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.observaciones", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.total_saldo", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.estado", "LIKE", "%$search%");
        });

        return $query
            ->orderBy('teso_arqueos_caja.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $query = ArqueoCaja::leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_arqueos_caja.teso_caja_id')
            ->select(
                'teso_arqueos_caja.fecha AS FECHA',
                'teso_arqueos_caja.id AS No.',
                'teso_cajas.descripcion AS CAJA',
                'teso_arqueos_caja.observaciones AS OBSERVACIONES',
                'teso_arqueos_caja.total_saldo AS TOTAL_SALDO',
                'teso_arqueos_caja.estado AS ESTADO'
            );

        $query = self::aplicarFiltroCreadoPor($query, 'teso_arqueos_caja.creado_por');

        $query->where(function($q) use ($search) {
            $q->where("teso_arqueos_caja.fecha", "LIKE", "%$search%")
                ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.observaciones", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.total_saldo", "LIKE", "%$search%")
                ->orWhere("teso_arqueos_caja.estado", "LIKE", "%$search%");
        });

        return self::reemplazarBindingsSql($query
            ->orderBy('teso_arqueos_caja.created_at', 'DESC')
            ->toSql(), $query->getBindings());
    }

    protected static function reemplazarBindingsSql($sql, $bindings)
    {
        foreach ($bindings as $binding) {
            if (is_numeric($binding)) {
                $value = $binding;
            } else {
                $value = "'" . str_replace("'", "''", $binding) . "'";
            }

            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ARQUEOS DE CAJA";
    }

    public static function opciones_campo_select()
    {
        $opciones = ArqueoCaja::where('teso_arqueos_caja.estado', 'Activo')
            ->select('teso_arqueos_caja.id', 'teso_arqueos_caja.detalle')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->detalle;
        }

        return $vec;
    }

    public function store_adicional($datos, $arqueocaja)
    {
        $datos = $this->applyStoredShiftData($datos, $arqueocaja);

        if ( self::usuario_tiene_bloqueo_movimientos_sistema() ) {
            $datos = $this->get_datos_adicionales( $datos );
            $arqueocaja->total_mov_entradas = $datos['total_mov_entradas'];
            $arqueocaja->total_mov_salidas = $datos['total_mov_salidas'];
            $arqueocaja->lbl_total_sistema = $datos['lbl_total_sistema'];
            $arqueocaja->total_saldo = $datos['total_saldo'];
        }

        $arqueocaja->billetes_contados = json_encode($datos['billetes']);
        $arqueocaja->monedas_contadas = json_encode($datos['monedas']);
        $arqueocaja->detalles_mov_entradas = $datos['movimientos_entradas'];
        $arqueocaja->detalles_mov_salidas = $datos['movimientos_salidas'];
        $arqueocaja->estado = 'ACTIVO';
        $result = $arqueocaja->save();
        if ($result) {
            return redirect('tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro CREADO correctamente.');
        } else {
            return redirect('tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro NO FUE CREADO correctamente.');
        }
    }

    public function validar_datos_creacion($request, $controller)
    {
        $this->validateAndNormalizeShift($request, $controller);
    }

    public function validar_datos_actualizacion($request, $controller, $id)
    {
        $this->validateAndNormalizeShift($request, $controller);
    }

    protected function validateAndNormalizeShift($request, $controller)
    {
        $empresaId = (int)Auth::user()->empresa_id;
        $pdvId = (int)$request->pdv_id;
        $turnoManager = app(TurnoManager::class);

        if ($pdvId > 0 && $turnoManager->enabledForPdv($empresaId, $pdvId, 'tesoreria')) {
            $turno = TurnoOperativo::where('id', (int)$request->turno_operativo_id)
                ->where('core_empresa_id', $empresaId)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdvId)
                ->first();

            if (is_null($turno)) {
                $controller->validate($request, array(
                    'turno_operativo_id' => 'required|integer|in:__turno_invalido__'
                ), array(
                    'turno_operativo_id.required' => 'Debe seleccionar el turno operativo que se va a arquear.',
                    'turno_operativo_id.in' => 'El turno seleccionado no existe o no corresponde a la empresa y PDV del arqueo.'
                ));
                return;
            }

            $request->merge(array(
                'turno_operativo_id' => $turno->id,
                'fecha' => $turno->fecha_operativa,
                'fecha_hora_apertura' => $this->turnoDateValue($turno->abierto_en),
                'fecha_hora_cierre' => $this->turnoDateValue($turno->cerrado_en),
                'base' => $turno->saldo_inicial
            ));
            return;
        }

        if ( !TesoMovimiento::usarMovimientosTesoreriaPorHora() ) {
            $request->merge([
                'fecha_hora_apertura' => null,
                'fecha_hora_cierre' => null
            ]);
            return;
        }

        $pdv = Pdv::where('id', (int)$request->pdv_id)
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->first();

        if (is_null($pdv)) {
            return;
        }

        try {
            $range = (new CashRegisterShiftService())->normalizeEditableRange(
                $request->fecha,
                $request->fecha_hora_apertura,
                $request->fecha_hora_cierre
            );
        } catch (\UnexpectedValueException $e) {
            $controller->validate($request, [
                'fecha_hora_apertura' => 'in:__invalid_range__'
            ], [
                'fecha_hora_apertura.in' => $e->getMessage()
            ]);
            return;
        }

        if (is_null($range)) {
            $request->merge([
                'fecha_hora_apertura' => null,
                'fecha_hora_cierre' => null
            ]);
            return;
        }

        $request->merge([
            'fecha_hora_apertura' => $range['opening_at'],
            'fecha_hora_cierre' => $range['closing_at']
        ]);
    }

    protected function turnoDateValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        return method_exists($value, 'format') ? $value->format('Y-m-d H:i:s') : substr((string)$value, 0, 19);
    }

    public function get_datos_adicionales( $datos )
    {
        $creado_por = isset($datos['creado_por']) ? $datos['creado_por'] : null;
        $pdv_id = isset($datos['pdv_id']) ? (int)$datos['pdv_id'] : 0;
        $fecha_hora_apertura = isset($datos['fecha_hora_apertura']) ? $datos['fecha_hora_apertura'] : null;
        $fecha_hora_cierre = isset($datos['fecha_hora_cierre']) ? $datos['fecha_hora_cierre'] : null;
        $turno_operativo_id = isset($datos['turno_operativo_id']) ? $datos['turno_operativo_id'] : null;

        /**
         * Entradas
         */
        $movimientos_caja = $this->get_movimientos_caja( 'entrada', $datos['fecha'], $datos['fecha'], $datos['teso_caja_id'], $creado_por, $pdv_id, $fecha_hora_apertura, $fecha_hora_cierre, $turno_operativo_id );

        $datos['movimientos_entradas'] = $this->get_string_movimientos( $movimientos_caja->toArray() );
        $datos['total_mov_entradas'] = $movimientos_caja->sum('valor_movimiento');
    
        /**
         * Salidas
         */
        $movimientos_caja = $this->get_movimientos_caja( 'salida', $datos['fecha'], $datos['fecha'], $datos['teso_caja_id'], $creado_por, $pdv_id, $fecha_hora_apertura, $fecha_hora_cierre, $turno_operativo_id );

        $datos['movimientos_salidas'] = $this->get_string_movimientos( $movimientos_caja->toArray() );
        $datos['total_mov_salidas'] = $movimientos_caja->sum('valor_movimiento') * -1;


        /**
         * Otros campos
         */
        $efectivo_base = (float)$datos['base'];
        if ( !(int)$datos['sumar_efectivo_base_en_saldo_esperado'] ) {
            $efectivo_base = 0;
        }

        $datos['lbl_total_sistema'] = $datos['total_mov_entradas'] + $efectivo_base - $datos['total_mov_salidas'];

        $datos['total_efectivo'] = $datos['total_billetes'] + $datos['total_monedas'] + $datos['otros_saldos'];

        $datos['total_saldo'] = $datos['total_efectivo'] - $datos['lbl_total_sistema'];

        return $datos;
    }

    public function get_string_movimientos( $movimientos )
    {
        $string_movimientos = '[';

        $es_el_primero = true;
        foreach($movimientos as $linea)
        {
            if ( $es_el_primero ) {
                $string_movimientos .= '{"motivo":"' . $linea['motivo'] . '","movimiento":"' . $linea['movimiento'] . '","codigo_referencia_tercero":"","valor_movimiento":' . $linea['valor_movimiento'] . '}';
                $es_el_primero = false;
            }else{
                $string_movimientos .= ',{"motivo":"' . $linea['motivo'] . '","movimiento":"' . $linea['movimiento'] . '","codigo_referencia_tercero":"","valor_movimiento":' . $linea['valor_movimiento'] . '}';
            }
        }

        $string_movimientos .= ']';

        return $string_movimientos;
    }

    public function get_movimientos_caja( $movimiento, $fecha_desde, $fecha_hasta, $teso_caja_id, $creado_por = null, $pdv_id = 0, $fecha_hora_apertura = null, $fecha_hora_cierre = null, $turno_operativo_id = null )
    {
        if ( !TesoMovimiento::usarMovimientosTesoreriaPorHora() ) {
            $fecha_hora_apertura = null;
            $fecha_hora_cierre = null;
        }

        if (!is_null($fecha_hora_apertura) && $fecha_hora_apertura != '' && substr($fecha_hora_apertura, 0, 10) != $fecha_desde) {
            $fecha_hora_apertura = null;
            $fecha_hora_cierre = null;
        }

        if ( !is_null($fecha_hora_apertura) && $fecha_hora_apertura != '' ) {
            $fecha_desde = substr($fecha_hora_apertura, 0, 10);
        }

        if ( !is_null($fecha_hora_cierre) && $fecha_hora_cierre != '' ) {
            $fecha_hasta = substr($fecha_hora_cierre, 0, 10);
        }

        return TesoMovimiento::movimiento_por_tipo_motivo( $movimiento, $fecha_desde, $fecha_hasta, $teso_caja_id, $creado_por, $pdv_id, $fecha_hora_apertura, $fecha_hora_cierre, $turno_operativo_id );
    }

    public function update_adicional($datos, $doc_encabezado_id)
    {
        $arqueocaja = ArqueoCaja::find($doc_encabezado_id);
        $datos = $this->applyStoredShiftData($datos, $arqueocaja);

        if ( self::usuario_tiene_bloqueo_movimientos_sistema() ) {
            $datos = $this->get_datos_adicionales( $datos );
            $arqueocaja->total_mov_entradas = $datos['total_mov_entradas'];
            $arqueocaja->total_mov_salidas = $datos['total_mov_salidas'];
            $arqueocaja->lbl_total_sistema = $datos['lbl_total_sistema'];
            $arqueocaja->total_saldo = $datos['total_saldo'];
        }

        $arqueocaja->billetes_contados = json_encode($datos['billetes']);
        $arqueocaja->monedas_contadas = json_encode($datos['monedas']);
        $arqueocaja->detalles_mov_entradas = $datos['movimientos_entradas'];
        $arqueocaja->detalles_mov_salidas = $datos['movimientos_salidas'];
        $arqueocaja->estado = 'ACTIVO';
        $arqueocaja->save();

        return 'tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'];
    }

    protected function applyStoredShiftData(array $data, ArqueoCaja $cashCount)
    {
        foreach (['fecha', 'pdv_id', 'teso_caja_id', 'base', 'turno_operativo_id', 'fecha_hora_apertura', 'fecha_hora_cierre'] as $field) {
            $data[$field] = $cashCount->{$field};
        }

        return $data;
    }
}
