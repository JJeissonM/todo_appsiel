<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Hotel\Services\HotelService;
use App\Http\Controllers\Controller;
use App\Inventarios\InvProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelOrderController extends Controller
{
    public function show($id)
    {
        $order = $this->findOrder($id);
        $products = $this->productsList();
        $miga_pan = array(
            array('url' => 'hotel/stays', 'etiqueta' => 'Hotel'),
            array('url' => 'NO', 'etiqueta' => 'Pedido ' . $order->document_number),
        );

        return view('hotel.orders.show', compact('order', 'products', 'miga_pan'));
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

        return redirect('hotel/orders/' . $order->id)->with('flash_message', 'Linea agregada correctamente.');
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

        return redirect('hotel/orders/' . $order->id)->with('flash_message', 'Linea actualizada correctamente.');
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

        return redirect('hotel/orders/' . $order->id)->with('flash_message', 'Linea eliminada correctamente.');
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

    public function generatePosInvoice($id)
    {
        $order = $this->findOrder($id);

        try {
            $doc = (new HotelService())->generatePosInvoice($order);
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
}
