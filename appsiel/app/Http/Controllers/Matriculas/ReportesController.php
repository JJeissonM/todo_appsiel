<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Requests;

use DB;
use PDF;
use View;
use Lava;
use Input;
use Hash;

use App\User;

use Auth;

// Modelos
use App\Sistema\SecuenciaCodigo;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;

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
        $stocksTable = Lava::DataTable();
        
        $stocksTable->addStringColumn('cursos')
                    ->addNumberColumn('Cantidad');
        
        foreach($alumnos_por_curso as $registro){
            $stocksTable->addRow([
              $registro->curso, (int)$registro->Cantidad
            ]);
        }

        Lava::BarChart('MyStocks', $stocksTable);
        
        return $alumnos_por_curso;
    }

    public static function grafica_estudiantes_x_genero( $periodo_lectivo )
    {
        $generos = Estudiante::get_cantidad_estudiantes_x_genero( $periodo_lectivo );

        // Creación de gráfico de Barras
        $stocksTable2 = Lava::DataTable();
        
        $stocksTable2->addStringColumn('Genero')
                    ->addNumberColumn('Cantidad');
        
        foreach($generos as $registro){
            $stocksTable2->addRow([
              $registro->Genero, (int)$registro->Cantidad
            ]);
        }

        Lava::PieChart('Generos', $stocksTable2);
        
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

}
