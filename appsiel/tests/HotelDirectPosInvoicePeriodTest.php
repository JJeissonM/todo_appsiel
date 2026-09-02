<?php

use App\Hotel\HotelStay;
use App\Http\Controllers\Hotel\HotelStayController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class HotelDirectPosInvoicePeriodTest extends TestCase
{
    use DatabaseTransactions;

    public function test_separa_facturas_del_pedido_y_muestra_pos_directas_por_hora_real()
    {
        $client = DB::table('vtas_clientes')
            ->join('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->select('vtas_clientes.id', 'vtas_clientes.core_tercero_id', 'core_terceros.core_empresa_id')
            ->first();

        if (is_null($client)) {
            $this->markTestSkipped('La base de pruebas no tiene clientes para validar el flujo hotelero.');
        }

        $room = DB::table('hotel_rooms')->where('empresa_id', $client->core_empresa_id)->first();
        $referenceInvoice = DB::table('vtas_pos_doc_encabezados')->first();

        if (is_null($room) || is_null($referenceInvoice)) {
            $this->markTestSkipped('La base de pruebas no tiene los catálogos mínimos de hotel y POS.');
        }

        $stayId = DB::table('hotel_stays')->insertGetId(array(
            'empresa_id' => $client->core_empresa_id,
            'main_cliente_id' => $client->id,
            'room_id' => $room->id,
            'check_in_at' => '2035-01-01 00:30:00',
            'expected_check_out_at' => '2035-01-04 10:00:00',
            'check_out_at' => '2035-01-04 10:00:00',
            'adults_count' => 1,
            'children_count' => 0,
            'total_guests' => 1,
            'status' => HotelStay::STATUS_CERRADA,
            'created_at' => '2035-01-01 00:30:00',
            'updated_at' => '2035-01-04 10:00:00',
        ));
        DB::table('hotel_stay_guests')->insert(array(
            'empresa_id' => $client->core_empresa_id,
            'stay_id' => $stayId,
            'cliente_id' => $client->id,
            'is_main_guest' => 1,
            'created_at' => '2035-01-01 00:30:00',
            'updated_at' => '2035-01-01 00:30:00',
        ));

        $firstTurnId = $this->insertClosedTurn($referenceInvoice, $client, '2034-12-31', '2034-12-31 20:00:00', '2035-01-01 06:00:00', 'A');
        $secondTurnId = $this->insertClosedTurn($referenceInvoice, $client, '2035-01-01', '2035-01-01 20:00:00', '2035-01-02 06:00:00', 'B');
        $thirdTurnId = $this->insertClosedTurn($referenceInvoice, $client, '2035-01-02', '2035-01-02 20:00:00', '2035-01-03 06:00:00', 'C');
        $laterTurnId = $this->insertClosedTurn($referenceInvoice, $client, '2035-01-05', '2035-01-05 08:00:00', '2035-01-05 18:00:00', 'D');

        // La estadía abarca varios días y turnos. La primera factura conserva la
        // fecha operativa anterior, aunque su created_at está dentro de la estadía.
        $firstDirectInvoiceId = $this->insertPosInvoice($referenceInvoice, $client, 990001, '2035-01-01 01:00:00', '2034-12-31', $firstTurnId);
        $secondDirectInvoiceId = $this->insertPosInvoice($referenceInvoice, $client, 990002, '2035-01-02 01:00:00', '2035-01-01', $secondTurnId);
        $thirdDirectInvoiceId = $this->insertPosInvoice($referenceInvoice, $client, 990003, '2035-01-03 01:00:00', '2035-01-02', $thirdTurnId);
        $orderInvoiceId = $this->insertPosInvoice($referenceInvoice, $client, 990004, '2035-01-02 02:00:00', '2035-01-01', $secondTurnId);
        $laterInvoiceId = $this->insertPosInvoice($referenceInvoice, $client, 990005, '2035-01-05 10:00:00', '2035-01-05', $laterTurnId);

        $staleStayId = DB::table('hotel_stays')->insertGetId(array(
            'empresa_id' => $client->core_empresa_id,
            'main_cliente_id' => $client->id,
            'room_id' => $room->id,
            'check_in_at' => '2034-01-01 08:00:00',
            'expected_check_out_at' => '2034-01-02 10:00:00',
            'check_out_at' => '2034-01-02 10:00:00',
            'adults_count' => 1,
            'children_count' => 0,
            'total_guests' => 1,
            'status' => HotelStay::STATUS_CERRADA,
            'created_at' => '2034-01-01 08:00:00',
            'updated_at' => '2034-01-02 10:00:00',
        ));
        // Simula una relación antigua después de reutilizarse el ID de una
        // factura eliminada. No puede ocultar la nueva factura POS pagada.
        DB::table('hotel_order_headers')->insert(array(
            'empresa_id' => $client->core_empresa_id,
            'stay_id' => $staleStayId,
            'cliente_id' => $client->id,
            'pdv_id' => $referenceInvoice->pdv_id,
            'document_number' => 'HOT-STALE-POS-ID',
            'order_date' => '2034-01-01 08:30:00',
            'status' => 'FACTURADO',
            'invoice_type' => 'POS',
            'pos_doc_id' => $firstDirectInvoiceId,
            'created_at' => '2034-01-01 08:30:00',
            'updated_at' => '2034-01-02 09:00:00',
        ));
        DB::table('hotel_order_headers')->insert(array(
            'empresa_id' => $client->core_empresa_id,
            'stay_id' => $stayId,
            'cliente_id' => $client->id,
            'pdv_id' => $referenceInvoice->pdv_id,
            'document_number' => 'HOT-PERIOD-TEST',
            'order_date' => '2035-01-01 00:45:00',
            'status' => 'FACTURADO',
            'invoice_type' => 'POS',
            'pos_doc_id' => $orderInvoiceId,
            'created_at' => '2035-01-01 00:45:00',
            // El pedido se actualiza al persistir la factura generada.
            'updated_at' => '2035-01-02 02:01:00',
        ));

        $stay = HotelStay::with('mainGuest.tercero', 'guests.cliente.tercero')->find($stayId);
        $controller = new HotelStayController();
        $method = new ReflectionMethod($controller, 'directPosInvoicesForStayGuests');
        $method->setAccessible(true);
        $invoices = $method->invoke($controller, $stay);
        $invoiceIds = $invoices->pluck('id')->all();

        $this->assertContains($firstDirectInvoiceId, $invoiceIds);
        $this->assertSame(
            'PAGADO',
            $invoices->first(function ($key, $invoice) use ($firstDirectInvoiceId) {
                return (int)$invoice->id === (int)$firstDirectInvoiceId;
            })->hotel_status_label
        );
        $this->assertContains($secondDirectInvoiceId, $invoiceIds);
        $this->assertContains($thirdDirectInvoiceId, $invoiceIds);
        $this->assertNotContains($orderInvoiceId, $invoiceIds);
        $this->assertNotNull(DB::table('vtas_pos_doc_encabezados')->where('id', $laterInvoiceId)->first());
        $this->assertNotContains($laterInvoiceId, $invoiceIds);
    }

    private function insertPosInvoice($reference, $client, $consecutive, $createdAt, $operationalDate, $turnId)
    {
        return DB::table('vtas_pos_doc_encabezados')->insertGetId(array(
            'uniqid' => uniqid('hotel-pos-period-', true),
            'core_tipo_transaccion_id' => $reference->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $reference->core_tipo_doc_app_id,
            'consecutivo' => $consecutive,
            'fecha' => $operationalDate,
            'core_empresa_id' => $client->core_empresa_id,
            'core_tercero_id' => $client->core_tercero_id,
            'remision_doc_encabezado_id' => 0,
            'ventas_doc_relacionado_id' => 0,
            'cliente_id' => $client->id,
            'vendedor_id' => $reference->vendedor_id,
            'pdv_id' => $reference->pdv_id,
            'cajero_id' => $reference->cajero_id,
            'forma_pago' => 'contado',
            'fecha_entrega' => $createdAt,
            'fecha_vencimiento' => '2035-01-01',
            'lineas_registros_medios_recaudos' => '[]',
            'descripcion' => 'Factura POS directa de prueba hotelera',
            'valor_total' => 1000,
            'efectivo_recibido' => 1000,
            'total_efectivo_recibido' => 1000,
            'valor_ajuste_al_peso' => 0,
            'valor_total_bolsas' => 0,
            'valor_total_cambio' => 0,
            'estado' => 'Contabilizado',
            'creado_por' => $reference->creado_por,
            'modificado_por' => $reference->modificado_por,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'lote_acumulacion' => '',
            'turno_operativo_id' => $turnId,
        ));
    }

    private function insertClosedTurn($reference, $client, $operationalDate, $openedAt, $closedAt, $suffix)
    {
        return DB::table('core_turnos_operativos')->insertGetId(array(
            'core_empresa_id' => $client->core_empresa_id,
            'contexto_tipo' => 'pdv',
            'contexto_id' => $reference->pdv_id,
            'pdv_id' => $reference->pdv_id,
            'fecha_operativa' => $operationalDate,
            'abierto_en' => $openedAt,
            'cerrado_en' => $closedAt,
            'saldo_inicial' => 0,
            'saldo_cierre' => 0,
            'estado' => 'CERRADO',
            'codigo' => uniqid('HOTEL-MULTI-TURN-' . $suffix . '-', true),
            'created_at' => $openedAt,
            'updated_at' => $closedAt,
        ));
    }
}
