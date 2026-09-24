<?php

class BoletinCalificacionSinConsultas
{
    public $calificacion = 3;
    public $id_asignatura = 1;

    public function nota_nivelacion()
    {
        throw new RuntimeException('La vista no debe consultar nivelaciones ya cargadas o no solicitadas.');
    }
}

class BoletinEtiquetaNivelacionTest extends TestCase
{
    private function renderEtiqueta($modo, $nivelacion, $precargada = true)
    {
        config(['calificaciones.etiqueta_calificacion_boletines' => 'solo_numeros',
            'calificaciones.cantidad_decimales_mostrar_calificaciones' => 1]);
        $linea = (object)[
            'calificacion' => new BoletinCalificacionSinConsultas,
            'escala_valoracion' => (object)['nombre_escala' => 'Alto']
        ];
        if ($precargada) {
            $linea->calificacion_nivelacion = $nivelacion;
        }
        return trim(view('calificaciones.boletines.lbl_descripcion_calificacion', [
            'linea' => $linea, 'mostrar_nota_nivelacion' => $modo
        ])->render());
    }

    public function testFormatoSeisNoDescargaElIconoDeVinetasPorHttp()
    {
        $html = view('calificaciones.boletines.pdf_boletines_6', [
            'curso' => (object)['descripcion' => 'Curso'],
            'tam_letra' => 3.5,
            'margenes' => (object)['superior'=>0, 'derecho'=>0, 'inferior'=>0, 'izquierdo'=>0],
            'mostrar_areas' => 'Si', 'all_boletines' => ''
        ])->render();
        $this->assertNotContains('/nube/check-mark-icon-small.png', $html);
        $this->assertTrue(strpos($html, 'list-style-type: disc') !== false ||
            strpos($html, 'data:image/png;base64,') !== false);
    }

    public function testSinNivelacionSolicitadaNoHaceConsultas()
    {
        $this->assertSame('3,0', $this->renderEtiqueta('', null, false));
    }

    public function testUsaNivelacionPrecargadaEnCadaModo()
    {
        $this->assertSame('4,0<sup>n</sup>', $this->renderEtiqueta('solo_nota_nivelacion_con_etiqueta', 4));
        $this->assertSame('4,0', $this->renderEtiqueta('solo_nota_nivelacion_sin_etiqueta', 4));
        $this->assertSame('<span style="color: gray">3,0</span> &nbsp;4,0<sup>n</sup>', $this->renderEtiqueta('ambas_notas', 4));
        $this->assertSame('3,0', $this->renderEtiqueta('ambas_notas', null));
    }
}
