<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

use App\Http\Controllers\Core\TransaccionController;

use App\VentasPos\Services\AccumulationService;

use App\VentasPos\FacturaPos;

use App\Ventas\VtasPedido;

use App\Ventas\VtasDocEncabezado;

use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\VentasPos\Services\CxCService;
use App\VentasPos\Services\InvoicingService;
use App\VentasPos\Services\TreasuryService;

class FacturaElectronicaController extends TransaccionController
{
    protected $doc_encabezado;

    /**
     * ALMACENA FACTURA ELECTRONICA DESDE VENTAS POS - ES LLAMADO VÍA AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $invoice_service = new InvoicingService();

        if (!isset($request['creado_por'])) {
            $request['creado_por'] = Auth::user()->email;
        }

        $request['estado'] = 'Pendiente';

        $crear_cruce_con_anticipos = false;
        $crear_abonos = false; // Cuando es credito y se ingresa alguna línea de Medio de pago
        if ($request->object_anticipos != 'null' && $request->object_anticipos != '') {
            $request['forma_pago'] = 'credito'; // Si hay anticipos, se asume que es crédito

            $crear_cruce_con_anticipos = true; // Si hay anticipos, se crea el cruce con los anticipos
            $crear_abonos = true; // Si hay anticipos, se crean los abonos
        }
        $todos_los_pedidos = collect([]);

        DB::beginTransaction();
        try {
            if ((int)$request->pedido_id != 0) {
                $pedido = VtasPedido::where('id', (int)$request->pedido_id)->lockForUpdate()->first();

                if (is_null($pedido) || $pedido->estado != 'Pendiente' || (int)$pedido->ventas_doc_relacionado_id != 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'El pedido seleccionado ya fue facturado o no está disponible. Actualice la lista de pendientes.'
                    ], 409);
                }

                if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                    $todos_los_pedidos = VtasPedido::where('cliente_id', $pedido->cliente_id)
                        ->where('estado', 'Pendiente')
                        ->where('ventas_doc_relacionado_id', 0)
                        ->whereIn('core_tipo_transaccion_id', [42, 60])
                        ->lockForUpdate()
                        ->get();

                    if ($todos_los_pedidos->isEmpty()) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Los pedidos de esta mesa/cliente ya no están disponibles para facturar.'
                        ], 409);
                    }
                } else {
                    $todos_los_pedidos = collect([$pedido]);
                }
            }

            $factura_pos_encabezado = $invoice_service->almacenar_factura_pos($request); // Con su Remision

            if ($request->pedido_id != 0) {
                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->ventas_doc_relacionado_id = $factura_pos_encabezado->id;
                    $un_pedido->estado = 'Facturado';
                    $this->guardar_pedido_sin_tocar_updated_at($un_pedido);

                    self::actualizar_cantidades_pendientes($un_pedido, 'restar');
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($this->es_error_uniqid_duplicado($e)) {
                $request_id = (string)$request->input('request_id', '');
                $request_uniqid = (string)$request->input('uniqid', '');
                Log::warning('POS_FE_DUPLICATE_UNIQID', [
                    'uniqid' => $request_uniqid,
                    'request_id' => $request_id
                ]);

                $factura_pos_existente = $this->find_existing_pos_invoice_by_uniqid($request_uniqid, $request);
                if (!is_null($factura_pos_existente)) {
                    $factura_electronica_existente = Factura::where('ventas_doc_relacionado_id', (int)$factura_pos_existente->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!is_null($factura_electronica_existente)) {
                        Log::warning('POS_FE_DUPLICATE_RECOVERED_PRINT_URL', [
                            'request_id' => $request_id,
                            'uniqid' => $request_uniqid,
                            'factura_pos_id' => (int)$factura_pos_existente->id,
                            'factura_electronica_id' => (int)$factura_electronica_existente->id
                        ]);

                        return $this->build_url_print_fe((int)$factura_electronica_existente->id);
                    }
                }

                return response()->json([
                    'status' => 'warning',
                    'message' => 'Se detectó un intento de guardado repetido. Verifique en historial si la factura ya fue creada y presione REFRESH para continuar.',
                    'request_id' => $request_id
                ], 409);
            }
            throw $e;
        }

        // Acumular la factura
        $obj_acumm_serv = new AccumulationService($factura_pos_encabezado->pdv_id);

        // Realizar preparaciones de recetas
        $obj_acumm_serv->hacer_preparaciones_recetas('Creado por factura POS ' . $factura_pos_encabezado->get_label_documento(), $factura_pos_encabezado->fecha);

        // Realizar desarme automático
        $obj_acumm_serv->hacer_desarme_automatico('Creado por factura POS ' . $factura_pos_encabezado->get_label_documento(), $factura_pos_encabezado->fecha);

        $obj_acumm_serv->accumulate_one_invoice($factura_pos_encabezado->id);

        // Convertir a factura electrónica
        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice($factura_pos_encabezado->id);
        if ($result->status == 'mensaje_error' || empty($result->new_document_header_id)) {
            return response()->json([
                'status' => 'error',
                'message' => $result->message
            ], 422);
        }

        // Enviar al proveedor tecnológico (validación previa + envío)
        $vtas_document_header = Factura::find((int)$result->new_document_header_id);
        if (is_null($vtas_document_header)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener la Factura Electrónica convertida.'
            ], 422);
        }

        $mensaje = $this->enviar_con_validacion_previa($vtas_document_header);

        if ($mensaje->tipo != 'mensaje_error') {
            $factura_pos_encabezado->estado = 'Enviada';
            $factura_pos_encabezado->save();

            $vtas_document_header->estado = 'Enviada';
            $vtas_document_header->save();
        }

        if ($crear_cruce_con_anticipos) {
            (new CxCService())->crear_cruce_con_anticipos($vtas_document_header, $request->object_anticipos);
        }

        if ($crear_abonos) {
            $datos = $factura_pos_encabezado->toArray();
            (new TreasuryService())->crear_abonos_documento($vtas_document_header, $datos['lineas_registros_medios_recaudos']);
        }

        $url_print = $this->build_url_print_fe((int)$result->new_document_header_id);

        if ($mensaje->tipo == 'mensaje_error') {
            return response()->json([
                'status' => 'warning',
                'message' => $mensaje->contenido,
                'url_print' => $url_print
            ], 200);
        }

        return $url_print;
    }

    /**
     * En uso
     */
    public static function actualizar_cantidades_pendientes($encabezado_pedido, $operacion)
    {
        $lineas_registros_pedido = $encabezado_pedido->lineas_registros;
        foreach ($lineas_registros_pedido as $linea_pedido) {
            if ($operacion == 'restar') {
                $linea_pedido->cantidad_pendiente = 0;
            } else {
                // sumar: al anular
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad;
            }

            $linea_pedido->save();
        }
    }

