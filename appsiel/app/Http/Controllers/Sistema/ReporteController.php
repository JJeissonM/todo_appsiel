<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;

use App\Sistema\Reporte;
use App\Sistema\Aplicacion;
use App\Sistema\Services\ModeloService;
use App\VentasPos\Services\ReportsServices;
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
        $lista_campos = $this->quitar_empleados_repetidos_listado_acumulados( $reporte, $lista_campos );
        
        $registro = 'NA';

        // Ajustar los valores según la acción
        $lista_campos = $modelo_service->ajustar_valores_lista_campos_segun_accion( $lista_campos, $registro, '', $accion );

        $miga_pan = [
        				[ 'url' => $app->app.'?id='.Input::get('id')  ,'etiqueta' => $app->descripcion ],
        				[ 'url' => 'NO'  ,'etiqueta' => $reporte->descripcion ]
        			];

        return view( 'core.reportes.vista_reportes', compact( 'reporte','lista_campos','miga_pan') );
    }

    protected function quitar_empleados_repetidos_listado_acumulados( Reporte $reporte, $lista_campos )
    {
        if ( (int)$reporte->id != 38 ) {
            return $lista_campos;
        }

        foreach ( $lista_campos as $key => $campo ) {
            if ( !isset( $campo['name'] ) || $campo['name'] != 'nom_contrato_id' ) {
                continue;
            }

            if ( !isset( $campo['opciones'] ) || !is_array( $campo['opciones'] ) ) {
                continue;
            }

            $lista_campos[$key]['opciones'] = $this->quitar_opciones_repetidas_por_etiqueta( $campo['opciones'] );
        }

        return $lista_campos;
    }

    protected function quitar_opciones_repetidas_por_etiqueta( $opciones )
    {
        $opciones_unicas = [];
        $etiquetas_agregadas = [];

        foreach ( $opciones as $valor => $etiqueta ) {
            if ( $valor === '' || $valor === null ) {
                $opciones_unicas[$valor] = $etiqueta;
                continue;
            }

            $llave_etiqueta = trim( (string)$etiqueta );

            if ( isset( $etiquetas_agregadas[$llave_etiqueta] ) ) {
                continue;
            }

            $etiquetas_agregadas[$llave_etiqueta] = true;
            $opciones_unicas[$valor] = $etiqueta;
        }

        return $opciones_unicas;
    }

    /**
     * 
     */
    public function print_reporte()
    {
        $data = Input::all();
        
        $reporte_url_form_action = json_decode( $data['reporte_instancia'] )->url_form_action;

        switch ($reporte_url_form_action) {
            case 'pos_resumen_diario':
                $fecha_desde = $data['fecha_desde'];
                $fecha_hasta = $data['fecha_hasta'];
                $pdv_id = $data['pdv_id'];

                return (new ReportsServices())->get_view_for_resumen_diario($fecha_desde, $fecha_hasta, $pdv_id, 'print' );
                break;
            
            default:
                # code...
                break;
        }

        return 'No hay formato de impresión definido para este reporte.';
    }
}
