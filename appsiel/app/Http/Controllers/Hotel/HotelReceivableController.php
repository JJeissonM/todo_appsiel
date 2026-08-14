<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelStay;
use App\Hotel\Services\HotelReceivableService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\VentasPos\Services\FacturaPosService;
use App\VentasPos\Services\PosPaymentModalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelReceivableController extends Controller
{
    public function create($stayId)
    {
        $stay = $this->findStay($stayId);
        $service = new HotelReceivableService();
        $invoices = $service->pendingInvoices($stay);
        $advances = $service->availableAdvances($stay);
        $paymentData = $this->paymentData();
        $miga_pan = HotelBreadcrumb::make('App\\Hotel\\HotelStay', 'Pagar facturas crédito - Estadía #' . $stay->id);

        return view('hotel.stays.receivables_payment', compact('stay', 'invoices', 'advances', 'miga_pan') + $paymentData);
    }

    public function store(Request $request, $stayId)
    {
        $stay = $this->findStay($stayId);
        $invoiceIds = is_array($request->invoice_ids) ? $request->invoice_ids : array();
        $advanceIds = is_array($request->advance_ids) ? $request->advance_ids : array();
        $paymentMethods = json_decode($request->payment_methods, true);
        if (!is_array($paymentMethods)) {
            $paymentMethods = array();
        }

        try {
            $result = (new HotelReceivableService())->register($stay, $invoiceIds, $advanceIds, $paymentMethods);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage());
        }

        $message = 'Pago de facturas crédito registrado correctamente.';
        if (!empty($result['receipt_id'])) {
            $message .= ' Recibo de caja #' . $result['receipt_id'] . '.';
        }
        if (!empty($result['cross_document_id'])) {
            $message .= ' Cruce de anticipos #' . $result['cross_document_id'] . '.';
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', $message);
    }

    private function findStay($id)
    {
        return HotelStay::where('empresa_id', Auth::user()->empresa_id)
            ->where('id', $id)
            ->with('room', 'mainGuest.tercero')
            ->firstOrFail();
    }

    private function paymentData()
    {
        $modalService = new PosPaymentModalService();
        $data = $modalService->buildData();

        return array(
            'id_transaccion' => 8,
            'motivos' => (new FacturaPosService())->get_motivos_tesoreria(),
            'medios_recaudo' => $data['medios_recaudo'],
            'cajas' => $data['cajas'],
            'cuentas_bancarias' => $data['cuentas_bancarias'],
            'cuerpo_tabla_medios_recaudos' => '',
            'usar_modal_botones_medios_pago' => $data['usar_modal_botones'],
            'modal_botones_medios_pago_data' => $data['modal_botones_data'],
            'filtrar_destinos_por_medio_recaudo' => $data['filtrar_destinos_por_medio_recaudo'],
            'destinos_medios_recaudo_data' => $data['destinos_medios_recaudo_data'],
        );
    }
}
