<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;
use Cache;
use NumerosEnLetras;

use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;


// Modelos

use App\Sistema\Aplicacion;
use App\Core\Empresa;

use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomConcepto;
use App\Nomina\NomEntidad;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;

class ReporteController extends Controller
{
   public function reportes()
    {
        $app = Aplicacion::find(Input::get('id'));

        $opciones1 = NomDocEncabezado::all();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id] = $opcion->descripcion;
        }
        $documentos = $vec1;

        $personas = NomContrato::get_empleados( '' );
        $vec2['Todos']='Todos';
        foreach ($personas as $opcion){
            $vec2[$opcion->core_tercero_id] = $opcion->empleado;
        }
        $empleados = $vec2;

        $miga_pan = [
                ['url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                ['url'=>$app->app.'?id='.Input::get('id'),'etiqueta'=>'Informes y listados']
            ];

        return view('nomina.reportes.desprendibles_de_pago', compact('miga_pan', 'documentos', 'empleados') );
    }

    /**
     * ajax_reporte_desprendibles_de_pago
     *
     */
    public function ajax_reporte_desprendibles_de_pago(Request $request)
    {
        return $this->generar_reporte_desprendibles_de_pago($request->nom_doc_encabezado_id,  $request->core_tercero_id);
    }

    /**
     * ajax_reporte_desprendibles_de_pago
     *
     */
    public function nomina_pdf_reporte_desprendibles_de_pago()
    {
        $tabla = $this->generar_reporte_desprendibles_de_pago(Input::get('nom_doc_encabezado_id'), Input::get('core_tercero_id') );

        $vista = '<html>
                    <head>
                        <title>Reporte desprendible de pago</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <style>
                            @page { 
                                margin: 0.7cm;
                            }
                            .page-break {
                                page-break-after: always;
                            }
                            .cuadro {
                                border: 1px solid;
                                border-radius: 10px;
                                padding: 5px;
                            }
                            .table td {
                                padding: 0px;
                            }
                        </style>    
                    </head>
                    <body>
                    
                    '.$tabla.'
                    </body>
                </html>';

        $tam_hoja = 'letter';//array(0, 0, 612.00, 390.00);//'folio';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($vista)->setPaper($tam_hoja,$orientacion);

        return $pdf->download('reporte_desprendibles_de_pago.pdf');
    }


    public function generar_reporte_desprendibles_de_pago($nom_doc_encabezado_id, $core_tercero_id )
    {  
        $documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

        if ( $core_tercero_id == 'Todos') 
        {
            $empleados = $documento->empleados;
        }else{
            $empleados = NomContrato::where('nom_contratos.core_tercero_id', $core_tercero_id)->get();
        }

        $vista = View::make('nomina.reportes.tabla_desprendibles_pagos', compact('documento', 'empleados') )->render();

        return $vista;
    }

    public function listado_acumulados(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;
        $valores_a_mostrar  = $request->valores_a_mostrar;

        $nom_agrupacion_id = (int)$request->nom_agrupacion_id;
        $agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id );

        $detalla_empleados = $request->detalla_empleados;
        $operador2 = '=';

        $movimientos = NomDocRegistro::listado_acumulados( $fecha_desde, $fecha_hasta, $nom_agrupacion_id);

        $conceptos =  NomConcepto::whereIn( 'id', array_keys( $movimientos->groupBy('nom_concepto_id')->toArray() ) )->get();

        $empleados =  NomContrato::whereIn( 'core_tercero_id', array_keys( $movimientos->groupBy('core_tercero_id')->toArray() ) )->get();

        $vista = View::make('nomina.reportes.listado_acumulados2', compact('movimientos','conceptos','empleados','detalla_empleados','agrupacion','fecha_desde', 'fecha_hasta','valores_a_mostrar'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }


    public function libro_fiscal_vacaciones(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $vista = '<h3 style="width: 100%; text-align: center;">
                        LIBRO FISCAL DE VACACIONES
                    </h3>
                    <p style="width: 100%; text-align: center;">
                        Desde: ' . $fecha_desde . ' - Hasta: ' . $fecha_hasta . '
                    </p>
                    <hr>';

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }


    public function resumen_x_entidad_empleado(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $entidades = NomEntidad::where('tipo_entidad','EPS')->get()->pluck('id')->toArray();
        $movimientos_entidades_salud = NomDocRegistro::movimientos_entidades_salud( $fecha_desde, $fecha_hasta, $entidades);
        $entidades_con_movimiento = $movimientos_entidades_salud->groupBy('entidad_salud_id')->toArray();
        $coleccion_movimientos_salud = $this->crear_coleccion_movimientos_entidades( $entidades_con_movimiento );

        $entidades = NomEntidad::where('tipo_entidad','AFP')->get()->pluck('id')->toArray();
        $movimientos_entidades_afp = NomDocRegistro::movimientos_entidades_afp( $fecha_desde, $fecha_hasta, $entidades);
        $entidades_con_movimiento = $movimientos_entidades_afp->groupBy('entidad_pension_id')->toArray();
        $coleccion_movimientos_afp = $this->crear_coleccion_movimientos_entidades( $entidades_con_movimiento );

        $gran_total = $movimientos_entidades_salud->sum('valor_deduccion') + $movimientos_entidades_afp->sum('valor_deduccion');

        switch ( $request->tipo_reporte )
        {
            case 'total_x_entidad':
                    $vista = View::make('nomina.reportes.resumen_entidades', compact( 'coleccion_movimientos_salud', 'coleccion_movimientos_afp', 'fecha_desde', 'fecha_hasta','gran_total') )->render();
                break;

            case 'detallar_empleados':
                    $vista = View::make('nomina.reportes.resumen_entidades_detallar_empleados', compact( 'coleccion_movimientos_salud', 'coleccion_movimientos_afp', 'fecha_desde', 'fecha_hasta','gran_total') )->render();
                break;

            default:
                # code...
                break;
        }

                

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function crear_coleccion_movimientos_entidades( $entidades_con_movimiento )
    {
        $movimientos = collect([]);

        foreach ($entidades_con_movimiento as $entidad_id => $registro)
        {
            $entidad = NomEntidad::find($entidad_id);
            $movimientos[] = (object)[ 'entidad'=> '<b>' . $entidad->descripcion . '</b> / NIT. ' . number_format($entidad->tercero->numero_identificacion,'0',',','.') . ')', 'movimiento'=>$registro ];
        }

        $sorted = $movimientos->sortBy('entidad');

        return $sorted->values()->all();
    }

    public function provisiones_x_entidad_empleado(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $tipos_entidades = [ 12 => "EPS", 13 => "AFP", "ARL", "CCF", 18 => "PARAFISCALES"];

        foreach ($tipos_entidades as $modo_liquidacion_id => $tipo_entidad)
        {
            $entidades = NomEntidad::where('tipo_entidad',$value)->get()->toArray();
            foreach ($entidades as $entidad )
            {
                $movimiento_entidad = 3;
            }
        }

        $movimientos = [];

        $vista = View::make('nomina.reportes.resumen_entidades', compact( 'tipos_entidades', 'movimientos', 'fecha_desde', 'fecha_hasta') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function listado_aportes_parafiscales(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $vista = '<h3 style="width: 100%; text-align: center;">
                        LISTADO DE APORTES PARAFISCALES
                    </h3>
                    <p style="width: 100%; text-align: center;">
                        Desde: ' . $fecha_desde . ' - Hasta: ' . $fecha_hasta . '
                    </p>
                    <hr>';

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}