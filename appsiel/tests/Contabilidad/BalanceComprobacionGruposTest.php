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
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER, core_empresa_id INTEGER, codigo TEXT, descripcion TEXT, contab_cuenta_grupo_id INTEGER)');
        DB::statement('CREATE TABLE contab_cuenta_grupos (id INTEGER, core_empresa_id INTEGER, codigo TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE core_terceros (id INTEGER, numero_identificacion TEXT, descripcion TEXT)');
        DB::statement('CREATE TABLE contab_movimientos (core_empresa_id INTEGER, contab_cuenta_id INTEGER, core_tercero_id INTEGER, fecha TEXT, valor_saldo REAL, valor_debito REAL, valor_credito REAL)');
        DB::table('contab_cuenta_grupos')->insert([
            ['id' => 11, 'core_empresa_id' => 1, 'codigo' => '11', 'descripcion' => 'Disponible'],
            ['id' => 13, 'core_empresa_id' => 1, 'codigo' => '13', 'descripcion' => 'Deudores'],
            ['id' => 22, 'core_empresa_id' => 2, 'codigo' => '22', 'descripcion' => 'Grupo externo']
        ]);
        foreach ([[1, '1105', 11], [2, '1110', 11], [3, '1305', 13], [4, '9999', null], [5, '9998', 99], [6, '1106', 11]] as $cuenta) {
            DB::table('contab_cuentas')->insert([
                'id' => $cuenta[0], 'core_empresa_id' => 1, 'codigo' => $cuenta[1],
                'descripcion' => 'Cuenta '.$cuenta[1], 'contab_cuenta_grupo_id' => $cuenta[2]
            ]);
        }
        DB::table('contab_cuentas')->insert([
            'id' => 7, 'core_empresa_id' => 2, 'codigo' => '2205',
            'descripcion' => 'Cuenta externa', 'contab_cuenta_grupo_id' => 22
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
            $this->assertCount(1, DB::getQueryLog());
            $xpath = $this->xpath($html);
            $subtotales = $xpath->query('//tr[contains(@class,"subtotal-grupo")]');
            $this->assertSame(4, $subtotales->length);
            $disponible = $xpath->query('//tr[contains(@class,"subtotal-grupo") and contains(.,"Disponible")]')->item(0);
            $deudores = $xpath->query('//tr[contains(@class,"subtotal-grupo") and contains(.,"Deudores")]')->item(0);
            $this->assertSame(['Subtotal 11 Disponible', '1.200', '190', '-40', '1.350'], $this->valores($disponible));
            $this->assertSame(['Subtotal 13 Deudores', '-300', '20', '-70', '-350'], $this->valores($deudores));
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
            $this->assertSame(1, $xpath->query('//tr[contains(@class,"subtotal-grupo")]')->length);
            $this->assertSame(['TOTAL GENERAL', '1.200', '190', '-40', '1.350'], $this->total($xpath));
        }
    }

    public function test_sin_agrupar_conserva_los_totales_y_no_inserta_subtotales()
    {
        $html = $this->reporte();
        $this->assertNotContains('subtotal-grupo', $html);
        $this->assertNotContains('encabezado-grupo', $html);
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

    public function test_selector_ofrece_grupos_de_las_cuentas_de_la_empresa_actual()
    {
        $datos = (new ContabReportesController)->balance_comprobacion()->getData();
        $this->assertSame(['todos' => 'Todos los grupos', 11 => '11 Disponible', 13 => '13 Deudores'], $datos['grupos']);
    }

    public function test_vista_reporte_cinco_incluye_filtro_y_agrupacion_sin_migrar_su_configuracion()
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
        $this->assertSame('select', $campos['contab_cuenta_grupo_id']['tipo']);
        $this->assertSame(['todos' => 'Todos los grupos', 11 => '11 Disponible', 13 => '13 Deudores'], $campos['contab_cuenta_grupo_id']['opciones']);
        $this->assertSame([0 => 'No', 1 => 'Sí'], $campos['agrupar_por_grupo']['opciones']);

        // El selector visible envía los mismos parámetros que procesa el POST.
        $html = $this->reporte(['contab_cuenta_grupo_id' => 11, 'agrupar_por_grupo' => 1, 'reporte_instancia' => json_encode(['id' => 5])]);
        $this->assertContains('Subtotal 11 Disponible', $html);
        $this->assertNotContains('Deudores', $html);
        $this->assertSame($html, \Illuminate\Support\Facades\Cache::get('pdf_reporte_5'));

        DB::table('sys_reportes')->where('id', 5)->update(['url_form_action' => 'otro_reporte']);
        $otros = (new \App\Http\Controllers\Sistema\ReporteController)->vista_reporte()->getData()['lista_campos'];
        $this->assertSame(['fecha_desde'], array_column($otros, 'name'));
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
