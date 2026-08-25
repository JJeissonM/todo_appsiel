<?php

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Tesoreria\ArchivoTransmisionBancaria;
use App\Tesoreria\Services\DaviviendaMassPaymentFileService;
use App\Tesoreria\TesoDocEncabezado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DaviviendaPaymentFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download($id, DaviviendaMassPaymentFileService $service)
    {
        if (!Auth::user()->can('teso_pagos_cxp')) {
            abort(403, 'No tiene permiso para descargar archivos bancarios de Pagos de CxP.');
        }

        $payment = TesoDocEncabezado::where('id', (int) $id)
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->where('core_tipo_transaccion_id', 33)
            ->firstOrFail();

        try {
            $file = $service->generate($payment);

            ArchivoTransmisionBancaria::create([
                'core_empresa_id' => $payment->core_empresa_id,
                'teso_doc_encabezado_id' => $payment->id,
                'formato' => 'davivienda_pagos_masivos_' . strtolower($file['service_code']),
                'nombre_archivo' => $file['file_name'],
                'hash_sha256' => $file['hash'],
                'cantidad_registros' => $file['count'],
                'cantidad_omitidos' => $file['omitted_count'],
                'valor_total' => $file['total'],
                'generado_por' => Auth::user()->email
            ]);

            return response($file['content'], 200, [
                'Content-Type' => 'text/plain; charset=US-ASCII',
                'Content-Disposition' => 'attachment; filename="' . $file['file_name'] . '"',
                'Content-Length' => strlen($file['content']),
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff'
            ]);
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->with('mensaje_error', $exception->getMessage());
        } catch (\Exception $exception) {
            Log::error('Error al generar archivo plano Davivienda.', [
                'pago_cxp_id' => $payment->id,
                'usuario' => Auth::user()->email,
                'exception' => $exception
            ]);

            return redirect()->back()->with(
                'mensaje_error',
                'No fue posible generar el archivo Davivienda. No se descargó información parcial.'
            );
        }
    }
}
