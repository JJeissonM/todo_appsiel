<?php

use App\Calificaciones\Services\EtiquetaCalificacionBoletin;
use App\Calificaciones\Services\ImagenesBoletinPdf;
use Illuminate\Database\Schema\Blueprint;

class PeriodoOpcionesBoletin
{
    public $id = 7;
    public $numero = 1;
    public $periodo_lectivo_id = 1;
    public $periodo_de_promedios = false;

    public function get_calificacion($curso, $estudiante, $asignatura)
    {
        return (object)['calificacion' => 91];
    }

    public function get_calificacion_nivelacion($curso, $estudiante, $asignatura)
    {
        return null;
    }
}

class ColegioOpcionesBoletin
{
    public $piefirma1 = 'DIRECTOR';
    public $piefirma2 = 'PROFESOR';

    public function representante_legal()
    {
        return (object)['descripcion' => 'Directora', 'firma_autorizada' => (object)[
            'imagen' => 'directora.png', 'titulo_tercero' => 'RECTORA',
            'tercero' => (object)['descripcion' => 'Directora']
        ]];
    }
}

class BoletinOpcionesImpresionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'calificaciones.etiqueta_calificacion_boletines' => 'numero_y_letras',
            'calificaciones.cantidad_decimales_mostrar_calificaciones' => 0,
            'configuracion.url_instancia_cliente' => 'http://colegio.test',
            'view.compiled' => sys_get_temp_dir()]);
        DB::purge('sqlite');
        Schema::create('sga_escala_valoracion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('periodo_lectivo_id');
            $table->float('calificacion_minima');
            $table->float('calificacion_maxima');
            $table->string('nombre_escala');
        });
        DB::table('sga_escala_valoracion')->insert([
            'periodo_lectivo_id' => 1, 'calificacion_minima' => 0,
            'calificacion_maxima' => 100, 'nombre_escala' => 'ALTO'
        ]);
    }

    private function datos()
    {
        return [
            'periodo' => new PeriodoOpcionesBoletin,
            'colegio' => new ColegioOpcionesBoletin,
            'curso' => (object)['id' => 1, 'director_grupo' => collect([(object)['tercero' => (object)[
                'descripcion' => 'Docente', 'firma_autorizada' => (object)[
                    'imagen' => 'docente.png', 'titulo_tercero' => 'DOCENTE',
                    'tercero' => (object)['descripcion' => 'Docente']
                ]
            ]]])],
            'firmas' => ['No cargada', 'No cargada'], 'tam_letra' => 3.5
        ];
    }

    public function testFirmasAutorizadasSeConservanConYSinEscala()
    {
        foreach (['Si', 'No'] as $mostrar) {
            $html = view('calificaciones.boletines.seccion_firmas', $this->datos() + [
                'mostrar_escala_valoracion' => $mostrar
            ])->render();
            $this->assertContains('firmas_autorizadas/directora.png', $html);
            $this->assertContains('firmas_autorizadas/docente.png', $html);
            $this->assertContains('RECTORA', $html);
            $this->assertContains('DOCENTE', $html);
            $this->assertSame($mostrar === 'Si', strpos($html, 'Escala de valoración') !== false);
        }
    }

    public function testFirmasTradicionalesSiguenDisponiblesConEscala()
    {
        $datos = $this->datos();
        $datos['firmas'] = ['Cargada', 'Cargada'];
        $datos['mostrar_escala_valoracion'] = 'Si';
        $html = view('calificaciones.boletines.seccion_firmas', $datos)->render();
        $this->assertContains('/storage/app/firma_rector.png', $html);
        $this->assertContains('/storage/app/firma_profesor.png', $html);
        $this->assertNotContains('firmas_autorizadas/', $html);
    }

    public function testEtiquetasDePeriodosYPromedioRespetanConfiguracion()
    {
        $datos = $this->datos();
        $datos += [
            'registro' => (object)['estudiante' => (object)['id' => 1]],
            'linea' => (object)['asignatura_id' => 1, 'maneja_calificacion' => 1,
                'calificacion_nivelacion' => null,
                'calificacion' => (object)['calificacion' => 91],
                'calificaciones_auxiliares' => null,
                'calificaciones_todos_los_periodos_asignatura_estudiante' => collect([(object)['id_periodo' => 7, 'calificacion' => 91]]),
                'calificaciones_niveladas_todos_los_periodos_asignatura_estudiante' => collect([])],
            'lbl_calificaciones_aux' => [],
            'periodos' => [new PeriodoOpcionesBoletin], 'width_columnas' => 60,
            'mostrar_calificacion_requerida' => false,
            'etiqueta_calificacion_boletin' => new EtiquetaCalificacionBoletin([
                (object)['calificacion_minima' => 0, 'calificacion_maxima' => 100, 'nombre_escala' => 'ALTO']
            ])
        ];
        foreach (['numero_y_letras' => '91 (ALTO)', 'solo_numeros' => '91', 'solo_letras' => 'ALTO'] as $modo => $esperado) {
            config(['calificaciones.etiqueta_calificacion_boletines' => $modo]);
            foreach ([false, true] as $final) {
                $datos['periodo']->periodo_de_promedios = $final;
                foreach (['periodos', 'auxiliares'] as $formato) {
                $html = view('calificaciones.boletines.lbls_descripciones_calificaciones_' . $formato, $datos)->render();
                $dom = new DOMDocument;
                $dom->loadHTML('<table><tr>'.$html.'</tr></table>');
                $celdas = $dom->getElementsByTagName('td');
                $this->assertSame(2, $celdas->length);
                foreach ($celdas as $celda) $this->assertSame($esperado, trim($celda->textContent));
                }
            }
        }
    }

    public function testDocenteDebajoDeAsignaturaSoloSinLogrosYSiSeSolicita()
    {
        $linea = (object)['asignatura_descripcion' => 'INGLÉS',
            'profesor_asignatura' => (object)['profesor' => (object)['name' => 'Luz Maestre']]];
        foreach ([0, 1] as $logros) {
            foreach (['Si', 'No'] as $docentes) {
                $html = view('calificaciones.boletines.formatos.nombre_asignatura', [
                    'linea' => $linea, 'mostrar_logros' => $logros,
                    'mostrar_nombre_docentes' => $docentes, 'tam_letra' => 3.5
                ])->render();
                $texto = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
                $this->assertContains('INGLÉS', $texto);
                $this->assertSame(!$logros && $docentes === 'Si', strpos($html, 'Luz Maestre') !== false);
                if (!$logros && $docentes === 'Si') {
                    $this->assertContains('text-align: right', $html);
                    $this->assertLessThan(strpos($texto, 'Luz Maestre'), strpos($texto, 'INGLÉS'));
                }
            }
        }
    }
    public function testDompdfImprimeFirmasConEscalaSinImagenesRemotasNiAdvertencias()
    {
        $directorio = sys_get_temp_dir() . '/firmas-boletin-' . uniqid();
        mkdir($directorio);
        try {
            $imagen = imagecreatetruecolor(20, 10);
            imagepng($imagen, $directorio . '/directora.png');
            imagepng($imagen, $directorio . '/docente.png');
            imagedestroy($imagen);
            $html = view('calificaciones.boletines.seccion_firmas', $this->datos() + [
                'mostrar_escala_valoracion' => 'Si'
            ])->render();
            $imagenes = new ImagenesBoletinPdf([
                'http://colegio.test/storage/app/firmas_autorizadas/' => $directorio
            ]);
            $html = $imagenes->prepararHtml($html);
            $this->assertSame(2, substr_count($html, 'data:image/png;base64,'));
            $GLOBALS['_dompdf_warnings'] = [];
            $pdf = new \Dompdf\Dompdf();
            $pdf->set_option('isRemoteEnabled', false);
            $pdf->set_option('logOutputFile', null);
            $pdf->loadHtml('<html><head><meta charset="UTF-8"><style>table{width:100%;border-collapse:collapse}</style></head><body>' . $html . '</body></html>');
            $pdf->setPaper('letter');
            $pdf->render();
            $binario = $pdf->output();
            $this->assertSame('%PDF-', substr($binario, 0, 5));
            $this->assertContains('/Subtype /Image', $binario);
            $this->assertSame(1, $pdf->getCanvas()->get_page_count());
            $this->assertEmpty($GLOBALS['_dompdf_warnings'], implode('; ', $GLOBALS['_dompdf_warnings']));
        } finally {
            foreach (glob($directorio . '/*') as $archivo) unlink($archivo);
            rmdir($directorio);
        }
    }

}
