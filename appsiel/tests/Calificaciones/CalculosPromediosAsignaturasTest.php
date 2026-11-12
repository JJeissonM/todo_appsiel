<?php

class CalculosPromediosAsignaturasTest extends TestCase
{
    private function celdas($tipo, array $notas, $decimales = 2, array $idsPeriodos = [1,2,3,4])
    {
        config(['calificaciones.cantidad_decimales_mostrar_calificaciones' => $decimales]);
        $periodos = collect([]); $calificaciones = collect([]);
        foreach ($idsPeriodos as $i => $id) {
            $periodos->push((object)['id'=>$id,'numero'=>$i+1]);
            if (array_key_exists($id,$notas)) $calificaciones->push((object)[
                'id_estudiante'=>'01','id_asignatura'=>'5','id_periodo'=>(string)$id,
                'calificacion'=>$notas[$id],'lbl_nivelacion'=>'n'
            ]);
        }
        // Notas ajenas y del periodo final no deben participar del promedio.
        foreach ([[2,5,1],[1,6,1],[1,5,999]] as $ids) $calificaciones->push((object)[
            'id_estudiante'=>$ids[0],'id_asignatura'=>$ids[1],'id_periodo'=>$ids[2],
            'calificacion'=>100,'lbl_nivelacion'=>''
        ]);
        $html = view('calificaciones.incluir.promedio_'.$tipo.'_asignaturas', [
            'asignaturas'=>[['id'=>5,'abreviatura'=>'MATEMATICAS']],
            'estudiantes'=>collect([(object)['id_estudiante'=>1,'nombre_completo'=>'ESTUDIANTE']]),
            'periodos'=>$periodos, 'calificaciones'=>$calificaciones,
            'curso'=>(object)['descripcion'=>'Cuarto'],
            'periodo_lectivo'=>(object)['descripcion'=>'2026'],
            'tope_escala_valoracion_minima'=>2.99,
        ])->render();
        $doc = new DOMDocument;
        $doc->loadHTML('<meta charset="UTF-8">'.$html);
        $xpath = new DOMXPath($doc);
        $resultado = [];
        foreach ($xpath->query('//table/tbody/tr[1]/td[position()>2]') as $celda) {
            $span = $xpath->query('./span',$celda)->item(0);
            // Primer nodo de texto: excluye el superíndice de nivelación.
            $resultado[] = ['texto'=>trim($span->firstChild->textContent),
                'estilo'=>$span->getAttribute('style'), 'contenido'=>$span->textContent];
        }
        return $resultado;
    }

    public function test_promedios_contra_referencia_independiente_para_todas_las_combinaciones_de_periodos()
    {
        foreach ([0,1,2] as $decimales) {
            foreach (range(0,15) as $mascara) {
                $notas = [];
                foreach ([1=>0,2=>'3.444',3=>3.447,4=>4.999] as $periodo=>$valor) {
                    if ($mascara & (1 << ($periodo-1))) $notas[$periodo] = $valor;
                }
                $promedio = count($notas) ? array_sum($notas) / count($notas) : 0;
                $esperado = number_format($promedio,$decimales,',','.');
                foreach (['proyectado','consolidado'] as $tipo) {
                    $celdas = $this->celdas($tipo,$notas,$decimales);
                    $this->assertSame($esperado,end($celdas)['texto'], $tipo.' máscara '.$mascara);
                }
            }
        }
    }

    public function test_cero_real_y_nivelacion_no_se_convierten_en_nota_proyectada()
    {
        $celdas = $this->celdas('proyectado',[1=>0,2=>4]);
        $this->assertSame('0,00',$celdas[0]['texto']);
        $this->assertNotContains('yellow',$celdas[0]['estilo']);
        $this->assertContains('n',$celdas[0]['contenido']);
        $this->assertSame('4,00',$celdas[1]['texto']);
        $this->assertSame('4,00',$celdas[2]['texto']);
        $this->assertContains('yellow',$celdas[2]['estilo']);
        $this->assertSame('2,00',$celdas[4]['texto']);
    }

    public function test_proyeccion_alcanza_minimo_aprobatorio_sin_notas_negativas_ni_redondeos_insuficientes()
    {
        foreach ([
            [[], '3,00'],
            [[1=>4], '2,67'],
            [[1=>0], '4,00'],
            [[1=>5,2=>5,3=>5], '0,00'],
            [[1=>0,2=>0,3=>0], '12,00'],
        ] as $caso) {
            $celdas = $this->celdas('proyectado',$caso[0]);
            $total = array_sum($caso[0]);
            for ($i=1;$i<=4;$i++) {
                if (array_key_exists($i,$caso[0])) continue;
                $this->assertSame($caso[1],$celdas[$i-1]['texto']);
                $total += (float)str_replace(',','.',$celdas[$i-1]['texto']);
            }
            $this->assertGreaterThanOrEqual(3, $total/4);
        }
    }

    public function test_redondeo_se_aplica_al_final_y_no_a_cada_periodo()
    {
        foreach (['proyectado','consolidado'] as $tipo) {
            $celdas = $this->celdas($tipo,[1=>3.444,2=>3.444,3=>3.447]);
            $this->assertSame('3,45',end($celdas)['texto']);
            $celdas = $this->celdas($tipo,[17=>0,42=>4],2,[17,42,83]);
            $this->assertSame('2,00',end($celdas)['texto']);
            $celdas = $this->celdas($tipo,[],2,[]);
            $this->assertSame('0,00',end($celdas)['texto']);
        }
    }
}
