<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;

use App\Sistema\Reporte;
use App\Sistema\Aplicacion;
use App\Sistema\Services\ModeloService;
use Illuminate\Support\Facades\Input;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function vista_reporte()
    {
    	$app = Aplicacion::find(Input::get('id'));
    	$reporte = Reporte::find(Input::get('reporte_id'));

    	$accion = 'create';

    	// Se obtienen los campos asociados a ese reporte
        $lista_campos = $reporte->campos()->orderBy('orden')->get()->toArray();

        $modelo_service = new ModeloService();

        $lista_campos = $modelo_service->ajustar_valores_lista_campos( $lista_campos );
        
        $registro = 'NA';

        // Ajustar los valores según la acción
        $lista_campos = $modelo_service->ajustar_valores_lista_campos_segun_accion( $lista_campos, $registro, '', $accion );

        $miga_pan = [
        				[ 'url' => $app->app.'?id='.Input::get('id')  ,'etiqueta' => $app->descripcion ],
        				[ 'url' => 'NO'  ,'etiqueta' => $reporte->descripcion ]
        			];

        return view( 'core.reportes.vista_reportes', compact( 'reporte','lista_campos','miga_pan') );
    }


    /*
      * Generar documento PDF con la vista almacenada en Cache, según el nombre de listado que se genera en los reportes automáticos
    */
    
}
