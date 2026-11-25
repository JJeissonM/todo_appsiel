<?php
// Prueba aislada sin Laravel ni conexiones: php tests/integration/pos_invoice_charges_legacy.php
namespace App\VentasPos {
    class FacturaPos {
        public static $rows = [];
        public static function where($key, $value) { return (new Query)->where($key, $value); }
    }
    class Query {
        private $filters = [];
        private $limit = 100;
        public function where($key, $value) { $this->filters[$key] = $value; return $this; }
        public function take($limit) { $this->limit = $limit; return $this; }
        public function first() { return $this->get()->first(); }
        public function get() {
            $rows = array_filter(FacturaPos::$rows, function ($row) {
                foreach ($this->filters as $key => $value) {
                    if ($row->$key != $value) return false;
                }
                return true;
            });
            return new Collection(array_slice(array_values($rows), 0, $this->limit));
        }
    }
    class Collection {
        private $rows;
        public function __construct($rows) { $this->rows = $rows; }
        public function count() { return count($this->rows); }
        public function first() { return isset($this->rows[0]) ? $this->rows[0] : null; }
    }
}
namespace {
    function config($key) { return $key === 'facturacion_electronica.proveedor_tecnologico_default' ? 'OSEI' : 0; }
    $root = __DIR__ . '/../../app/';
    require $root . 'FacturacionElectronica/Services/PosInvoiceChargesService.php';
    require $root . 'VentasPos/Services/TipService.php';
    require $root . 'VentasPos/Services/DatafonoService.php';
    require $root . 'VentasPos/Services/PaymentReconciliationService.php';
    function check($ok, $label) { if (!$ok) throw new \RuntimeException($label); }
    function rejects($service, $invoice) {
        try { $service->getCharges($invoice); } catch (\UnexpectedValueException $e) { return true; }
        return false;
    }
    $invoice = (object)['id'=>628, 'core_empresa_id'=>1, 'core_tipo_transaccion_id'=>52,
        'core_tipo_doc_app_id'=>57, 'consecutivo'=>623, 'ventas_doc_relacionado_id'=>0,
        'valor_total'=>888140, 'valor_ajuste_al_peso'=>0, 'valor_total_bolsas'=>60];
    $pos = clone $invoice;
    $pos->id = 97568;
    $pos->forma_pago = 'credito';
    $pos->lineas_registros_medios_recaudos = '[{"valor":"$888200"}]';
    \App\VentasPos\FacturaPos::$rows = [$pos];
    $service = new \App\FacturacionElectronica\Services\PosInvoiceChargesService;
    $charge = $service->getOseiGlobalAllowanceCharge($invoice);
    check($charge['type'] === 'charge' && $charge['amount'] == 60 && $charge['base_amount'] == 888140, 'Caso FE 623');
    $invoice->ventas_doc_relacionado_id = 97568;
    check($service->getOseiGlobalAllowanceCharge($invoice) === $charge, 'Enlace explícito');
    $invoice->ventas_doc_relacionado_id = 999;
    check(rejects($service, $invoice), 'No sustituir enlace incorrecto');
    $invoice->ventas_doc_relacionado_id = 0;
    foreach (['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo'] as $key) {
        $other = clone $pos;
        $other->$key++;
        \App\VentasPos\FacturaPos::$rows = [$other];
        check(rejects($service, $invoice), 'No aceptar coincidencia parcial: ' . $key);
    }
    \App\VentasPos\FacturaPos::$rows = [$pos, clone $pos];
    check(rejects($service, $invoice), 'Rechazar ambigüedad');
    \App\VentasPos\FacturaPos::$rows = [];
    check(rejects($service, $invoice), 'No omitir bolsas si falta POS');
    $invoice->valor_total_bolsas = 0;
    check($service->getCharges($invoice) === [], 'Factura sin cargos ni POS');
    \App\VentasPos\FacturaPos::$rows = [$pos];
    $invoice->valor_ajuste_al_peso = -40;
    check($service->getOseiGlobalAllowanceCharge($invoice)['type'] === 'discount', 'Redondeo negativo');
    $pos->forma_pago = 'contado';
    check(rejects($service, $invoice), 'Reconciliar pagos');
    echo "OK: 12 escenarios de resolución, cargos, redondeo y pagos.\n";
}
