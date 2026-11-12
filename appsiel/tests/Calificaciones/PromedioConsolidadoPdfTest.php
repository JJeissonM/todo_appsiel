<?php

class PromedioConsolidadoPdfTest extends TestCase
{
    private function contenido($cantidadAsignaturas, $cantidadEstudiantes = 1)
    {
        config(['calificaciones.cantidad_decimales_mostrar_calificaciones' => 2]);
        $asignaturas = [];
        $calificaciones = collect([]);
        for ($i = 1; $i <= $cantidadAsignaturas; $i++) {
            $asignaturas[] = ['id' => $i, 'abreviatura' => 'ASIGNATURA '.$i];
            foreach ([1 => 3, 3 => 5] as $periodo => $nota) {
                $calificaciones->push((object)[
                    'id_estudiante' => 1, 'id_periodo' => $periodo,
                    'id_asignatura' => $i, 'calificacion' => $nota, 'lbl_nivelacion' => ''
                ]);
            }
        }
        $estudiantes = collect([]);
        for ($i = 1; $i <= $cantidadEstudiantes; $i++) $estudiantes->push((object)[
            'id_estudiante' => $i, 'nombre_completo' => 'ESTUDIANTE '.$i.' APELLIDOS Y NOMBRES COMPLETOS'
        ]);
        $periodos = collect([]);
        for ($i = 1; $i <= 4; $i++) $periodos->push((object)['id' => $i, 'numero' => $i]);
        return view('calificaciones.incluir.promedio_consolidado_asignaturas', [
            'asignaturas' => $asignaturas, 'estudiantes' => $estudiantes,
            'periodos' => $periodos, 'calificaciones' => $calificaciones,
            'curso' => (object)['descripcion' => 'Cuarto A'],
            'periodo_lectivo' => (object)['descripcion' => 'Año 2026'],
            'tope_escala_valoracion_minima' => 2.99,
        ])->render();
    }

    public function test_todas_las_asignaturas_y_promedios_se_distribuyen_en_bloques_de_ocho_exportables()
    {
        foreach ([0, 1, 8, 9, 16, 17, 25] as $cantidad) {
            $html = $this->contenido($cantidad);
            $this->assertSame((int)ceil($cantidad / 8), substr_count($html, 'data-excel-export="true"'));
            $dom = new DOMDocument;
            $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>');
            $xpath = new DOMXPath($dom);
            $asignaturas = [];
            foreach ($xpath->query('//table') as $tabla) {
                $encabezados = $xpath->query('./thead/tr[1]/th[position()>2]', $tabla);
                $this->assertGreaterThan(0, $encabezados->length);
                $this->assertLessThanOrEqual(8, $encabezados->length);
                foreach ($encabezados as $encabezado) $asignaturas[] = trim($encabezado->textContent);
                $celdas = $xpath->query('./tbody/tr[1]/td[position()>2]', $tabla);
                $this->assertSame($encabezados->length, $celdas->length);
                // Se promedian solo los periodos con nota: (3 + 5) / 2, incluso en las partes posteriores.
                foreach ($celdas as $celda) $this->assertSame('4,00', trim($celda->textContent));
            }
            $esperadas = [];
            for ($i = 1; $i <= $cantidad; $i++) $esperadas[] = 'ASIGNATURA '.$i;
            $this->assertSame($esperadas, $asignaturas);
        }
    }

    public function test_dompdf_renderiza_cuatro_partes_sin_advertencias_ni_desbordamientos()
    {
        $contenido = $this->contenido(25, 35);
        $this->assertSame(4, substr_count($contenido, 'data-excel-export="true"'));
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
            $this->assertEmpty($GLOBALS['_dompdf_warnings'], implode('; ', array_unique($GLOBALS['_dompdf_warnings'])));
            $this->assertSame('%PDF-', substr($binario, 0, 5));
            $this->assertGreaterThanOrEqual(4, count($tablas));
            $this->assertGreaterThanOrEqual(30, min(array_column($limites, 0)));
            $this->assertLessThanOrEqual($dompdf->getCanvas()->get_width() - 30,
                max(array_column($limites, 1)), 'Contenido fuera del ancho útil en '.implode(' ', $papel));
            $texto = preg_replace('/\s+/', ' ', implode(' ', $textos));
            $this->assertContains('ASIGNATURA 25', $texto);
            $this->assertContains('ESTUDIANTE 35', $texto);
        }
    }
}
