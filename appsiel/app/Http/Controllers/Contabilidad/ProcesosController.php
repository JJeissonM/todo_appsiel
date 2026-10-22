<?php

namespace App\Http\Controllers\Contabilidad;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabPeriodoEjercicio;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ProcesosController extends Controller
{
    public function generar_listado_cierre_ejercicio(Request $request)
    {
        $periodo_ejercicio = $this->obtener_periodo_ejercicio($request, 'periodo_ejercicio_id');
        $cuenta_ganancias_perdidas_ejercicio = $this->obtener_cuenta_cierre();
        $lista_movimientos = $this->obtener_movimientos_resultados($periodo_ejercicio);

        return View::make(
            'contabilidad.procesos.cierre_ejercicio_tabla_saldos_cuentas_resultados',
            compact('lista_movimientos', 'cuenta_ganancias_perdidas_ejercicio', 'periodo_ejercicio')
        )->render();
    }

    public function crear_nota_cierre_ejercicio(Request $request)
    {
        $periodo_ejercicio = $this->obtener_periodo_ejercicio($request, 'periodo_ejercicio_id2');
        $cuenta_ganancias_perdidas_ejercicio_id = $this->obtener_cuenta_cierre()->id;
        $lista_movimientos = $this->obtener_movimientos_resultados($periodo_ejercicio);
        $tabla_registros_documento = [];
        $valor_total = 0;

        foreach ($lista_movimientos as $mov_cuenta) {
            $saldo = $mov_cuenta->valor_saldo;
            if ($saldo == 0) {
                continue;
            }

            // Cancelar el saldo de la cuenta y registrar su contrapartida.
            $debito = max(-$saldo, 0);
            $credito = max($saldo, 0);
            $tabla_registros_documento[] = $this->crear_linea_cierre(
                $periodo_ejercicio->fecha_hasta, $mov_cuenta->contab_cuenta_id, $debito, $credito
            );
            $tabla_registros_documento[] = $this->crear_linea_cierre(
                $periodo_ejercicio->fecha_hasta, $cuenta_ganancias_perdidas_ejercicio_id, $credito, $debito
            );
            $valor_total += $saldo;
        }

        if (empty($tabla_registros_documento)) {
            return redirect()->back()->with('mensaje_error', 'No hay saldos de cuentas de resultado para cerrar.');
        }

        // El guardado existente descarta las dos últimas filas de la tabla.
        $tabla_registros_documento[] = (object) [
            'fecha_vencimiento' => '', 'documento_soporte_tercero' => '',
            'tipo_transaccion' => 'NA', 'Cuenta' => 'NA', 'Tercero' => 'NA',
            'Detalle' => 'NA', 'debito' => 'NA', 'credito' => 'NA'
        ];
        $tabla_registros_documento[] = (object) [
            'fecha_vencimiento' => '', 'documento_soporte_tercero' => '',
            'tipo_transaccion' => '', 'Cuenta' => 'NA', 'Tercero' => 'NA', 'Detalle' => 'NA'
        ];

        $usuario = Auth::user();
        $request->merge([
            'tabla_registros_documento' => json_encode($tabla_registros_documento),
            'core_empresa_id' => $usuario->empresa_id,
            'core_tipo_doc_app_id' => (int) config('contabilidad.tipo_documento_cierre_ejercicio'),
            'fecha' => $periodo_ejercicio->fecha_hasta,
            'core_tercero_id_aux' => " NOMBRE_TERCERO ",
            'core_tercero_id' => (int) config('contabilidad.tercero_default_cierre_ejercicio'),
            'documento_soporte' => "",
            'descripcion' => "Cierre del ejercicio contable " . $periodo_ejercicio->descripcion . '. Cancelación cuentas de resultado.',
            'creado_por' => $usuario->email,
            'estado' => "Activo",
            'consecutivo' => "",
            'core_tipo_transaccion_id' => (int) config('contabilidad.transaccion_default_cierre_ejercicio'),
            'valor_total' => abs($valor_total),
            'modificado_por' => "0",
            'url_id' => "14",
            'url_id_modelo' => "47",
            'url_id_transaccion' => (int) config('contabilidad.transaccion_default_cierre_ejercicio'),
            'inv_bodega_id_aux' => "",
        ]);

        $registro_encabezado_doc = DB::transaction(function () use ($request, $tabla_registros_documento) {
            $contab_controller = app(ContabilidadController::class);
            $encabezado = $contab_controller->crear_encabezado_documento($request, $request->url_id_modelo);
            $contab_controller->almacenar_lineas_registros($request, $tabla_registros_documento, $encabezado);

            return $encabezado;
        });

        return redirect('contabilidad/' . $registro_encabezado_doc->id . '?' . http_build_query([
            'id' => $request->url_id,
            'id_modelo' => $request->url_id_modelo,
            'id_transaccion' => $request->url_id_transaccion
        ]));
    }

    private function obtener_periodo_ejercicio(Request $request, $campo)
    {
        $this->validate($request, [$campo => 'required|integer|min:1']);

        return ContabPeriodoEjercicio::where('core_empresa_id', Auth::user()->empresa_id)
            ->findOrFail($request->input($campo));
    }

    private function obtener_cuenta_cierre()
    {
        return ContabCuenta::findOrFail((int) config('contabilidad.cuenta_ganancias_perdidas_ejercicio'));
    }

    private function obtener_movimientos_resultados(ContabPeriodoEjercicio $periodo)
    {
        return ContabMovimiento::with('cuenta')
            ->join('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->whereBetween('contab_movimientos.fecha', [$periodo->fecha_desde, $periodo->fecha_hasta])
            ->whereIn('contab_cuentas.contab_cuenta_clase_id', [4, 5, 6, 7])
            ->selectRaw('contab_movimientos.contab_cuenta_id, SUM(contab_movimientos.valor_saldo) AS valor_saldo')
            ->groupBy('contab_movimientos.contab_cuenta_id', 'contab_cuentas.contab_cuenta_clase_id')
            ->orderBy('contab_cuentas.contab_cuenta_clase_id')
            ->orderBy('contab_movimientos.contab_cuenta_id')
            ->get();
    }

    private function crear_linea_cierre($fecha, $cuenta_id, $debito, $credito)
    {
        return (object) [
            'fecha_vencimiento' => $fecha,
            'documento_soporte_tercero' => '',
            'tipo_transaccion' => 'causacion',
            'Cuenta' => $cuenta_id . '- COD_CUENTA DESCRIPCION_CUENTA',
            'Tercero' => '-',
            'Detalle' => '',
            'debito' => '$  ' . $debito,
            'credito' => '$  ' . $credito
        ];
    }
}
