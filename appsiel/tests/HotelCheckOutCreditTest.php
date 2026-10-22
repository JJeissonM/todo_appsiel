<?php

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelStay;
use App\Hotel\HotelStayGuest;
use App\Hotel\Services\HotelReceivableService;
use App\Hotel\Services\HotelService;
use App\Ventas\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HotelCheckOutCreditTest extends TestCase
{
    private $previousConnection;
    private $stay;

    public function setUp()
    {
        parent::setUp();
        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.hotel_checkout_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('hotel_checkout_test');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function ($a, $b, $c) {
            return $a . $b . $c;
        }, 3);

        foreach (['cxc_movimientos', 'vtas_doc_encabezados', 'vtas_pos_doc_encabezados'] as $table) {
            Schema::create($table, function ($schema) {
                $schema->increments('id');
                foreach (['core_empresa_id', 'core_tercero_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo'] as $column) {
                    $schema->integer($column);
                }
                $schema->string('forma_pago')->nullable();
                $schema->decimal('saldo_pendiente')->default(0);
                $schema->date('fecha')->nullable();
            });
        }
        Schema::create('core_tipos_docs_apps', function ($schema) {
            $schema->increments('id');
            $schema->string('prefijo');
        });
        Schema::create('core_terceros', function ($schema) {
            $schema->increments('id');
            $schema->string('descripcion');
        });
        Schema::create('hotel_order_headers', function ($schema) {
            $schema->increments('id');
            $schema->integer('empresa_id');
            $schema->integer('stay_id');
            $schema->string('status');
        });
        $this->stay = new HotelStay(['empresa_id' => 1]);
        $this->stay->id = 1;
        $this->stay->setRelation('mainGuest', new Cliente(['core_tercero_id' => 10]));
        $guest = new HotelStayGuest();
        $guest->setRelation('cliente', new Cliente(['core_tercero_id' => 20]));
        $this->stay->setRelation('guests', collect([$guest]));
    }

    public function tearDown()
    {
        DB::setDefaultConnection($this->previousConnection);
        DB::purge('hotel_checkout_test');
        parent::tearDown();
    }

    /** @dataProvider invoiceCases */
    public function test_checkout_depends_on_invoice_payment_type($table, $payment, $balance, $guest, $blocked)
    {
        $this->invoice($table, $payment, $balance, $guest);
        $message = (new HotelService())->getCheckOutBlockMessage($this->stay);
        $this->assertSame($blocked, $message !== '');
        // La excepción de checkout no elimina la cartera que muestra la estadía.
        $this->assertCount($balance > 0.1 ? 1 : 0, (new HotelReceivableService())->pendingInvoices($this->stay));
    }

    public function invoiceCases()
    {
        $cases = [];
        foreach (['vtas_doc_encabezados', 'vtas_pos_doc_encabezados'] as $table) {
            foreach ([10, 20] as $guest) {
                $cases[] = [$table, 'credito', 100, $guest, false];
                $cases[] = [$table, 'contado', 100, $guest, true];
                $cases[] = [$table, 'contado', 0, $guest, false];
            }
        }
        return $cases;
    }

    public function test_credit_does_not_override_cash_balance_or_open_orders()
    {
        $this->invoice('vtas_doc_encabezados', 'credito', 100, 10);
        $this->invoice('vtas_pos_doc_encabezados', 'contado', 50, 20);
        $service = new HotelService();
        $this->assertContains('$ 50,00', $service->getCheckOutBlockMessage($this->stay));
        DB::table('cxc_movimientos')->where('core_tercero_id', 20)->update(['saldo_pendiente' => 0]);
        DB::table('hotel_order_headers')->insert(['empresa_id' => 1, 'stay_id' => 1, 'status' => HotelOrderHeader::STATUS_ABIERTO]);
        $this->assertContains('pendientes por facturar', $service->getCheckOutBlockMessage($this->stay));
    }

    public function test_credit_from_another_company_does_not_exempt_unidentified_debt()
    {
        $this->invoice('vtas_doc_encabezados', 'credito', 100, 10);
        DB::table('vtas_doc_encabezados')->update(['core_empresa_id' => 2]);
        $this->assertNotSame('', (new HotelService())->getCheckOutBlockMessage($this->stay));
    }

    private function invoice($table, $payment, $balance, $guest)
    {
        $identity = [
            'core_empresa_id' => 1, 'core_tercero_id' => $guest,
            'core_tipo_transaccion_id' => $table === 'vtas_doc_encabezados' ? 23 : 32,
            'core_tipo_doc_app_id' => 1, 'consecutivo' => $guest,
        ];
        DB::table($table)->insert(array_merge($identity, ['forma_pago' => $payment]));
        DB::table('cxc_movimientos')->insert(array_merge($identity, [
            'saldo_pendiente' => $balance, 'fecha' => date('Y-m-d'),
        ]));
    }
}
