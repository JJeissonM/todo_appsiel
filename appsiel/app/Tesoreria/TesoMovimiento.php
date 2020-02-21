<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoMovimiento extends Model
{
    //protected $table = 'teso_doc_registros_recaudos';

    protected $fillable = ['fecha', 'core_empresa_id', 'core_tercero_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'teso_motivo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'valor_movimiento', 'documento_soporte', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    public $encabezado_tabla = ['Fecha', 'Documento', 'Caja/Banco', 'Tercero', 'Motivo', 'Valor movimiento', 'Detalle', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS campo2';

        $registros = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_movimientos.teso_caja_id')
            ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_movimientos.teso_cuenta_bancaria_id')
            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
            ->where('teso_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select('teso_movimientos.fecha AS campo1', DB::raw($select_raw), DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion ) AS campo3'), 'core_terceros.descripcion AS campo4', 'teso_motivos.descripcion AS campo5', 'teso_movimientos.valor_movimiento AS campo6', 'teso_movimientos.descripcion AS campo7', 'teso_movimientos.id AS campo8')
            ->get()
            ->toArray();

        return $registros;
    }

    public static function consultar_registros2()
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_movimientos.consecutivo) AS campo2';

        $registros = TesoMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_movimientos.core_tipo_doc_app_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_movimientos.teso_caja_id')
            ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_movimientos.teso_cuenta_bancaria_id')
            ->leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_movimientos.core_tercero_id')
            ->where('teso_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select('teso_movimientos.fecha AS campo1', DB::raw($select_raw), DB::raw('CONCAT( teso_cajas.descripcion, " ", teso_cuentas_bancarias.descripcion ) AS campo3'), 'core_terceros.descripcion AS campo4', 'teso_motivos.descripcion AS campo5', 'teso_movimientos.valor_movimiento AS campo6', 'teso_movimientos.descripcion AS campo7', 'teso_movimientos.id AS campo8')
            ->orderBy('teso_movimientos.created_at', 'DESC')
            ->paginate(100);

        return $registros;
    }

    public static function movimiento_por_tipo_motivo($tipo_movimiento, $fecha_inicial, $fecha_final, $teso_caja_id = 0)
    {
        $operador = 'LIKE';
        if ($teso_caja_id != 0) {
            $operador = '=';
        }

        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->where('teso_motivos.movimiento', '=', $tipo_movimiento)
            ->where('teso_movimientos.fecha', '>=', $fecha_inicial)
            ->where('teso_movimientos.fecha', '<=', $fecha_final)
            ->where('teso_movimientos.teso_caja_id', $operador, (int) $teso_caja_id)
            ->groupBy('teso_movimientos.teso_motivo_id')
            ->select('teso_motivos.descripcion as motivo', 'teso_motivos.movimiento', DB::raw('sum(teso_movimientos.valor_movimiento) AS valor_movimiento'))
            ->get()
            ->toArray();
    }

    public static function get_suma_movimientos_menor_a_la_fecha($fecha)
    {
        return TesoMovimiento::leftJoin('teso_motivos', 'teso_motivos.id', '=', 'teso_movimientos.teso_motivo_id')
            ->where('teso_movimientos.fecha', '<', $fecha)
            ->sum('teso_movimientos.valor_movimiento');
    }
}
