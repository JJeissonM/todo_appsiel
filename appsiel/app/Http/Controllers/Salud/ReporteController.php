<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use Form;
use Cache;
use View;

use App\Salud\ConsultaMedica;
use App\Salud\FormulaOptica;

class ReporteController extends Controller
{
	public $total_pacientes_nuevos = 0;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /*
	  * Generar resumen de consultas médicas entre fechas
    */
    public function resumen_consultas( Request $request )
    {
    	//dd( $request->reporte_instancia );

    	$consultas = ConsultaMedica::get_resumen_consultas( $request->fecha_desde, $request->fecha_hasta );

    	$total_consultas = $consultas->count();    	

    	$consultas->each(function ($item, $key) {
    		if($item->tipo == "Primera Vez")
    		{
		    	$this->total_pacientes_nuevos++;
    		}
		});

    	//$total_pacientes_nuevos = $consultas->where('tipo_consulta','Primera Vez');
    	$total_pacientes_antiguos = $total_consultas - $this->total_pacientes_nuevos;

    	//$total_examenes = ResultadoExamenMedico::whereIn('consulta_id')

    	$total_pacientes_nuevos = $this->total_pacientes_nuevos;

        $vista = View::make( 'consultorio_medico.reportes.resumen_consultas', compact( 'consultas', 'total_consultas', 'total_pacientes_nuevos', 'total_pacientes_antiguos') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }


    /*
	  * Generar listado de pacientes con citas de control vencidas a la fecha 
    */
    public function citas_control_vencidas( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        if( $request->fecha_desde == "" )
        {
            $fecha_desde = '1900-01-01';
        }

        $consultas = FormulaOptica::get_citas_control_vencidas( $fecha_desde );

        $vista = View::make( 'consultorio_medico.reportes.citas_control_vencidas', compact( 'consultas' ) )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }


    public function generar_reporte( Request $request )
    {
    	dd( $request->reporte_instancia );
    	//$modelo = Modelo::find( $request-> );
    }
}
