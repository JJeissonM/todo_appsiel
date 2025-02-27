<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Core\ConfiguracionController;

// Modelos
use App\Core\FirmaAutorizada;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Tesoreria\TesoLibretasPago;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

class ReportesController extends Controller
{

    protected $colegio;

    // VALIDAR AUTENTICACION
	public function __construct()
    {
        $this->middleware('auth');
    }
	
    public static function grafica_estudiantes_x_curso( $periodo_lectivo )
    {
        $alumnos_por_curso = Estudiante::get_cantidad_estudiantes_x_curso( $periodo_lectivo );

        // Creación de gráfico de Torta
        $stocksTable = LavachartsFacade::DataTable();
        
        $stocksTable->addStringColumn('cursos')
                    ->addNumberColumn('Cantidad');
        
        foreach($alumnos_por_curso as $registro){
            $stocksTable->addRow([
              $registro->curso, (int)$registro->Cantidad
            ]);
        }

        LavachartsFacade::BarChart('MyStocks', $stocksTable);
        
        return $alumnos_por_curso;
    }
	
    public static function nuevos_matriculados( $periodo_lectivo )
    {
        $alumnos_por_antiguedad = Estudiante::get_estudiantes_x_antiguedad( $periodo_lectivo );
        // Creación de gráfico de Torta
        $stocksTable = LavachartsFacade::DataTable();
        
        $stocksTable->addStringColumn('ESTUDIANTES')
                    ->addNumberColumn('CANTIDAD');
        
        foreach($alumnos_por_antiguedad as $registro){
            $stocksTable->addRow([
              $registro[0], (int)$registro[1]
            ]);
        }

        LavachartsFacade::BarChart('antiguedad', $stocksTable);
        
        return $alumnos_por_antiguedad;
    }

    public static function grafica_estudiantes_x_genero( $periodo_lectivo )
    {
        $generos = Estudiante::get_cantidad_estudiantes_x_genero( $periodo_lectivo );

        // Creación de gráfico de Barras
        $stocksTable2 = LavachartsFacade::DataTable();
        
        $stocksTable2->addStringColumn('Genero')
                    ->addNumberColumn('Cantidad');
        
        foreach($generos as $registro){
            $stocksTable2->addRow([
              $registro->Genero, (int)$registro->Cantidad
            ]);
        }

        LavachartsFacade::PieChart('Generos', $stocksTable2);
        
        return $generos;
    }

    public static function get_cursos_del_grado($sga_grado_id)
    {
        if ( $sga_grado_id == "Todos" ) {
            $opciones = '<option value="Todos">Todos</option>';
        }else{
            $registros = Curso::where('sga_grado_id', $sga_grado_id)->where('estado', 'Activo')->get();
            $opciones = '<option value="Todos">Todos</option>';
            foreach ($registros as $campo) {
                $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
            }
        }            
        return $opciones;
    }

    public function generacion_carnets(Request $request)
    {
        $imagen_mostrar = $request->imagen_a_mostrar;

        $estudiantes = Matricula::todos_estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id );
        
        if( $request->estudiante_id != '' )
        {
            $estudiantes = $estudiantes->where('id_estudiante', (int)$request->estudiante_id)->all();
        }

        $curso = Curso::find( $request->curso_id );

        $numero_columnas = 2;
        $tamanio_letra = $request->tamanio_letra; // En px
        
        $vista = View::make( 'matriculas.estudiantes.carnets.show', compact( 'estudiantes', 'curso', 'numero_columnas', 'tamanio_letra','imagen_mostrar') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}