    /**
     * En uso
     */
    public function get_todos_los_pedidos_mesero_para_la_mesa($pedido)
    {
        return VtasPedido::where(
            [
                ['cliente_id', '=', $pedido->cliente_id],
                ['estado', '=', 'Pendiente']
            ]
        )
            ->where('ventas_doc_relacionado_id', 0)
            ->whereIn('core_tipo_transaccion_id', [42, 60])
            ->get();
    }

    protected function guardar_pedido_sin_tocar_updated_at($pedido)
    {
        $pedido->timestamps = false;
        $pedido->save();
        $pedido->timestamps = true;
    }

    public function convertir_en_factura_electronica($factura_pos_encabezado_id)
    {
        $factura_pos_encabezado = FacturaPos::find($factura_pos_encabezado_id);
        if (is_null($factura_pos_encabezado)) {
            return '';
        }

        if ($factura_pos_encabezado->cliente->tercero->tipo == 'Interno') {
            return url('/') . '/vtas_imprimir/' . $factura_pos_encabezado_id . '?id=20&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';
        }

        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice($factura_pos_encabezado->id);
        if ($result->status == 'mensaje_error' || empty($result->new_document_header_id)) {
            return url('/') . '/pos_factura/' . $factura_pos_encabezado->id . '?id=20&id_modelo=230&id_transaccion=47';
        }

        $factura_electronica = Factura::find((int)$result->new_document_header_id);
        if (is_null($factura_electronica)) {
            return url('/') . '/pos_factura/' . $factura_pos_encabezado->id . '?id=20&id_modelo=230&id_transaccion=47';
        }

        $mensaje = $this->enviar_con_validacion_previa($factura_electronica);

        if ($mensaje->tipo != 'mensaje_error') {
            $factura_pos_encabezado->estado = 'Enviada';
            $factura_pos_encabezado->save();

            $vtas_document_header = VtasDocEncabezado::find((int)$result->new_document_header_id);
            if (!is_null($vtas_document_header)) {
                $vtas_document_header->estado = 'Enviada';
                $vtas_document_header->save();
            }
        }

        $url_print = url('/') . '/vtas_imprimir/' . $result->new_document_header_id . '?id=21&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';

        return $url_print;
    }

