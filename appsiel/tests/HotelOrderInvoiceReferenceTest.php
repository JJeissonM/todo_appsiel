<?php

use App\Hotel\HotelOrderHeader;

class HotelOrderInvoiceReferenceTest extends TestCase
{
    public function test_muestra_factura_pos_vinculada_aunque_el_tipo_de_factura_este_vacio()
    {
        $order = new HotelOrderHeader(array(
            'invoice_type' => null,
            'pos_doc_id' => 999999999,
        ));

        $this->assertSame('999999999', (string)$order->invoiceLabel());
        $this->assertContains('/pos_factura/999999999?', $order->invoiceUrl());
    }

    public function test_muestra_factura_estandar_vinculada_aunque_el_tipo_de_factura_este_vacio()
    {
        $order = new HotelOrderHeader(array(
            'invoice_type' => null,
            'sales_doc_id' => 999999998,
        ));

        $this->assertSame('999999998', (string)$order->invoiceLabel());
        $this->assertContains('/ventas/999999998?', $order->invoiceUrl());
    }
}
