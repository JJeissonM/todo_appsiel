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
use App\Http\Controllers\Sistema\EmailController;

// Modelos

use App\Sistema\Aplicacion;
use App\Core\Empresa;

use App\Nomina\ModosLiquidacion\PrestacionesSociales\Vacaciones;
use App\Nomina\ModosLiquidacion\PrestacionesSociales\PrimaServicios;
use App\Nomina\ModosLiquidacion\PrestacionesSociales\Cesantias;

use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomConcepto;
use App\Nomina\GrupoEmpleado;
use App\Nomina\NomEntidad;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

use App\Nomina\PilaNovedades;
use App\Nomina\PilaSalud;
use App\Nomina\PilaPension;
use App\Nomina\PilaRiesgoLaboral;
use App\Nomina\PilaParafiscales;
use App\Nomina\EmpleadoPlanilla;

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

        $vista = '';
        foreach ($empleados as $empleado)
        {
            $vista .= View::make('nomina.reportes.tabla_desprendibles_pagos', compact('documento', 'empleado') )->render();
        }

        return $vista;
    }

    /*
        Enviar por email
    */
    public function enviar_por_email_desprendibles_de_pago(Request $request)
    {
        $empresa = Empresa::find( Auth::user()->empresa_id );
        $documento = NomDocEncabezado::find( $request->nom_doc_encabezado_id2 );

        if ( $request->core_tercero_id2 == 'Todos' ) 
        {
            $empleados = $documento->empleados;
        }else{
            $empleados = NomContrato::where('nom_contratos.core_tercero_id', $request->core_tercero_id2)->get();
        }

        $enviados = 0;
        foreach ($empleados as $empleado)
        {
            $vista = View::make('nomina.reportes.tabla_desprendibles_pagos', compact('documento', 'empleado') )->render();

            $tercero = $empleado->tercero;
            if ( $tercero->email != '' )
            {
                $asunto = 'Desprendible de pago de nómina. '.$documento->descripcion;

                $cuerpo_mensaje = 'Hola ' . $tercero->nombre1 . ' ' .  $tercero->otros_nombres . ', <br> Le hacemos llegar su volante de nómina. <br><br> <b>Documento:</b> '. $documento->descripcion . ' <br> <b>Fecha:</b> ' . $documento->fecha . ' <br> Cualquier duda o inquietud, favor remitirla al área de talento humano. <br><br> Atentamente, <br><br> ANALISTA DE NÓMINA <br> ' . $empresa->descripcion . ' <br> Tel. ' . $empresa->telefono1 . ' <br> Email: ' . $empresa->email;

                $vec = EmailController::enviar_por_email_documento( $empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $vista );                

                if ($vec['tipo_mensaje'] == 'flash_message' )
                {
                    $enviados++;
                }
            }
        }            

        return 'Se envío el desprendible a cada empleado con email registrado. Total envíos: ' . $enviados;
    }

    public function listado_acumulados(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;
        $valores_a_mostrar  = $request->valores_a_mostrar;

        $nom_contrato_id = (int)$request->nom_contrato_id;

        $nom_agrupacion_id = (int)$request->nom_agrupacion_id;
        $agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id );

        $detalla_empleados = $request->detalla_empleados;
        $operador2 = '=';

        $movimientos = NomDocRegistro::listado_acumulados( $fecha_desde, $fecha_hasta, $nom_agrupacion_id);

        $conceptos =  NomConcepto::whereIn( 'id', array_keys( $movimientos->groupBy('nom_concepto_id')->toArray() ) )->get();

        $empleados =  NomContrato::whereIn( 'core_tercero_id', array_keys( $movimientos->groupBy('core_tercero_id')->toArray() ) )->get();

        if ( $nom_contrato_id != 0 )
        {
            $empleados =  NomContrato::where( 'id', $nom_contrato_id )->get();
        }

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
        

        $entidades = NomEntidad::where('tipo_entidad','AFP')->get()->pluck('id')->toArray();
        $movimientos_entidades_afp = NomDocRegistro::movimientos_entidades_afp( $fecha_desde, $fecha_hasta, $entidades);

        $gran_total = $movimientos_entidades_salud->sum('valor_deduccion') + $movimientos_entidades_afp->sum('valor_deduccion');

        switch ( $request->tipo_reporte )
        {
            case 'total_x_entidad':
                    
                    $entidades_con_movimiento = $movimientos_entidades_salud->groupBy('entidad_salud_id')->toArray();
                    $coleccion_movimientos_salud = $this->crear_coleccion_movimientos_entidades( $entidades_con_movimiento );

                    $entidades_con_movimiento = $movimientos_entidades_afp->groupBy('entidad_pension_id')->toArray();
                    $coleccion_movimientos_afp = $this->crear_coleccion_movimientos_entidades( $entidades_con_movimiento );

                    $vista = View::make('nomina.reportes.resumen_entidades', compact( 'coleccion_movimientos_salud', 'coleccion_movimientos_afp', 'fecha_desde', 'fecha_hasta','gran_total') )->render();
                break;

            case 'detallar_empleados':

                    $entidades_con_movimiento = $movimientos_entidades_salud->groupBy('entidad_salud_id');                
                    $coleccion_movimientos_salud = $this->crear_coleccion_movimientos_entidades_terceros( $entidades_con_movimiento );

                    $entidades_con_movimiento = $movimientos_entidades_afp->groupBy('entidad_pension_id');
                    $coleccion_movimientos_afp = $this->crear_coleccion_movimientos_entidades_terceros( $entidades_con_movimiento );
                    
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
            $movimientos[] = (object)[ 'entidad_id'=> $entidad_id, 'entidad'=> '<b>' . $entidad->descripcion . '</b> / NIT. ' . number_format($entidad->tercero->numero_identificacion,'0',',','.') . ')', 'movimiento'=>$registro ];
        }

        $sorted = $movimientos->sortBy('entidad');

        return $sorted->values()->all();
    }

    public function crear_coleccion_movimientos_entidades_terceros( $entidades_con_movimiento )
    {
        $movimientos = collect([]);

        foreach ($entidades_con_movimiento as $entidad_id => $movimiento_entidad)
        {
            
            $entidad = NomEntidad::find($entidad_id);

            $agrupado_por_terceros = $movimiento_entidad->groupBy('core_tercero_id');
            $mov_terceros = [];
            $total_deduccion_entidad = 0;
            foreach ( $agrupado_por_terceros as $registros_tercero )
            {
                $valor_deduccion = 0;
                foreach ($registros_tercero as $registro_tercero)
                {
                    $nom_contrato_id = $registro_tercero->nom_contrato_id;
                    $valor_deduccion += $registro_tercero->valor_deduccion;
                    $total_deduccion_entidad += $registro_tercero->valor_deduccion;
                }

                $empleado = NomContrato::find( $nom_contrato_id );

                $mov_terceros[] = (object)[
                                            'descripcion_tercero' => '<b>' . $empleado->tercero->descripcion . '</b> / CC. ' . number_format($empleado->tercero->numero_identificacion,'0',',','.') . ')',
                                            'total_deduccion' => $valor_deduccion
                                            ];
            }

            $movimientos[] = (object)[ 
                                        'entidad_id' => $entidad_id,
                                        'entidad' => '<b>' . $entidad->descripcion . '</b> / NIT. ' . number_format($entidad->tercero->numero_identificacion,'0',',','.') . ')',
                                        'total_deduccion_entidad' => $total_deduccion_entidad,
                                        'movimiento' => $mov_terceros ];
        }

        $sorted = $movimientos->sortBy('entidad');

        return $sorted->values()->all();
    }

    public function consolidado_prestaciones_sociales(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $nom_contrato_id = null;
        if ( $request->nom_contrato_id != '' )
        {
            $nom_contrato_id = (int)$request->nom_contrato_id;
        }
        
        $movimiento = NomDocRegistro::listado_acumulados( $fecha_desde, $fecha_hasta, '', $nom_contrato_id);

        $empleados_con_movimiento = $movimiento->unique('nom_contrato_id')->values()->all();

        $dias_calendario = $this->calcular_dias_laborados_calendario_30_dias( $fecha_desde, $fecha_hasta );
        $cantidad_meses_a_promediar = round( $dias_calendario / 30, 0);
        
        $prestaciones = ['vacaciones','prima_legal','cesantias','intereses_cesantias'];
        $datos = [];
        foreach ($empleados_con_movimiento as $registro_empleado)
        {
            foreach ($prestaciones as $key => $prestacion)
            {
                $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion',$prestacion )
                                                                        ->where('grupo_empleado_id',$registro_empleado->contrato->grupo_empleado_id)
                                                                        ->get()->first();
                if ( is_null( $parametros_prestacion) )
                {
                    continue;
                }

                $parametros_prestacion->cantidad_meses_a_promediar = $cantidad_meses_a_promediar;

                $dias_laborados = $this->calcular_dias_reales_laborados( $registro_empleado->contrato, $fecha_desde, $fecha_hasta, $parametros_prestacion->nom_agrupacion_id );
                
                switch ( $prestacion )
                {
                    case 'vacaciones':
                        $descripcion_prestacion = 'Vacaciones';
                        $dias_derecho = $dias_laborados * 15 / 360;
                        $liquidacion_prestacion = new Vacaciones;
                        $base_diaria = $liquidacion_prestacion->get_valor_base_diaria( $registro_empleado->contrato, $fecha_hasta, 'normal', $parametros_prestacion );
                        break;
                    
                    case 'prima_legal':
                        $descripcion_prestacion = 'Prima de servicios';
                        $dias_derecho = $dias_laborados * 15 / 180;
                        $liquidacion_prestacion = new PrimaServicios;
                        $base_diaria = $liquidacion_prestacion->get_valor_base_diaria( $registro_empleado->contrato, $fecha_hasta, 'normal', $parametros_prestacion );
                        break;
                    
                    case 'cesantias':
                        $descripcion_prestacion = 'Cesantías';
                        $dias_derecho = $dias_laborados * 30 / 360;
                        $liquidacion_prestacion = new Cesantias;
                        $base_diaria = $liquidacion_prestacion->get_valor_base_diaria( $registro_empleado->contrato, $fecha_hasta, 'normal', $parametros_prestacion );
                        break;
                    
                    case 'intereses_cesantias':
                        $descripcion_prestacion = 'Intereses de cesantías';
                        $dias_derecho = $dias_laborados * 30 / 360;
                        $liquidacion_prestacion = new Cesantias;
                        $base_diaria = $liquidacion_prestacion->get_valor_base_diaria( $registro_empleado->contrato, $fecha_hasta, 'normal', $parametros_prestacion ) * ( 12 / 100 );
                        break;
                    
                    default:
                        # code...
                        break;
                }

                $valor_provision = $dias_derecho * $base_diaria;

                $datos[] = (object)[ 
                                        'empleado_numero_identificacion' => $registro_empleado->tercero->numero_identificacion,
                                        'empleado_descripcion' => $registro_empleado->tercero->descripcion,
                                        'concepto' => $descripcion_prestacion,
                                        'dias_laborados' => $dias_laborados,
                                        'dias_derecho' => $dias_derecho,
                                        'base_diaria' => $base_diaria,
                                        'valor_provision' => $valor_provision
                                    ];
            }
        }
        //dd($datos);

        $vista = View::make('nomina.reportes.consolidado_prestaciones_sociales', compact( 'datos', 'fecha_desde', 'fecha_hasta','cantidad_meses_a_promediar') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos;

        // El tiempo se calcula para los concepto que forman parte del básico
        $vec_conceptos = [];
        foreach ($conceptos_de_la_agrupacion as $concepto)
        {
            if ($concepto->forma_parte_basico)
            {
                $vec_conceptos[] = $concepto->id;
            }
        }

        $cantidad_horas_laboradas = NomDocRegistro::whereIn( 'nom_concepto_id', $vec_conceptos )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'cantidad_horas' );

        return ( $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );
    }



    public function calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $fecha_final )
    {
        $vec_fecha_inicial = explode("-", $fecha_inicial);
        $vec_fecha_final = explode("-", $fecha_final);

        // Días iniciales = (Año ingreso x 360) + ((Mes ingreso-1) x 30) + días ingreso
        $dias_iniciales = ( (int)$vec_fecha_inicial[0] * 360 ) + ( ( (int)$vec_fecha_inicial[1] - 1 ) * 30) + (int)$vec_fecha_inicial[2];

        // Días finales = (Año ingreso x 360) + ((Mes ingreso-1) x 30) + días ingreso
        $dias_finales = ( (int)$vec_fecha_final[0] * 360 ) + ( ( (int)$vec_fecha_final[1] - 1 ) * 30) + (int)$vec_fecha_final[2];

        $dias_totales_laborados = ($dias_finales - $dias_iniciales) + 1;

        return $dias_totales_laborados;
    }

    public function listado_aportes_pila(Request $request)
    {
        $fecha_final_mes = $request->fecha_final_mes;

        $coleccion_movimientos_salud = PilaSalud::where('fecha_final_mes',$fecha_final_mes)
                                    ->orderBy('codigo_entidad_salud')
                                    ->get();

        $coleccion_movimientos_pension = PilaPension::where('fecha_final_mes',$fecha_final_mes)
                                    ->orderBy('codigo_entidad_pension')
                                    ->get();

        $coleccion_movimientos_riesgos_laborales = PilaRiesgoLaboral::where('fecha_final_mes',$fecha_final_mes)
                                    ->orderBy('codigo_arl')
                                    ->get();

        $coleccion_movimientos_parafiscales = PilaParafiscales::where('fecha_final_mes',$fecha_final_mes)
                                    ->get();

        //dd($coleccion_movimientos_riesgos_laborales);
        $vista = View::make('nomina.reportes.aportes_pila', compact( 'coleccion_movimientos_salud', 'coleccion_movimientos_pension', 'coleccion_movimientos_riesgos_laborales', 'coleccion_movimientos_parafiscales', 'fecha_final_mes' ) )->render();

        //dd($coleccion_movimientos_salud);

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }



    public function resumen_liquidaciones(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $forma_visualizacion  = $request->forma_visualizacion;

        $nom_contrato_id = null;
        if ( $request->nom_contrato_id != '' )
        {
            $nom_contrato_id = (int)$request->nom_contrato_id;
        }

        $movimiento = NomDocRegistro::listado_acumulados( $fecha_desde, $fecha_hasta, '', $nom_contrato_id);

        switch ($forma_visualizacion)
        {
            case 'empleados_conceptos':
                $datos = $this->get_datos_empleados_conceptos( $movimiento );
                break;
            
            
            case 'grupo_empleados_conceptos':
                $datos = $this->get_datos_grupos_empleados_conceptos( $movimiento );
                break;
            
            default:
                # code...
                break;
        }
        

        $vista = View::make('nomina.reportes.resumen_liquidaciones', compact( 'datos', 'fecha_desde', 'fecha_hasta', 'forma_visualizacion') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function get_datos_empleados_conceptos( $movimiento )
    {
        $empleados_con_movimiento = $movimiento->unique('nom_contrato_id')->values()->all();
        $datos = [];
        foreach ($empleados_con_movimiento as $registro_empleado)
        {
            $conceptos_liquidados = $movimiento->where('nom_contrato_id',$registro_empleado->nom_contrato_id)->unique('nom_concepto_id')->sortByDesc('valor_devengo')->values()->all();
            
            foreach ($conceptos_liquidados as $registro_concepto)
            {
                $datos[] = (object)[ 
                                        'empleado_numero_identificacion' => $registro_concepto->tercero->numero_identificacion,
                                        'empleado_descripcion' => $registro_concepto->tercero->descripcion,
                                        'concepto' => $registro_concepto->nom_concepto_id . ' - ' . $registro_concepto->concepto->descripcion,
                                        'cantidad_horas' => $movimiento->where('nom_contrato_id',$registro_concepto
                                                                    ->nom_contrato_id)
                                                                        ->where('nom_concepto_id',$registro_concepto->nom_concepto_id)
                                                                        ->sum('cantidad_horas'),
                                        'valor_devengo' => $movimiento->where('nom_contrato_id',$registro_concepto->nom_contrato_id)
                                                                        ->where('nom_concepto_id',$registro_concepto->nom_concepto_id)
                                                                        ->sum('valor_devengo'),
                                        'valor_deduccion' => $movimiento->where('nom_contrato_id',$registro_concepto->nom_contrato_id)
                                                                        ->where('nom_concepto_id',$registro_concepto->nom_concepto_id)
                                                                        ->sum('valor_deduccion'),
                                    ];
            }
        }

        return $datos;
    }

    public function get_datos_grupos_empleados_conceptos( $movimiento )
    {
        $empleados_con_movimiento = $movimiento->unique('nom_contrato_id')->values()->all();

        $array_grupos_empleados = [];
        $aux = [];
        foreach ($empleados_con_movimiento as $linea)
        {
            if ( !in_array($linea->contrato->grupo_empleado_id, $aux) )
            {
                $aux[] = $linea->contrato->grupo_empleado_id;
                $array_grupos_empleados[] = GrupoEmpleado::find( $linea->contrato->grupo_empleado_id );
            }            
        }

        //dd( $empleados_con_movimiento->chunck('nom_contrato_id') );

        $datos = [];
        foreach ($array_grupos_empleados as $grupo_empleado)
        {
            $registros_grupo_empleado = [];
            foreach ($movimiento as $registro_movimiento)
            {
                if ( $registro_movimiento->contrato->grupo_empleado_id == $grupo_empleado->id )
                {
                    $registros_grupo_empleado[] = $registro_movimiento;
                }
            }
            $datos[] = (object)[ 'grupo_empleado' => $grupo_empleado, 'datos' => $registros_grupo_empleado];
        }

        $registros_grupo_empleado_2 = [];
        foreach ($datos as $linea)
        {
            $array_conceptos = [];
            $aux = [];
            foreach ( $linea->datos as $registro_concepto )
            {
                if( in_array($registro_concepto->nom_concepto_id, $aux ) )
                {
                    $array_conceptos[$registro_concepto->nom_concepto_id]['cantidad_horas'] += $registro_concepto->cantidad_horas;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['valor_devengo'] += $registro_concepto->valor_devengo;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['valor_deduccion'] += $registro_concepto->valor_deduccion;
                }else{
                    $aux[] = $registro_concepto->nom_concepto_id;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['nom_registro_id'] = $registro_concepto->id;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['concepto'] = $registro_concepto->concepto;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['cantidad_horas'] = $registro_concepto->cantidad_horas;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['valor_devengo'] = $registro_concepto->valor_devengo;
                    $array_conceptos[$registro_concepto->nom_concepto_id]['valor_deduccion'] = $registro_concepto->valor_deduccion;
                }
            }
            //dd($array_conceptos);
            ksort($array_conceptos);
            $registros_grupo_empleado_2[] = (object)[ 'grupo_empleado' => $linea->grupo_empleado, 'datos' => $array_conceptos];  
        }

        $datos_2 = [];
        foreach ($registros_grupo_empleado_2 as $registro_grupo_empleado)
        {
            foreach ($registro_grupo_empleado->datos as $registro_concepto)
            {
                $datos_2[] = (object)[ 
                                        'grupo_empleado' => $registro_grupo_empleado->grupo_empleado->descripcion,
                                        'concepto' => $registro_concepto['concepto']->id . ' - ' . $registro_concepto['concepto']->descripcion,
                                        'cantidad_horas' => $registro_concepto['cantidad_horas'],
                                        'valor_devengo' => $registro_concepto['valor_devengo'],
                                        'valor_deduccion' => $registro_concepto['valor_deduccion']
                                    ];
            }
        }
        return $datos_2;
    }
}