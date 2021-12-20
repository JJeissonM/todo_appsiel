<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Requests;

use DB;
use PDF;
use View;
use Lava;
use Input;
use Hash;
use Cache;

use App\User;

use Auth;

// Modelos
use App\Core\FirmaAutorizada;
use App\Sistema\SecuenciaCodigo;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Tesoreria\TesoLibretasPago;

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
	
    public static function nuevos_matriculados( $periodo_lectivo )
    {
        $alumnos_por_antiguedad = Estudiante::get_estudiantes_x_antiguedad( $periodo_lectivo );
        // Creación de gráfico de Torta
        $stocksTable = Lava::DataTable();
        
        $stocksTable->addStringColumn('ESTUDIANTES')
                    ->addNumberColumn('CANTIDAD');
        
        foreach($alumnos_por_antiguedad as $registro){
            $stocksTable->addRow([
              $registro[0], (int)$registro[1]
            ]);
        }

        Lava::BarChart('antiguedad', $stocksTable);
        
        return $alumnos_por_antiguedad;
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


    public function matri_constancia_estudios( Request $request )
    {
        $estudiante = Estudiante::get_datos_basicos( (int)$request->estudiante_id );

        $matriculado = true;

        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $matricula = Matricula::get_matricula_periodo_lectivo_un_estudiante( (int)$request->estudiante_id, $request->periodo_lectivo_id );

        $libreta_pago = null;
        if ( !is_null($matricula) )
        {
            $libreta_pago = TesoLibretasPago::where( 'matricula_id', $matricula->id )->get()->first();
        }else{
            $matriculado = false;
        }
        
        $curso = Curso::find( $request->curso_id );

        $tam_hoja = $request->tam_hoja;
        $detalla_valores_matricula_pension = $request->detalla_valores_matricula_pension;

        $array_fecha = [ date('d'), ConfiguracionController::nombre_mes( date('m') ), date('Y') ];

        if ( $request->fecha_expedicion != '' )
        {
            $fecha = explode('-', $request->fecha_expedicion );
            $array_fecha = [ $fecha[2], ConfiguracionController::nombre_mes( $fecha[1] ), $fecha[0] ];            
        }
        
        $firma_autorizada_datos_1 = FirmaAutorizada::get_datos( $request->firma_autorizada_1 );
        $firma_autorizada_1 = FirmaAutorizada::find( $request->firma_autorizada_1 );

        $vista = View::make( 'core.dis_formatos.plantillas.constancia_estudios_estudiante', compact( 'estudiante', 'curso', 'periodo_lectivo', 'array_fecha', 'firma_autorizada_1','firma_autorizada_datos_1', 'tam_hoja', 'libreta_pago', 'detalla_valores_matricula_pension', 'matriculado' )  )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }
}
