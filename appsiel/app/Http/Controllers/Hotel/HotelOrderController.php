<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Hotel\Services\HotelService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\CxC\CxcMovimiento;
use App\Inventarios\InvProducto;
use App\VentasPos\Services\FacturaPosService;
use App\VentasPos\Services\PosPaymentModalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('hotel.orders.show', compact('order', 'products', 'anticipos', 'miga_pan') + $paymentData);
    }

    public function addLine(Request $request, $id)
    {
        $order = $this->findOrder($id);
        $this->validate($request, array(
            'producto_id' => 'required|exists:inv_productos,id',
            'quantity' => 'required|numeric|min:0.01',
        ));

        if ($request->unit_price !== null && $request->unit_price !== '') {
            $this->validate($request, array('unit_price' => 'numeric|min:0'));
        }

        try {
            (new HotelService())->createLine($order, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id))->with('flash_message', 'Linea agregada correctamente.');
    }

    public function updateLine(Request $request, $id, $lineId)
    {
        $order = $this->findOrder($id);
        $line = $this->findLine($order, $lineId);

        $this->validate($request, array(
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ));

        try {
            (new HotelService())->updateLine($order, $line, $request->all());
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

        try {
            $doc = (new HotelService())->generatePosInvoice($order, $request->lineas_registros_medios_recaudos, $request->forma_pago, $request->object_anticipos);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect('pos_factura/' . $doc->id . '?id=20&id_modelo=230&id_transaccion=47')->with('flash_message', 'Factura POS generada correctamente.');
    }

    private function findOrder($id)
    {
        return HotelOrderHeader::where('empresa_id', Auth::user()->empresa_id)->where('id', $id)->with('stay.room', 'cliente.tercero', 'lines.product')->firstOrFail();
    }

    private function findLine(HotelOrderHeader $order, $lineId)
    {
        return HotelOrderLine::where('empresa_id', Auth::user()->empresa_id)->where('hotel_order_id', $order->id)->where('id', $lineId)->firstOrFail();
    }

    private function productsList()
    {
        $rows = InvProducto::where('core_empresa_id', Auth::user()->empresa_id)->where('estado', 'Activo')->orderBy('descripcion')->get();
        $options = array('' => '');
        foreach ($rows as $row) {
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
}