    protected function enviar_con_validacion_previa(Factura $vtas_document_header)
    {
        // 1) El proveedor valida si ya existe el documento.
        $mensaje_validacion = $this->normalizar_mensaje($vtas_document_header->enviar_al_proveedor_tecnologico());

        // 2) Si no está enviado, intenta el envío definitivo.
        $mensaje_envio = $this->normalizar_mensaje($vtas_document_header->enviar_al_proveedor_tecnologico());

        // Si alguno fue exitoso, preferir éxito para mantener el flujo actual.
        if ($mensaje_validacion->tipo != 'mensaje_error') {
            return $mensaje_validacion;
        }

        if ($mensaje_envio->tipo != 'mensaje_error') {
            return $mensaje_envio;
        }

        // Ambos fallaron: prioriza el mensaje con más detalle.
        $contenido_validacion = trim((string)$mensaje_validacion->contenido);
        $contenido_envio = trim((string)$mensaje_envio->contenido);

        if (strlen($contenido_envio) > strlen($contenido_validacion)) {
            return $mensaje_envio;
        }

        return $mensaje_validacion;
    }

    protected function normalizar_mensaje($mensaje)
    {
        if (is_array($mensaje)) {
            $mensaje = (object)$mensaje;
        }

        if (!is_object($mensaje)) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'Respuesta no válida del proveedor tecnológico.'
            ];
        }

        if (!property_exists($mensaje, 'tipo')) {
            $mensaje->tipo = 'mensaje_error';
        }

        if (!property_exists($mensaje, 'contenido')) {
            $mensaje->contenido = '';
        }

        $contenido = trim((string)$mensaje->contenido);
        if ($mensaje->tipo == 'mensaje_error' && ($contenido == '' || $contenido == 'Error de Empresa:' || $contenido == 'Error de Empresa')) {
            $mensaje->contenido = 'Error de Empresa: no fue posible enviar la factura electronica. Revise configuracion FE de la empresa (NIT, software, resolucion y proveedor).';
        }

        if ($mensaje->tipo == 'mensaje_error') {
            Log::warning('POS_FE_SEND_ERROR', [
                'factura_id' => isset($mensaje->factura_id) ? $mensaje->factura_id : null,
                'contenido' => (string)$mensaje->contenido
            ]);
        }

        return $mensaje;
    }

    protected function es_error_uniqid_duplicado(\Throwable $e)
    {
        if (!($e instanceof QueryException)) {
            return false;
        }

        $error_info = $e->errorInfo;
        $mysql_error_code = isset($error_info[1]) ? (int)$error_info[1] : 0;
        if ($mysql_error_code !== 1062) {
            return false;
        }

        $message = (string)$e->getMessage();
        return (stripos($message, 'for key \'uniqid\'') !== false || stripos($message, 'for key `uniqid`') !== false);
    }

    protected function find_existing_pos_invoice_by_uniqid($uniqid, Request $request = null)
    {
        if (trim((string)$uniqid) == '') {
            return null;
        }

        $query = FacturaPos::where('uniqid', $uniqid);
        if (!is_null($request)) {
            $core_empresa_id = (int)$request->get('core_empresa_id');
            $cajero_id = (int)$request->get('cajero_id');
            $pdv_id = (int)$request->get('pdv_id');

            if ($core_empresa_id > 0) {
                $query->where('core_empresa_id', $core_empresa_id);
            }

            if ($cajero_id > 0) {
                $query->where('cajero_id', $cajero_id);
            }

            if ($pdv_id > 0) {
                $query->where('pdv_id', $pdv_id);
            }
        }

        return $query->first();
    }

    protected function build_url_print_fe($factura_electronica_id)
    {
        return url('/') . '/vtas_imprimir/' . (int)$factura_electronica_id . '?id=21&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';
    }
}
