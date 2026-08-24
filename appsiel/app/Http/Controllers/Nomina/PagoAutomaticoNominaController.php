<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomPagoAutomatico;
use App\Nomina\Services\PagoAutomaticoNominaService;
use App\Sistema\TipoTransaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class PagoAutomaticoNominaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function previsualizar(Request $request, PagoAutomaticoNominaService $service)
    {
        $this->autorizarProceso();

        $documento = NomDocEncabezado::where('id', (int) $request->nom_doc_encabezado_id)
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->first();

        if (is_null($documento)) {
            return response('<div class="alert alert-danger">El documento de nómina no existe o no pertenece a la empresa actual.</div>', 422);
        }

        if ($documento->estado !== NomDocEncabezado::ESTADO_CONTABILIZADO) {
            return response('<div class="alert alert-warning">El documento debe estar contabilizado antes de generar pagos.</div>', 422);
        }

        $lineas = $service->empleadosParaPrevisualizar($documento);
        $historial = NomPagoAutomatico::where('core_empresa_id', Auth::user()->empresa_id)
            ->where('nom_doc_encabezado_id', $documento->id)
            ->orderBy('id', 'DESC')
            ->take(10)
            ->get();
        $modeloPagoCxpId = $this->modeloPagoCxpId();

        return View::make('nomina.procesos.incluir.tabla_pagos_nomina', compact('documento', 'lineas', 'historial', 'modeloPagoCxpId'))->render();
    }

    public function pagar(Request $request, PagoAutomaticoNominaService $service)
    {
        $this->autorizarProceso();

        try {
            $terceros = $request->empleados;
            if (!is_array($terceros)) {
                $terceros = [];
            }

            $token = trim((string) $request->token_solicitud);
            if (strlen($token) < 20 || strlen($token) > 80) {
                throw new \InvalidArgumentException('La solicitud de pago no tiene un identificador de seguridad válido. Actualice la página.');
            }

            $proceso = $service->generar(
                (int) $request->nom_doc_encabezado_id,
                $terceros,
                trim((string) $request->fecha_pago),
                (int) $request->teso_medio_recaudo_id,
                (int) $request->teso_medio_recaudo_destino_id,
                $token
            );

            $pago = $proceso->documento_pago;
            $url = url('tesoreria/pagos_cxp/' . $pago->id . '?id=3&id_modelo=' . $this->modeloPagoCxpId() . '&id_transaccion=33');

            return response()->json([
                'ok' => true,
                'mensaje' => 'Se generó un pago de CxP por $' . number_format($proceso->valor_total, 2, ',', '.') . ' para ' . $proceso->cantidad_empleados . ' empleado(s).',
                'documento' => $pago->get_label_documento(),
                'url' => $url
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error al generar pago automático de nómina.', [
                'usuario' => Auth::user()->email,
                'documento_nomina_id' => $request->nom_doc_encabezado_id,
                'exception' => $e
            ]);
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se generó ningún pago. Ocurrió un error y toda la operación fue revertida.'
            ], 500);
        }
    }

    protected function autorizarProceso()
    {
        if (!Auth::user()->can('nomina.procesos.generar_pagos_nomina')) {
            abort(403, 'No tiene permiso para generar pagos automáticos de nómina.');
        }
    }

    protected function modeloPagoCxpId()
    {
        $transaccion = TipoTransaccion::find(PagoAutomaticoNominaService::TRANSACCION_PAGO_CXP);

        return is_null($transaccion) ? 0 : (int) $transaccion->core_modelo_id;
    }
}
