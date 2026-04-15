<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Compras\Services\SyncFacturaCompraService;
use App\Core\Empresa;
use App\Core\Tercero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncFacturaCompraController extends Controller
{
    /**
     * Recibe el JSON de OSEI (BOT DIAN) y sincroniza las facturas de compra.
     *
     * POST /api/compras/sync-facturas
     *
     * La empresa destino se resuelve usando customer.identification_number del primer
     * ítem del JSON, que es el NIT del cliente (la empresa que usa Appsiel).
     * Así un solo endpoint sirve para cualquier empresa registrada en el sistema.
     */
    
    public function store(Request $request, SyncFacturaCompraService $service): JsonResponse
    {
        // ── Validación mínima del payload ─────────────────────────
        $this->validate($request, [
            'data'                                                        => 'required|array|min:1',
            'data.*.cufe'                                                 => 'required|string',
            'data.*.invoice_data.invoice'                                 => 'required|array',
            'data.*.invoice_data.invoice.supplier'                        => 'required|array',
            'data.*.invoice_data.invoice.supplier.identification_number'  => 'required|string',
            'data.*.invoice_data.invoice.customer'                        => 'required|array',
            'data.*.invoice_data.invoice.customer.identification_number'  => 'required|string',
            'data.*.invoice_data.invoice.items'                           => 'required|array|min:1',
            'data.*.invoice_data.invoice.issue_date'                      => 'required|string',
        ]);

        // ── Validación de Token (Fase 1) ─────────────────
        $incoming_token = $request->input('data.0.invoice_data.invoice.authorization_token');
        $expected_token = config('facturacion_electronica.tokenEmpresa');

        if (!empty($expected_token) && $incoming_token !== $expected_token) {
            return response()->json(['error' => 'No Autorizado: El Token de Autorización de la Empresa no coincide.'], 401);
        }

        // ── Resolver empresa por NIT del customer ─────────────────
        // El JSON incluye customer.identification_number = NIT de la empresa en Appsiel.
        // Todos los ítems del array data[] tienen el mismo customer (misma empresa destino).
        $nit_cliente = $request->input('data.0.invoice_data.invoice.customer.identification_number');

        // Estrategia 1: buscar en core_empresas directamente si tiene columna nit/numero_identificacion
        $empresa = Empresa::where('numero_identificacion', $nit_cliente)->first();

        // Estrategia 2: buscar via core_terceros → core_empresas por core_tercero_id
        if (!$empresa) {
            $tercero = Tercero::where('numero_identificacion', $nit_cliente)->first();
            if ($tercero) {
                $empresa = Empresa::find($tercero->core_empresa_id);
            }
        }

        // Estrategia 3: fallback a la primera empresa del sistema
        if (!$empresa) {
            $empresa = Empresa::first();
        }

        if (!$empresa) {
            return response()->json(['error' => 'No se encontró una empresa configurada en Appsiel.'], 500);
        }

        // ── Sincronizar ───────────────────────────────────────────
        $resultado = $service->sincronizar($request->all(), $empresa->id, 'bot_osei');

        return response()->json($resultado, 200);
    }
}