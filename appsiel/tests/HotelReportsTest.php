<?php

use App\Http\Controllers\Hotel\HotelReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HotelReportsTest extends TestCase
{
    private $previousConnection;

    public function setUp()
    {
        parent::setUp();
        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.hotel_reports_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('hotel_reports_test');
        Auth::shouldReceive('user')->andReturn((object)['empresa_id' => 1]);
        foreach ([
            'hotel_order_headers' => ['empresa_id', 'stay_id', 'cliente_id', 'status', 'order_date', 'document_number'],
            'hotel_order_lines' => ['hotel_order_id', 'line_total'],
            'hotel_stays' => ['room_id'], 'hotel_rooms' => ['room_number'],
            'vtas_clientes' => ['core_tercero_id'],
            'core_terceros' => ['descripcion', 'numero_identificacion', 'telefono1', 'email'],
            'core_tipos_docs_apps' => ['prefijo'],
            'cxc_movimientos' => ['core_empresa_id', 'core_tercero_id', 'core_tipo_doc_app_id', 'consecutivo', 'estado', 'saldo_pendiente', 'fecha'],
            'hotel_reservations' => ['empresa_id', 'cliente_id', 'room_id', 'reserved_from', 'reserved_until', 'status', 'notes'],
        ] as $table => $columns) {
            Schema::create($table, function ($schema) use ($columns) {
                $schema->increments('id');
                foreach ($columns as $column) {
                    if (in_array($column, ['saldo_pendiente', 'line_total'])) {
                        $schema->decimal($column, 15, 2)->nullable();
                    } else {
                        $schema->string($column)->nullable();
                    }
                }
            });
        }
    }

    public function tearDown()
    {
        DB::setDefaultConnection($this->previousConnection);
        DB::purge('hotel_reports_test');
        parent::tearDown();
    }

    public function test_customer_advance_is_counted_once_for_multiple_orders()
    {
        DB::table('vtas_clientes')->insert(['id' => 1, 'core_tercero_id' => 1]);
        DB::table('core_terceros')->insert(['id' => 1, 'descripcion' => 'Cliente prueba']);
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('hotel_order_headers')->insert(['id' => $id, 'empresa_id' => $id == 4 ? 2 : 1,
                'cliente_id' => 1, 'status' => $id == 3 ? 'ANULADO' : 'ABIERTO',
                'document_number' => 'PED-' . $id, 'order_date' => '2026-10-18']);
            DB::table('hotel_order_lines')->insert(['hotel_order_id' => $id, 'line_total' => 100]);
        }
        DB::table('cxc_movimientos')->insert(['core_empresa_id' => 1, 'core_tercero_id' => 1,
            'estado' => 'Pendiente', 'saldo_pendiente' => -50, 'fecha' => '2020-01-01']);
        $html = (new HotelReportController())->receivableStays(new Request());
        $this->assertContains('150,00', $html);
        $this->assertNotContains('PED-3', $html);
        $this->assertNotContains('PED-4', $html);
    }

    public function test_reservations_filter_by_overlap_active_status_and_company()
    {
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('hotel_reservations')->insert(['empresa_id' => $id == 4 ? 2 : 1,
                'status' => $id == 3 ? 'CUMPLIDA' : 'ACTIVA', 'notes' => 'Reserva prueba ' . $id,
                'reserved_from' => '2026-10-01 12:00:00',
                'reserved_until' => $id == 2 ? '2026-10-18 00:00:00' : '2026-10-20 12:00:00']);
        }
        $html = (new HotelReportController())->reservations(new Request([
            'hotel_reservations_desde' => '2026-10-18', 'hotel_reservations_hasta' => '2026-10-18',
        ]));
        $this->assertContains('Reserva prueba 1', $html);
        foreach ([2, 3, 4] as $id) {
            $this->assertNotContains('Reserva prueba ' . $id, $html);
        }
    }

    public function test_invalid_date_ranges_are_rejected()
    {
        foreach (['receivableStays', 'reservations'] as $method) {
            $html = (new HotelReportController())->$method(new Request(['fecha_desde' => '2026-02-30']));
            $this->assertContains('alert-danger', $html);
            $html = (new HotelReportController())->$method(new Request(['fecha_desde' => '2026-10-19', 'fecha_hasta' => '2026-10-18']));
            $this->assertContains('alert-danger', $html);
        }
    }
}
