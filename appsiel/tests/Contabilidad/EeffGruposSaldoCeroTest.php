<?php

use App\Contabilidad\Services\EstructuraEeffService;
use Illuminate\Support\Facades\View;

class EeffGruposSaldoCeroTest extends TestCase
{
    public function testMuestraLaJerarquiaCompletaAunqueLosSaldosSeCompensen()
    {
        $grupos = [
            (object)['id'=>1,'grupo_padre_id'=>0,'contab_cuenta_clase_id'=>3,'descripcion'=>'Patrimonio'],
            (object)['id'=>2,'grupo_padre_id'=>1,'contab_cuenta_clase_id'=>3,'descripcion'=>'Superavit'],
            (object)['id'=>3,'grupo_padre_id'=>2,'contab_cuenta_clase_id'=>3,'descripcion'=>'Prima en colocacion'],
        ];
        $movimientos = [];
        foreach (['320505'=>-9900000, '320510'=>9900000, '320599'=>0] as $codigo=>$valor) {
            $movimientos[] = (object)['cuenta_codigo'=>(string)$codigo,'cuenta_descripcion'=>'Cuenta '.$codigo,
                'cuenta_clase_id'=>3,'cuenta_clase_descripcion'=>'Patrimonio','cuenta_grupo_hijo_id'=>3,'valor_saldo'=>$valor];
        }
        foreach ([true, false] as $detalle) {
            $service = new EstructuraEeffService;
            $filas = $service->generar($movimientos, $grupos, [3], $detalle);
            $html = View::make('contabilidad.reportes.tabla_eeff', ['filas'=>$filas,'anio'=>2026,'gran_total'=>0])->render();
            $dom = new DOMDocument;
            $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new DOMXPath($dom);
            foreach (['Patrimonio', 'Superavit', 'Prima en colocacion'] as $grupo) {
                $row = $xpath->query('//tr[td[contains(.,"'.$grupo.'")]]')->item(0);
                $this->assertNotNull($row);
                $this->assertContains('0,00', $row->textContent);
            }
            $this->assertEquals(0, $service->totales_clases[3]);
            $this->assertSame($detalle ? 2 : 0, $xpath->query('//tr[@class="tr_cuenta"]')->length);
            if ($detalle) {
                $this->assertContains('320505', $html);
                $this->assertContains('320510', $html);
                $this->assertNotContains('320599', $html);
            }
        }
    }
}
