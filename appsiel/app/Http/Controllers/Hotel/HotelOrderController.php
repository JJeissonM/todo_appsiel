<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Hotel\Services\HotelService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\Core\Services\ResolucionFacturacionService;
use App\Core\TipoDocApp;
use App\CxC\CxcMovimiento;
use App\Hotel\HotelRoom;
use App\Inventarios\InvProducto;
use App\Ventas\Cliente;
use App\VentasPos\Services\FacturaPosService;
use App\VentasPos\Services\PosPaymentModalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelOrderController extends Controller
{
    public function __construct()
    {
        HotelBreadcrumb::ensureContext('App\\Hotel\\HotelOrderHeader');
    }

    public function show($id)
    {
        $order = $this->findOrder($id);
        $products = $this->productsList();
        $miga_pan = HotelBreadcrumb::make('App\\Hotel\\HotelOrderHeader', 'Pedido ' . $order->document_number);
        $paymentData = $this->paymentData();
        $anticipos = $this->anticiposCliente($order);
        $electronicResolutionValidation = $this->electronicResolutionValidation();
        $canEditHotelOrderPrice = $this->canEditHotelOrderPrice();

        return view('hotel.orders.show', compact('order', 'products', 'anticipos', 'miga_pan', 'electronicResolutionValidation', 'canEditHotelOrderPrice') + $paymentData);
    }

    public function addLine(Request $request, $id)
    {
        $order = $this->findOrder($id);
        $this->validate($request, array(
            'producto_id' => 'required|exists:inv_productos,id',
            'quantity' => 'required|numeric|min:0.01',
        ));

        if ($this->canEditHotelOrderPrice() && $request->unit_price !== null && $request->unit_price !== '') {
            $this->validate($request, array('unit_price' => 'numeric|min:0'));
        }

        try {
            $service = new HotelService();
            $canEditPrice = $this->canEditHotelOrderPrice();
            DB::transaction(function () use ($order, $request, $service, $canEditPrice) {
                $data = $request->all();
                if (!$canEditPrice && isset($data['unit_price'])) {
                    unset($data['unit_price']);
                }

                $service->createLine($order, $data);
                $service->validateStockForOpenOrder($order);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id))->with('flash_message', 'Linea agregada correctamente.');
    }

    public function updateLine(Request $request, $id, $lineId)
    {
        $order = $this->findOrder($id);
        $line = $this->findLine($order, $lineId);

        $rules = array(
            'quantity' => 'required|numeric|min:0.01',
        );
        if ($this->canEditHotelOrderPrice()) {
            $rules['unit_price'] = 'required|numeric|min:0';
        }
        $this->validate($request, $rules);

        try {
            $service = new HotelService();
            $canEditPrice = $this->canEditHotelOrderPrice();
            DB::transaction(function () use ($order, $line, $request, $service, $canEditPrice) {
                $data = $request->all();
                if (!$canEditPrice && isset($data['unit_price'])) {
                    unset($data['unit_price']);
                }

                $service->updateLine($order, $line, $data);
                $service->validateStockForOpenOrder($order);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id))->with('flash_message', 'Linea actualizada correctamente.');
    }

    public function deleteLine($id, $lineId)
    {
        $order = $this->findOrder($id);
        $line = $this->findLine($order, $lineId);

        try {
            (new HotelService())->deleteLine($order, $line);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id))->with('flash_message', 'Linea eliminada correctamente.');
    }

    public function saveLines(Request $request, $id)
    {
        $order = $this->findOrder($id);
        $service = new HotelService();
        $lines = is_array($request->lines) ? $request->lines : array();
        $newLine = is_array($request->new_line) ? $request->new_line : array();
        $newLines = is_array($request->new_lines) ? $request->new_lines : array();
        $canEditPrice = $this->canEditHotelOrderPrice();

        if (!$order->canEditLines()) {
            return redirect()->back()->with('mensaje_error', 'El pedido no permite modificar lineas.');
        }

        try {
            DB::transaction(function () use ($order, $service, $lines, $newLine, $newLines, $canEditPrice) {
                foreach ($lines as $lineId => $lineData) {
                    $line = $this->findLine($order, $lineId);
                    if (!$canEditPrice && isset($lineData['unit_price'])) {
                        unset($lineData['unit_price']);
                    }

                    $this->validateLineData($lineData, $canEditPrice);
                    $service->updateLine($order, $line, $lineData);
                }

                if (isset($newLine['producto_id']) && (int)$newLine['producto_id'] > 0) {
                    $newLines[] = $newLine;
                }

                foreach ($newLines as $lineData) {
                    if (!isset($lineData['producto_id']) || (int)$lineData['producto_id'] <= 0) {
                        continue;
                    }

                    if (!$canEditPrice && isset($lineData['unit_price'])) {
                        unset($lineData['unit_price']);
                    }

                    $this->validateLineData($lineData, false);
                    $service->createLine($order, $lineData);
                }

                $service->validateStockForOpenOrder($order);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id))->with('flash_message', 'Pedido hotelero actualizado correctamente.');
    }

    private function canEditHotelOrderPrice()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if (method_exists($user, 'can')) {
            try {
                if ($user->can('editar_precio_total_en_linea_registro_factura_pos')) {
                    return true;
                }
            } catch (\Exception $e) {
                // Algunas instalaciones antiguas pueden no tener este permiso sembrado.
            }
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasRole('Admin Colegio')) {
                return true;
            }
        }

        return false;
    }

    public function generateStandardInvoice($id)
    {
        $order = $this->findOrder($id);

        try {
            $doc = (new HotelService())->generateStandardInvoice($order);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect('ventas/' . $doc->id . '?id=13&id_modelo=' . config('ventas.factura_ventas_modelo_id', 139) . '&id_transaccion=' . config('ventas.factura_ventas_tipo_transaccion_id', 23))->with('flash_message', 'Factura estandar generada correctamente.');
    }

    public function generatePosInvoice(Request $request, $id)
    {
        $order = $this->findOrder($id);
        $invoiceClienteId = $order->cliente_id;
        $invoiceDocumentType = $request->invoice_document_type == 'electronic' ? 'electronic' : 'pos';
        $convertToElectronic = $invoiceDocumentType == 'electronic';

        if ($convertToElectronic) {
            $resolutionValidation = $this->electronicResolutionValidation();
            if ($resolutionValidation->status == 'error') {
                return redirect()->back()->with('mensaje_error', $resolutionValidation->message);
            }
        }

        if ($request->invoice_customer_mode == 'other') {
            $this->validate($request, array(
                'invoice_cliente_id' => 'required|exists:vtas_clientes,id',
            ));

            $invoiceClienteId = (int)$request->invoice_cliente_id;
        }

        $invoiceCliente = Cliente::find($invoiceClienteId);
        if (is_null($invoiceCliente)) {
            return redirect()->back()->with('mensaje_error', 'El cliente seleccionado para facturar no existe.');
        }

        try {
            $doc = (new HotelService())->generatePosInvoice($order, $request->lineas_registros_medios_recaudos, $request->forma_pago, $request->object_anticipos, $invoiceClienteId, $convertToElectronic);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        if ($convertToElectronic && isset($doc->hotel_factura_electronica_url) && $doc->hotel_factura_electronica_url != '') {
            return redirect($doc->hotel_factura_electronica_url)->with('flash_message', 'Factura electronica generada correctamente desde pedido hotelero.');
        }

        return redirect('pos_factura/' . $doc->id . '?id=20&id_modelo=230&id_transaccion=47')->with('flash_message', 'Factura POS generada correctamente.');
    }

    private function findOrder($id)
    {
        return HotelOrderHeader::where('empresa_id', Auth::user()->empresa_id)
            ->where('id', $id)
            ->with('stay.room.bodega', 'cliente.tercero', 'lines.product', 'lines.bodega', 'posInvoice.tipo_documento_app', 'salesInvoice.tipo_documento_app')
            ->firstOrFail();
    }

    private function findLine(HotelOrderHeader $order, $lineId)
    {
        return HotelOrderLine::where('empresa_id', Auth::user()->empresa_id)->where('hotel_order_id', $order->id)->where('id', $lineId)->firstOrFail();
    }

    private function validateLineData($data, $requireUnitPrice)
    {
        $quantity = isset($data['quantity']) ? (float)$data['quantity'] : 0;
        if ($quantity <= 0) {
            throw new \Exception('La cantidad debe ser mayor a cero.');
        }

        if ($requireUnitPrice || (isset($data['unit_price']) && $data['unit_price'] !== '')) {
            if (!isset($data['unit_price']) || !is_numeric($data['unit_price']) || (float)$data['unit_price'] < 0) {
                throw new \Exception('El precio debe ser mayor o igual a cero.');
            }
        }

        if (isset($data['discount']) && $data['discount'] !== '' && (!is_numeric($data['discount']) || (float)$data['discount'] < 0)) {
            throw new \Exception('El descuento debe ser mayor o igual a cero.');
        }

        if (isset($data['producto_id']) && (int)$data['producto_id'] > 0 && is_null(InvProducto::find((int)$data['producto_id']))) {
            throw new \Exception('El producto no existe.');
        }
    }

    private function productsList()
    {
        $rows = InvProducto::where('core_empresa_id', Auth::user()->empresa_id)->where('estado', 'Activo')->orderBy('descripcion')->get();
        $options = array('' => '');
        foreach ($rows as $row) {

            if ( HotelRoom::where('inv_producto_id', $row->id)->exists() ) {
                continue; // Skip products that are linked to hotel rooms
            }
            
            $options[$row->id] = $row->id . ' - ' . $row->descripcion;
        }
        return $options;
    }

    private function paymentData()
    {
        $id_transaccion = 8;
        $motivos = (new FacturaPosService())->get_motivos_tesoreria();
        $paymentModalService = new PosPaymentModalService();
        $paymentModalData = $paymentModalService->buildData();

        return array(
            'id_transaccion' => $id_transaccion,
            'motivos' => $motivos,
            'medios_recaudo' => $paymentModalData['medios_recaudo'],
            'cajas' => $paymentModalData['cajas'],
            'cuentas_bancarias' => $paymentModalData['cuentas_bancarias'],
            'cuerpo_tabla_medios_recaudos' => '',
            'usar_modal_botones_medios_pago' => $paymentModalData['usar_modal_botones'],
            'modal_botones_medios_pago_data' => $paymentModalData['modal_botones_data'],
            'filtrar_destinos_por_medio_recaudo' => $paymentModalData['filtrar_destinos_por_medio_recaudo'],
            'destinos_medios_recaudo_data' => $paymentModalData['destinos_medios_recaudo_data'],
        );
    }

    private function anticiposCliente(HotelOrderHeader $order)
    {
        if (is_null($order->cliente) || empty($order->cliente->core_tercero_id)) {
            return array();
        }

        $rows = CxcMovimiento::get_documentos_tercero($order->cliente->core_tercero_id, date('Y-m-d'));
        $anticipos = array();
        foreach ($rows as $row) {
            if ((float)$row['saldo_pendiente'] < -0.1) {
                $anticipos[] = $row;
            }
        }

        return $anticipos;
    }

    private function electronicResolutionValidation()
    {
        if ((int)config('ventas_pos.modulo_fe_activo') != 1) {
            return (object)array(
                'status' => 'error',
                'message' => 'El modulo de facturacion electronica no esta activo. Active la facturacion electronica antes de generar facturas electronicas desde hotel.'
            );
        }

        $tipoDocApp = TipoDocApp::find((int)config('facturacion_electronica.document_type_id_default'));
        return (new ResolucionFacturacionService())->validate_resolucion_facturacion($tipoDocApp, Auth::user()->empresa_id);
    }
}
