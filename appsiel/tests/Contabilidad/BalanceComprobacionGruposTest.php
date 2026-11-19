<?php

use App\Http\Controllers\Contabilidad\ContabReportesController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceComprobacionGruposTest extends TestCase
{
    private $conexionOriginal;

    public function setUp()
    {
        parent::setUp();
        $this->conexionOriginal = DB::getDefaultConnection();
        config(['database.connections.balance_grupos_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''
        ]]);
        DB::setDefaultConnection('balance_grupos_test');
        Auth::setUser(new User(['id' => 1, 'empresa_id' => 1]));
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER, core_empresa_id INTEGER, codigo TEXT, descripcion TEXT, contab_cuenta_grupo_id INTEGER, contab_cuenta_clase_id INTEGER)');
        DB::statement('CREATE TABLE contab_cuenta_grupos (id INTEGER, core_empresa_id INTEGER, codigo TEXT, descripcion TEXT, grupo_padre_id INTEGER, contab_cuenta_clase_id INTEGER)');
        DB::statement('CREATE TABLE core_terceros (id INTEGER, numero_identificacion TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE contab_movimientos (core_empresa_id INTEGER, contab_cuenta_id INTEGER, core_tercero_id INTEGER, fecha TEXT, valor_saldo REAL, valor_debito REAL, valor_credito REAL)');
        DB::statement('CREATE TABLE contab_cuenta_clases (id INTEGER, descripcion TEXT)');
        DB::table('contab_cuenta_clases')->insert(['id'=>1, 'descripcion'=>'Activos']);
        DB::table('contab_cuenta_grupos')->insert([
            ['id'=>9,'core_empresa_id'=>1,'codigo'=>'1','descripcion'=>'Activos corrientes','grupo_padre_id'=>0,'contab_cuenta_clase_id'=>1],
            ['id'=>10,'core_empresa_id'=>1,'codigo'=>'10','descripcion'=>'Efectivo','grupo_padre_id'=>9,'contab_cuenta_clase_id'=>1],
            ['id' => 11, 'core_empresa_id' => 1, 'codigo' => '11', 'descripcion' => 'Disponible','grupo_padre_id'=>10,'contab_cuenta_clase_id'=>1],
            ['id' => 13, 'core_empresa_id' => 1, 'codigo' => '13', 'descripcion' => 'Deudores','grupo_padre_id'=>9,'contab_cuenta_clase_id'=>1],
            ['id' => 22, 'core_empresa_id' => 2, 'codigo' => '22', 'descripcion' => 'Grupo externo','grupo_padre_id'=>0,'contab_cuenta_clase_id'=>2]
        ]);
        foreach ([[1, '1105', 11], [2, '1110', 11], [3, '1305', 13], [4, '9999', null], [5, '9998', 99], [6, '1106', 11]] as $cuenta) {
            DB::table('contab_cuentas')->insert([
                'id' => $cuenta[0], 'core_empresa_id' => 1, 'codigo' => $cuenta[1],
                'descripcion' => 'Cuenta '.$cuenta[1], 'contab_cuenta_clase_id'=>1, 'contab_cuenta_grupo_id' => $cuenta[2]
            ]);
        }
        DB::table('contab_cuentas')->insert([
            'id' => 7, 'core_empresa_id' => 2, 'codigo' => '2205',
            'descripcion' => 'Cuenta externa', 'contab_cuenta_clase_id'=>2, 'contab_cuenta_grupo_id' => 22
        ]);
        DB::table('core_terceros')->insert([
            ['id' => 1, 'numero_identificacion' => '101', 'descripcion' => 'Tercero uno'],
            ['id' => 2, 'numero_identificacion' => '102', 'descripcion' => 'Tercero dos']
        ]);
        foreach ([
            [1, 1, '2026-08-31', 1000, 0], [2, 2, '2026-08-31', 200, 0],
            [3, 1, '2026-08-31', 0, -300],
            [1, 1, '2026-09-01', 100, -30], [1, 2, '2026-09-30', 50, 0],
            [2, 2, '2026-09-15', 40, -10], [3, 1, '2026-09-15', 20, -70],
            [4, 1, '2026-09-15', 5, 0], [5, 1, '2026-09-15', 7, 0],
            [6, 1, '2026-09-15', 0, 0], [1, 1, '2026-10-01', 99999, 0]
        ] as $mov) {
            DB::table('contab_movimientos')->insert([
                'core_empresa_id' => 1, 'contab_cuenta_id' => $mov[0], 'core_tercero_id' => $mov[1],
                'fecha' => $mov[2], 'valor_debito' => $mov[3], 'valor_credito' => $mov[4],
                'valor_saldo' => $mov[3] + $mov[4]
            ]);
        }
        DB::table('contab_movimientos')->insert([
            'core_empresa_id' => 2, 'contab_cuenta_id' => 1, 'core_tercero_id' => 1,
            'fecha' => '2026-09-15', 'valor_debito' => 88888, 'valor_credito' => 0, 'valor_saldo' => 88888
        ]);
    }

    public function tearDown()
    {
        DB::purge('balance_grupos_test');
        DB::setDefaultConnection($this->conexionOriginal);
        parent::tearDown();
    }

    public function test_subtotales_y_total_general_con_y_sin_detalle_de_terceros()
    {
        foreach (['No', 'Si'] as $detallar) {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $html = $this->reporte(['agrupar_por_grupo' => 1, 'detalla_terceros' => $detallar]);
            $this->assertCount(3, DB::getQueryLog());
            $xpath = $this->xpath($html);
            $this->assertSame(1, $xpath->query('//tr[@class="fila-clase"]')->length);
            $disponible = $xpath->query('//tr[@class="fila-grupo" and contains(.,"Disponible")]')->item(0);
            $deudores = $xpath->query('//tr[@class="fila-grupo" and contains(.,"Deudores")]')->item(0);
            $this->assertSame($this->celdas('Disponible', ['1.200', '190', '-40', '1.350'], $detallar), $this->valores($disponible));
            $this->assertSame('3', $disponible->getAttribute('data-nivel'));
            $this->assertSame($this->celdas('Deudores', ['-300', '20', '-70', '-350'], $detallar), $this->valores($deudores));
            $this->assertSame(['TOTAL GENERAL', '900', '222', '-110', '1.012'], $this->total($xpath));
            $this->assertContains('Sin grupo', $html);
            $this->assertContains('Grupo 99', $html);
            $this->assertNotContains('Cuenta 1106', $html);
            $this->assertNotContains('88.888', $html);
            $this->assertNotContains('99.999', $html);
            if ($detallar === 'Si') {
                $this->assertContains('Tercero uno', $html);
                $this->assertContains('Tercero dos', $html);
            }
        }
    }

    public function test_filtra_por_id_del_grupo_y_conserva_el_parametro_anterior()
    {
        foreach (['contab_cuenta_grupo_id', 'contab_grupo_cuenta_id'] as $parametro) {
            $html = $this->reporte([$parametro => 11, 'agrupar_por_grupo' => 1]);
            $this->assertContains('Cuenta 1105', $html);
            $this->assertContains('Cuenta 1110', $html);
            $this->assertNotContains('Cuenta 1305', $html);
            $this->assertNotContains('Cuenta 9999', $html);
            $xpath = $this->xpath($html);
            $this->assertSame(3, $xpath->query('//tr[@class="fila-grupo"]')->length);
            $this->assertSame(['TOTAL GENERAL', '1.200', '190', '-40', '1.350'], $this->total($xpath));
        }
    }

    public function test_la_jerarquia_es_obligatoria_y_el_total_no_suma_dos_veces_los_subtotales()
    {
        $html = $this->reporte();
        $this->assertNotContains('subtotal-grupo', $html);
        $this->assertContains('fila-grupo', $html);
        $this->assertContains('ACTIVOS', $html);
        $this->assertSame(['TOTAL GENERAL', '900', '222', '-110', '1.012'], $this->total($this->xpath($html)));
    }

    public function test_grupo_sin_movimientos_y_grupo_externo_no_exponen_datos()
    {
        foreach ([999, 22] as $grupo) {
            $html = $this->reporte(['contab_cuenta_grupo_id' => $grupo, 'agrupar_por_grupo' => 1]);
            $this->assertNotContains('subtotal-grupo', $html);
            $this->assertSame(['TOTAL GENERAL', '0', '0', '0', '0'], $this->total($this->xpath($html)));
        }
    }

    public function test_vista_reporte_cinco_retira_filtros_anteriores_y_conserva_exportacion()
    {
        DB::statement('CREATE TABLE sys_aplicaciones (id INTEGER, app TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE sys_reportes (id INTEGER, core_app_id INTEGER, descripcion TEXT, url_form_action TEXT)');
        DB::statement('CREATE TABLE sys_campos (id INTEGER, name TEXT, descripcion TEXT, tipo TEXT, opciones TEXT, value TEXT, atributos TEXT, requerido INTEGER, editable INTEGER)');
        DB::statement('CREATE TABLE sys_reporte_tiene_campos (core_reporte_id INTEGER, core_campo_id INTEGER, orden INTEGER)');
        DB::table('sys_aplicaciones')->insert(['id' => 14, 'app' => 'contabilidad', 'descripcion' => 'Contabilidad']);
        DB::table('sys_reportes')->insert([
            'id' => 5, 'core_app_id' => 14, 'descripcion' => 'Balance de comprobación',
            'url_form_action' => 'contab_ajax_balance_comprobacion'
        ]);
        DB::table('sys_campos')->insert([
            'id' => 1, 'name' => 'fecha_desde', 'descripcion' => 'Fecha desde', 'tipo' => 'date',
            'opciones' => '', 'value' => 'null', 'atributos' => '', 'requerido' => 1, 'editable' => 1
        ]);
        DB::table('sys_reporte_tiene_campos')->insert(['core_reporte_id' => 5, 'core_campo_id' => 1, 'orden' => 1]);
        request()->replace(['id' => 14, 'reporte_id' => 5]);

        $vista = (new \App\Http\Controllers\Sistema\ReporteController)->vista_reporte();
        $this->assertSame('core.reportes.vista_reportes', $vista->name());
        $campos = array_column($vista->getData()['lista_campos'], null, 'name');
        $this->assertArrayHasKey('fecha_desde', $campos);
        $this->assertArrayNotHasKey('contab_cuenta_grupo_id', $campos);
        $this->assertArrayNotHasKey('agrupar_por_grupo', $campos);

        // Los consumidores anteriores pueden seguir enviando el filtro por grupo.
        $html = $this->reporte(['contab_cuenta_grupo_id' => 11, 'agrupar_por_grupo' => 1, 'reporte_instancia' => json_encode(['id' => 5])]);
        $this->assertContains('Disponible', $html);
        $this->assertNotContains('Deudores', $html);
        $this->assertSame($html, \Illuminate\Support\Facades\Cache::get(\App\Contabilidad\Services\BalanceComprobacionPdfService::cacheKey(5)));

        DB::table('sys_reportes')->where('id', 5)->update(['url_form_action' => 'otro_reporte']);
        $otros = (new \App\Http\Controllers\Sistema\ReporteController)->vista_reporte()->getData()['lista_campos'];
        $this->assertSame(['fecha_desde'], array_column($otros, 'name'));
    }

    public function test_fracciones_de_centavo_no_cambian_los_totales_al_detallar()
    {
        foreach ([1, 2] as $tercero) {
            DB::table('contab_movimientos')->insert([
                'core_empresa_id'=>1, 'contab_cuenta_id'=>1, 'core_tercero_id'=>$tercero,
                'fecha'=>'2026-09-15', 'valor_debito'=>0.014, 'valor_credito'=>0, 'valor_saldo'=>0.014
            ]);
        }
        $controller = new ContabReportesController;
        $method = new ReflectionMethod($controller, 'get_balance_comprobacion_movimientos');
        $method->setAccessible(true);
        $totales = [];
        foreach ([false, true] as $detalle) {
            $movs = $method->invoke($controller, '2026-09-01', '2026-09-30', $detalle);
            $result = (new \App\Contabilidad\Services\BalanceComprobacionJerarquiaService)->generar(
                $movs, \App\Contabilidad\ContabCuentaGrupo::where('core_empresa_id', 1)->get(),
                \App\Contabilidad\ClaseCuenta::all(), $detalle
            );
            $totales[] = $result['totales'];
            $this->assertEquals(222.03, $result['totales']['debitos']);
        }
        $this->assertSame($totales[0], $totales[1]);
    }

    public function test_pdf_descargable_y_excel_con_numeros_y_total_unico()
    {
        DB::statement('CREATE TABLE sys_reportes (id INTEGER, url_form_action TEXT)');
        DB::table('sys_reportes')->insert(['id'=>5, 'url_form_action'=>'contab_ajax_balance_comprobacion']);
        foreach (['No', 'Si'] as $detalle) {
            $html = $this->reporte(['detalla_terceros'=>$detalle, 'reporte_instancia'=>json_encode(['id'=>5])]);
            $xpath = $this->xpath($html);
            $this->assertSame(1, $xpath->query('//table[@data-excel-format="xlsx"]')->length);
            $this->assertSame(1, $xpath->query('//tr[@class="total-general"]')->length);
            $this->assertSame(0, $xpath->query('//tfoot')->length);
            $this->assertSame(4, $xpath->query('//tr[@class="total-general"]/td[@data-excel-value]')->length);
            $response = (new \App\Http\Controllers\Sistema\VistaController)->generar_pdf(5);
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
            $this->assertSame(0, strpos($response->getContent(), '%PDF-'));
            $this->assertContains('balance_de_comprobacion.pdf', $response->headers->get('Content-Disposition'));
        }
    }

    public function test_pdf_no_usa_el_balance_generado_por_otro_usuario()
    {
        DB::statement('CREATE TABLE sys_reportes (id INTEGER, url_form_action TEXT)');
        DB::table('sys_reportes')->insert(['id'=>5, 'url_form_action'=>'contab_ajax_balance_comprobacion']);
        $this->reporte(['reporte_instancia'=>json_encode(['id'=>5])]);
        $otro = new User();$otro->id=77;$otro->empresa_id=1;Auth::setUser($otro);
        $this->setExpectedException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        (new \App\Http\Controllers\Sistema\VistaController)->generar_pdf(5);
    }

    public function test_pdf_procesa_bloques_y_elimina_temporales()
    {
        $binding = $this->app->getBindings()['dompdf.wrapper']['concrete'];
        $bloques = 0;
        $this->app->bind('dompdf.wrapper', function ($app) use ($binding, &$bloques) {
            $bloques++;
            return $binding($app);
        });
        $antes = glob(storage_path('app/balance_pdf_*'));
        try {
            $fila = '<tr data-nivel="2"><td>Cuenta</td><td>0</td><td>100</td><td>-10</td><td>90</td></tr>';
            $html = '<h3>Balance</h3><table id="myTable"><thead><tr><th>Cuenta</th><th>Saldo inicial</th><th>Mov. Débito</th><th>Mov. Crédito</th><th>Saldo final</th></tr></thead><tbody>'.str_repeat($fila, 361).'</tbody></table>';
            $bytes = (new \App\Contabilidad\Services\BalanceComprobacionPdfService)->generar($html, 'Letter', 'Landscape');
            $this->assertSame(3, $bloques);
            $this->assertSame(0, strpos($bytes, '%PDF-'));
            $lector = new \setasign\Fpdi\Fpdi;
            $paginas = $lector->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($bytes));
            $this->assertGreaterThanOrEqual(3, $paginas);
            $plantilla = $lector->importPage(1);
            $this->assertSame('L', $lector->getTemplateSize($plantilla)['orientation']);
            $this->assertSame($antes, glob(storage_path('app/balance_pdf_*')));
        } finally {
            $this->app->bind('dompdf.wrapper', $binding);
        }
    }

    private function celdas($descripcion, $valores, $detallar)
    {
        return array_merge([$descripcion], $detallar === 'Si' ? [''] : [], $valores);
    }

    private function reporte(array $opciones = [])
    {
        return (new ContabReportesController)->contab_ajax_balance_comprobacion(new Request(array_merge([
            'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-30', 'detalla_terceros' => 'No'
        ], $opciones)));
    }

    private function xpath($html)
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        return new DOMXPath($dom);
    }

    private function valores($fila)
    {
        $valores = [];
        foreach ($fila->getElementsByTagName('td') as $celda) {
            $valores[] = preg_replace('/\s+/u', ' ', trim($celda->textContent));
        }
        return $valores;
    }

    private function total($xpath)
    {
        return $this->valores($xpath->query('//tr[contains(@class,"total-general")]')->item(0));
    }
}
