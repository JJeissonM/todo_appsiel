<?php

class PromedioProyectadoPdfTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_dompdf_renderiza_todas_las_partes_sin_desbordar_la_hoja()
    {
        config(['calificaciones.cantidad_decimales_mostrar_calificaciones' => 2]);
        $asignaturas = [];
        for ($i = 1; $i <= 20; $i++) $asignaturas[] = ['id' => $i, 'abreviatura' => 'ASIGNATURA '.$i];
        $estudiantes = collect([]);
        for ($i = 1; $i <= 35; $i++) $estudiantes->push((object)[
            'id_estudiante' => $i, 'nombre_completo' => 'ESTUDIANTE '.$i.' APELLIDOS Y NOMBRES COMPLETOS'
        ]);
        $periodos = collect([]);
        for ($i = 1; $i <= 4; $i++) $periodos->push((object)['id' => $i, 'numero' => $i]);
        $contenido = view('calificaciones.incluir.promedio_proyectado_asignaturas', [
            'asignaturas' => $asignaturas, 'estudiantes' => $estudiantes,
            'periodos' => $periodos, 'calificaciones' => collect([(object)[
                'id_estudiante' => 1, 'id_periodo' => 1, 'id_asignatura' => 1, 'calificacion' => 100, 'lbl_nivelacion' => 'n'
            ]]),
            'curso' => (object)['descripcion' => 'Cuarto A'],
            'periodo_lectivo' => (object)['descripcion' => 'Año 2026'],
            'tope_escala_valoracion_minima' => 2.99,
        ])->render();
        $this->assertSame(4, substr_count($contenido, 'data-excel-export="true"'));
        $this->assertContains('ASIGNATURA 20', $contenido);
        $this->assertSame(4, substr_count($contenido, 'ESTUDIANTE 35 APELLIDOS'));
        $html = view('core.pdf_documento', ['contenido' => $contenido])->render();
        foreach ([['letter', 'portrait'], ['folio', 'portrait'], ['folio', 'landscape']] as $papel) {
            $GLOBALS['_dompdf_warnings'] = [];
            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('isRemoteEnabled', false);
            $dompdf->set_option('logOutputFile', null);
            $tablas = []; $limites = []; $textos = [];
            $dompdf->setCallbacks([['event' => 'end_frame', 'f' => function ($info) use (&$tablas, &$limites, &$textos) {
                $frame = $info['frame'];
                $tipo = $frame->get_node()->nodeName;
                if ($tipo === 'table') $tablas[] = $frame->get_border_box();
                if (in_array($tipo, ['table', 'th', 'td', '#text'])) {
                    $caja = $frame->get_border_box();
                    if ($caja['w'] > 0) $limites[] = [$caja['x'], $caja['x'] + $caja['w']];
                }
                if ($tipo === '#text') $textos[] = $frame->get_node()->textContent;
            }]]);
            $pdf->loadHTML($html)->setPaper($papel[0], $papel[1]);
            $binario = $pdf->output();
            $this->assertEmpty($GLOBALS['_dompdf_warnings'], 'Advertencias de Dompdf en '.implode(' ', $papel).': '.implode('; ', array_unique($GLOBALS['_dompdf_warnings'])));
            $this->assertSame('%PDF-', substr($binario, 0, 5));
            $this->assertGreaterThanOrEqual(4, count($tablas));
            $this->assertGreaterThanOrEqual(30, min(array_column($limites, 0)));
            $this->assertLessThanOrEqual($dompdf->getCanvas()->get_width() - 30,
                max(array_column($limites, 1)), 'Contenido fuera del ancho útil en '.implode(' ', $papel));
            $texto = preg_replace('/\s+/', ' ', implode(' ', $textos));
            $this->assertContains('ASIGNATURA 20', $texto);
            $this->assertContains('ESTUDIANTE 35', $texto);
            unset($pdf, $dompdf);
            gc_collect_cycles();
        }
    }
}
