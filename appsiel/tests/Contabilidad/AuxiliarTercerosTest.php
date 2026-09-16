<?php

use App\Contabilidad\Services\AuxiliarTercerosService;
use App\Http\Controllers\Contabilidad\AuxiliarTercerosController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuxiliarTercerosTest extends TestCase
{
    private $previousConnection;
    private $directory;

    protected function setUp()
    {
        parent::setUp();
        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.auxiliar_terceros_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('auxiliar_terceros_test');
        $this->directory = sys_get_temp_dir().'/auxiliar_terceros_test_'.uniqid();
        DB::statement('CREATE TABLE contab_movimientos (id INTEGER PRIMARY KEY, core_empresa_id INTEGER, core_tercero_id INTEGER, contab_cuenta_id INTEGER, fecha TEXT, valor_debito REAL, valor_credito REAL, valor_saldo REAL, core_tipo_doc_app_id INTEGER, core_tipo_transaccion_id INTEGER, consecutivo INTEGER, detalle_operacion TEXT, documento_soporte TEXT, estado TEXT)');
        DB::statement('CREATE TABLE core_terceros (id INTEGER PRIMARY KEY, numero_identificacion TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER PRIMARY KEY, codigo TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE core_tipos_docs_apps (id INTEGER PRIMARY KEY, prefijo TEXT)');
        DB::table('core_terceros')->insert(['id'=>1,'numero_identificacion'=>'001234567890123456','descripcion'=>'=Tercero & prueba']);
        DB::table('contab_cuentas')->insert([['id'=>1,'codigo'=>'110505','descripcion'=>'Caja'],['id'=>2,'codigo'=>'210505','descripcion'=>'Pasivo']]);
        DB::table('core_tipos_docs_apps')->insert(['id'=>1,'prefijo'=>'CC']);
        $this->movement(1, '2024-12-31', 100, 0, 100);
        $this->movement(2, '2025-01-01', 20, 0, 20);
        $this->movement(3, '2025-01-01', 0, -5, -5);
        $this->movement(4, '2025-12-31', 0, -10, -10);
        $this->movement(5, '2026-01-01', 900, 0, 900);
        $this->movement(6, '2025-01-01', 500, 0, 500, ['core_empresa_id'=>2]);
        $this->movement(7, '2024-01-01', 0, -80, -80, ['contab_cuenta_id'=>2]);
        // Huérfano, estado que el auxiliar existente incluye y crédito reversado.
        $this->movement(8, '2025-02-01', 0, 7, 7, ['core_tercero_id'=>99,'estado'=>'Pendiente']);
    }

    private function movement($id, $date, $debit, $credit, $balance, array $extra = [])
    {
        DB::table('contab_movimientos')->insert(array_merge(['id'=>$id,'core_empresa_id'=>1,'core_tercero_id'=>1,'contab_cuenta_id'=>1,'fecha'=>$date,'valor_debito'=>$debit,'valor_credito'=>$credit,'valor_saldo'=>$balance,'core_tipo_doc_app_id'=>1,'core_tipo_transaccion_id'=>1,'consecutivo'=>1,'detalle_operacion'=>'Detalle <prueba>','documento_soporte'=>'001','estado'=>'Activo'], $extra));
    }

    protected function tearDown()
    {
        AuxiliarTercerosService::removeDirectory($this->directory);
        DB::purge('auxiliar_terceros_test'); DB::setDefaultConnection($this->previousConnection);
        parent::tearDown();
    }

    private function service(array $filters = [])
    {
        return new AuxiliarTercerosService(array_merge(['empresa'=>1,'ano'=>2025], $filters));
    }

    private function cells($path, $sheet)
    {
        $zip = new ZipArchive(); $this->assertTrue($zip->open($path));
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet'.$sheet.'.xml'));
        $rows = [];
        foreach ($xml->sheetData->row as $r) {
            $values = [];
            foreach ($r->c as $cell) { $values[] = isset($cell->v) ? (float)$cell->v : (string)$cell->is->t; }
            $rows[] = $values;
        }
        $zip->close();
        return [$rows, $xml];
    }

    public function test_export_reconciles_months_year_detail_and_preserves_opening_only_accounts()
    {
        $result = $this->service()->export($this->directory);
        $this->assertEquals(4, $result['audit']['movimientos']);
        $this->assertEquals(20, $result['audit']['debito_anual']);
        $this->assertEquals(8, $result['audit']['credito_anual']);
        $this->assertEquals(20, $result['audit']['saldo_inicial']);
        $this->assertEquals(32, $result['audit']['saldo_final']);
        $this->assertEquals(1, $result['audit']['combinaciones_sin_tercero']);
        $this->assertEquals(1, $result['audit']['creditos_positivos_historicos']);
        $path = glob($this->directory.'/*.xlsx')[0];
        list($summary, $xml) = $this->cells($path, 1);
        $this->assertCount(44, $summary[0]);
        $this->assertEquals('001234567890123456', $summary[1][0]);
        $this->assertEquals('=Tercero & prueba', $summary[1][1]);
        $this->assertEquals(100, $summary[1][4]);
        $this->assertEquals([20,5,115], array_slice($summary[1], 5, 3));
        $this->assertEquals(115, $summary[1][10]); // Febrero sin movimientos.
        $this->assertEquals(105, $summary[1][40]);
        $this->assertEquals([20,15,105], array_slice($summary[1], 41));
        $this->assertEquals(-80, $summary[2][43]); // Naturaleza crédito, sin inversión.
        $this->assertTrue(count($xml->hyperlinks->hyperlink) > 0);
        list($detail) = $this->cells($path, 2);
        $this->assertCount(5, $detail);
        $this->assertEquals([120,115,105], [$detail[1][10],$detail[2][10],$detail[3][10]]);
        $this->assertEquals(['2','3','4','8'], [$detail[1][11],$detail[2][11],$detail[3][11],$detail[4][11]]);
        // No escrituras contables en la generación.
        $this->assertEquals(8, DB::table('contab_movimientos')->count());
    }

    public function test_company_third_account_and_code_range_filters_apply_to_history_and_detail()
    {
        foreach ([['tercero'=>1,'cuenta'=>1], ['tercero'=>1,'cuenta_desde'=>'110000','cuenta_hasta'=>'119999']] as $filter) {
            $service = $this->service($filter);
            $rows = $service->summaryQuery()->get();
            $this->assertCount(1, $rows);
            $this->assertEquals(100, $rows[0]->inicial);
            $this->assertEquals(3, $service->detailQuery()->count());
        }
        $this->assertEquals(500, $this->service(['empresa'=>2])->summaryQuery()->first()->debito);
    }

    public function test_splits_at_pair_boundary_without_losing_or_duplicating_rows()
    {
        $result = $this->service()->export($this->directory, 3);
        $this->assertEquals(2, $result['audit']['archivos_excel']);
        $ids = [];
        foreach (glob($this->directory.'/*.xlsx') as $path) {
            list($rows) = $this->cells($path, 2);
            foreach (array_slice($rows, 1) as $row) { $ids[] = $row[11]; }
        }
        $this->assertEquals(['2','3','4','8'], $ids);
    }

    public function test_reports_historical_inconsistency_without_rewriting_the_balance()
    {
        $this->movement(9, '2023-01-01', 50, 0, 3);
        $result = $this->service()->export($this->directory);
        $this->assertEquals(1, $result['audit']['saldos_historicos_inconsistentes']);
        $this->assertEquals(23, $result['audit']['saldo_inicial']);
    }

    public function test_empty_result_still_has_two_header_sheets()
    {
        $result = $this->service(['tercero'=>888])->export($this->directory);
        $this->assertEquals(0, $result['audit']['movimientos']);
        foreach ([1,2] as $sheet) {
            list($rows) = $this->cells(glob($this->directory.'/*.xlsx')[0], $sheet);
            $this->assertCount(1, $rows);
        }
    }

    public function test_rejects_unauthorized_company_before_querying()
    {
        Auth::shouldReceive('user')->once()->andReturn((object)['empresa_id'=>1]);
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\HttpException');
        (new AuxiliarTercerosController())->export(new Request(['empresa'=>2,'ano'=>2025]));
    }

    public function test_generated_workbook_is_readable_by_existing_spreadsheet_library()
    {
        $this->service()->export($this->directory);
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load(glob($this->directory.'/*.xlsx')[0]);
        $this->assertEquals(['AUXILIAR_TERCEROS_MENSUAL', 'AUXILIAR_TERCEROS_DETALLE'], $book->getSheetNames());
        $this->assertEquals('001234567890123456', $book->getSheet(0)->getCell('A2')->getValue());
        $this->assertEquals('=Tercero & prueba', $book->getSheet(0)->getCell('B2')->getValue());
        $this->assertEquals('inlineStr', $book->getSheet(0)->getCell('B2')->getDataType());
        $this->assertEquals(105, $book->getSheet(0)->getCell('AR2')->getValue());
        $book->disconnectWorksheets();
    }

    public function test_refuses_export_if_detail_does_not_match_summary()
    {
        $service = new AuxiliarTercerosMissingRowService(['empresa'=>1,'ano'=>2025]);
        $this->setExpectedException('RuntimeException', 'No coincide la validación');
        $service->export($this->directory);
    }

    public function test_large_historical_balance_does_not_absorb_fractional_movements()
    {
        $this->movement(10, '2024-01-01', 56445596308293.64, 0, 56445596308293.64);
        for ($id = 11; $id <= 110; $id++) {
            $this->movement($id, '2025-12-31', .01, 0, .01);
        }
        $this->service(['tercero'=>1, 'cuenta'=>1])->export($this->directory);
        $path = glob($this->directory.'/*.xlsx')[0];
        list($summary) = $this->cells($path, 1);
        list($detail) = $this->cells($path, 2);
        $this->assertSame($summary[1][40], $summary[1][43]);
        $this->assertSame($summary[1][43], $detail[count($detail)-1][10]);
        $this->assertSame($summary[1][4] + 6.0, $summary[1][43]);
    }

    public function test_web_download_streams_zip_and_removes_private_temporary_directory()
    {
        Auth::shouldReceive('user')->once()->andReturn((object)['empresa_id'=>1]);
        $before = glob(storage_path('app/auxiliar_terceros_*'));
        $response = (new AuxiliarTercerosController())->export(new Request(['empresa'=>1,'ano'=>2025]));
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\StreamedResponse', $response);
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $this->assertSame('PK', substr($content, 0, 2));
        $this->assertEquals($response->headers->get('Content-Length'), strlen($content));
        $this->assertEquals($before, glob(storage_path('app/auxiliar_terceros_*')));
    }

    public function test_invalid_year_is_rejected()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->service(['ano'=>'2025 OR 1=1']);
    }
}

// Simula una pérdida en el detalle para comprobar que no se entrega un archivo válido.
class AuxiliarTercerosMissingRowService extends AuxiliarTercerosService
{
    public function detailQuery()
    {
        return parent::detailQuery()->where('m.id', '<>', 3);
    }
}
