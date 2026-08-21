<?php

use App\Nomina\NomDocEncabezado;
use App\Nomina\TransaccionesViaInterfaz\LibroExcel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LibroExcelTransaccionesTest extends TestCase
{
    protected $archivosTemporales = [];

    public function setUp()
    {
        parent::setUp();

        config(['database.connections.libro_excel_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('libro_excel_testing');

        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });
        Schema::create('nom_cargos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->integer('cargo_id');
            $table->date('fecha_ingreso');
            $table->date('contrato_hasta')->nullable();
            $table->string('estado');
        });
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modo_liquidacion_id');
            $table->string('descripcion');
            $table->string('naturaleza');
            $table->string('estado');
        });
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('nom_contrato_id');
            $table->integer('nom_concepto_id');
        });

        DB::table('core_terceros')->insert([
            'id' => 1,
            'core_empresa_id' => 7,
            'numero_identificacion' => '10650001',
            'descripcion' => 'ANA EMPLEADA'
        ]);
        DB::table('nom_cargos')->insert(['id' => 3, 'descripcion' => 'ANALISTA']);
        DB::table('nom_contratos')->insert([
            'id' => 5,
            'core_tercero_id' => 1,
            'cargo_id' => 3,
            'fecha_ingreso' => '2026-01-01',
            'contrato_hasta' => '2026-12-31',
            'estado' => 'Activo'
        ]);
        DB::table('nom_conceptos')->insert([
            'id' => 20,
            'modo_liquidacion_id' => 2,
            'descripcion' => 'BONIFICACION MANUAL',
            'naturaleza' => 'Devengo',
            'estado' => 'Activo'
        ]);
    }

    public function tearDown()
    {
        foreach ($this->archivosTemporales as $archivo) {
            if (file_exists($archivo)) {
                unlink($archivo);
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function lee_columnas_en_cualquier_orden_y_reporta_la_fila_duplicada()
    {
        $ruta = $this->crearLibro([
            ['valor', 'concepto_id', 'numero_identificacion', 'cantidad_horas'],
            [150000, 20, '10650001', 0],
            [200000, 20, '10650001', 0]
        ]);

        $lineas = (new LibroExcel($this->documento(), $ruta))->validar();

        $this->assertCount(2, $lineas);
        $this->assertSame(2, $lineas[0]->numero_fila);
        $this->assertSame(150000.0, $lineas[0]->valor);
        $this->assertSame(5, $lineas[0]->contrato->id);
        $this->assertEmpty($lineas[0]->errores);
        $this->assertContains('La combinación empleado/concepto está repetida; ya aparece en la fila 2.', $lineas[1]->errores);
    }

    /** @test */
    public function rechaza_libros_con_columnas_faltantes()
    {
        $ruta = $this->crearLibro([
            ['numero_identificacion', 'concepto_id', 'valor'],
            ['10650001', 20, 150000]
        ]);

        $this->setExpectedException(InvalidArgumentException::class, 'Faltan columnas obligatorias');

        (new LibroExcel($this->documento(), $ruta))->validar();
    }

    /** @test */
    public function valida_la_vigencia_del_contrato_y_los_valores_numericos()
    {
        DB::table('nom_contratos')->where('id', 5)->update(['contrato_hasta' => '2026-06-30']);
        $ruta = $this->crearLibro([
            ['numero_identificacion', 'concepto_id', 'cantidad_horas', 'valor'],
            ['10650001', 20, 'texto', 0]
        ]);

        $linea = (new LibroExcel($this->documento(), $ruta))->validar()[0];

        $this->assertContains('La columna cantidad_horas debe contener un número sin símbolos de moneda ni separadores de miles.', $linea->errores);
        $this->assertContains('El empleado no tiene un contrato activo y vigente durante el período del documento.', $linea->errores);
    }

    protected function documento()
    {
        $documento = new class extends NomDocEncabezado {
            public function lapso()
            {
                return (object) [
                    'fecha_inicial' => '2026-07-01',
                    'fecha_final' => '2026-07-31'
                ];
            }
        };
        $documento->id = 9;
        $documento->core_empresa_id = 7;

        return $documento;
    }

    protected function crearLibro(array $filas)
    {
        $libro = new Spreadsheet;
        $libro->getActiveSheet()->fromArray($filas, null, 'A1');
        $ruta = tempnam(sys_get_temp_dir(), 'nom_excel_') . '.xlsx';
        (new Xlsx($libro))->save($ruta);
        $libro->disconnectWorksheets();
        $this->archivosTemporales[] = $ruta;

        return $ruta;
    }
}
