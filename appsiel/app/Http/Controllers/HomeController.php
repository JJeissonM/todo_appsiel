<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

use DB;
use View;
use Lava;
use Storage;
use Input;

use App\User;
use App\Sistema\Aplicacion;
use App\Core\Empresa;
use App\Core\Colegio;
use App\Sistema\Modelo;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inicio()
    {
    	$modelo_empresa_id = 41;

    	$aplicaciones = Aplicacion::where('estado','Activo')->orderBy('orden','ASC')->get()->toArray();

    	$empresa = Empresa::find(Auth::user()->empresa_id);

    	if ( !is_null($empresa) )
    	{
    		$model_empresa = Modelo::find( $modelo_empresa_id );
	    	
	    	$url = asset(config('configuracion.url_instancia_cliente').'/storage/app/'.$model_empresa->ruta_storage_imagen.$empresa->imagen);
	    	
	        $logo = $url.'?'.rand(1,1000);

    		return view('inicio',compact('aplicaciones','empresa','logo'));
    	}else{
    		// Si no existe empresa, se debe crear
    		$app_configuracion_id = 7;
    		return redirect('web/create?id='.$app_configuracion_id.'&id_modelo='.$modelo_empresa_id)->with('mensaje_error','No existe ninguna empresa creada en el sistema. Debe crear una.');
    	}
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    	$usuario = Auth::user();

    	if(!$usuario->hasRole('Profesor')){

	        /**   ALGUNAS ESTADISTICAS            **/
	        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        	$colegio = $colegio[0];

			$id_colegio= $colegio->id;
			$anio_actual = date('Y');
			//                 Alumnos por cursos
			$alumnos_por_curso=DB::select(DB::raw('SELECT cursos.descripcion AS curso, 
								COUNT(matriculas.id_estudiante) AS Cantidad FROM cursos,matriculas 
								WHERE matriculas.id_colegio='.$id_colegio.' AND matriculas.estado="Activa" AND cursos.id=matriculas.curso_id 
									GROUP BY matriculas.curso_id'));							
			// Creación de gráfico de Torta
			$stocksTable = Lava::DataTable();
			
			$stocksTable->addStringColumn('cursos')
						->addNumberColumn('Cantidad');
			
			foreach($alumnos_por_curso as $registro){
				$stocksTable->addRow([
				  $registro->curso, (float)$registro->Cantidad
				]);
			}
			
			$slices = [['offset'=> 0.2],
						['offset'=> 0.35],
						['offset'=> 0.3],[],
						['offset'=> 0.1]
						];
			$chart = Lava::PieChart('MyStocks', $stocksTable,[
					'is3D'                  => True,
					'pieSliceText'          => 'value',
					'slices'				=> $slices
				]);
			
			//                 Mujeres y hombres
			$generos=DB::select(DB::raw('SELECT sga_estudiantes.genero AS Genero, 
							COUNT(matriculas.id_estudiante) AS Cantidad 
							FROM sga_estudiantes,matriculas 
							WHERE matriculas.id_colegio='.$id_colegio.' AND matriculas.estado="Activa" AND sga_estudiantes.id=matriculas.id_estudiante
								GROUP BY sga_estudiantes.genero'));
			// Creación de gráfico de Barras
			$stocksTable2 = Lava::DataTable();
			
			$stocksTable2->addStringColumn('Genero')
						->addNumberColumn('Cantidad');
			
			foreach($generos as $registro){
				$stocksTable2->addRow([
				  $registro->Genero, $registro->Cantidad
				]);
			}
			$chart2 = Lava::BarChart('Generos', $stocksTable2);
			
			
			/**************************************/
			
			$miga_pan = [
                        ['url'=>'NO','etiqueta'=>'Dashboard']
                    ];
            
            return view('dashboard',compact('generos','alumnos_por_curso','miga_pan'));
		}else{
			
		}
    }
}
