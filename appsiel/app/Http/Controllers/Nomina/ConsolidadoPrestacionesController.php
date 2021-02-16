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
use NumerosEnLetras;
use Carbon\Carbon;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Sistema\Html\MigaPan;
use App\Sistema\Aplicacion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\PrestacionesLiquidadas;
use App\Nomina\DiaFestivo;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use App\Nomina\ConsolidadoPrestacionesSociales;

class ConsolidadoPrestacionesController extends TransaccionController
{
    protected $valor_consolidado_mes_cesantias = 0;

    /*
        Por cada empleado activo liquida los conceptos automáticos, las cuotas y préstamos
        Además actualiza el total de devengos y deducciones en el documento de nómina
    */
    public function consolidar_prestaciones( Request $request )
    {
        $vista = '';
        $usuario = Auth::user();
        $fecha_final_promedios = $request->fecha_final_promedios;
        $fecha_inicial_promedios = explode( '-', $fecha_final_promedios );

        $empleados_con_movimiento = NomDocRegistro::whereBetween( 'fecha',[ $fecha_inicial_promedios[0].'-'.$fecha_inicial_promedios[1].'-01', $fecha_final_promedios ] )->distinct('nom_contrato_id')->get()->unique('nom_contrato_id')->values()->all();

        $array_prestaciones_liquidadas = (object)[];
        $lista_consolidados = [];
        foreach ($empleados_con_movimiento as $registro_empleado)
        {
            $fecha_final_promedios = $request->fecha_final_promedios;

            $empleado = $registro_empleado->contrato;
            if ( $empleado->estado == 'Retirado' )
            {
                $fecha_liquidacion_contrato = $empleado->fecha_liquidacion_contrato();
                if ( !is_null($fecha_liquidacion_contrato) )
                {
                    $fecha_final_promedios = $fecha_liquidacion_contrato;
                }
            }

            $prestaciones = [ 'vacaciones', 'prima_legal', 'cesantias', 'intereses_cesantias'];
            foreach( $prestaciones as $key => $prestacion )
            {
                $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where( 'concepto_prestacion', $prestacion )
                                                                        ->where( 'grupo_empleado_id', $empleado->grupo_empleado_id )
                                                                        ->get()->first();

                if( is_null( $parametros_prestacion ) )
                {
                    $lista_consolidados[] = (object)[
                                                        'tercero' => (object)[ 'descripcion' => $empleado->tercero->descripcion, 'numero_identificacion' => $empleado->tercero->numero_identificacion],
                                                        'tipo_prestacion'=> $prestacion,
                                                        'fecha_fin_mes' => $fecha_final_promedios,
                                                        'valor_acumulado_mes_anterior' => 0,
                                                        'valor_pagado_mes' => 0,
                                                        'valor_consolidado_mes' => 0,
                                                        'dias_consolidado_mes' => 0,
                                                        'valor_acumulado' => 0,
                                                        'dias_acumulados' => 0,
                                                        'observacion' => '',
                                                        'dias_totales_laborados' => 0,
                                                        'estado' => 'Error. No hay parametros de liquidación para el Grupo de empleados al que pertenece este empleado.'
                                                    ];
                    continue;
                }

                $fecha_inicial = $this->get_fecha_inicial_promedios( $fecha_final_promedios, $empleado, 1 );

                $dias_totales_laborados = $this->calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final_promedios );

                $dias_base_legales = 360;
                if ( $prestacion == 'prima_legal' )
                {
                    $dias_base_legales = 180;
                }

                $dias_totales_liquidacion = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / $dias_base_legales;

                $valor_base_diaria =  $this->get_valor_base_diaria( $empleado, $fecha_inicial, $fecha_final_promedios, $parametros_prestacion->nom_agrupacion_id, $parametros_prestacion->base_liquidacion );

                $valor_consolidado_mes = $dias_totales_liquidacion * $valor_base_diaria;
                if ( $prestacion == 'cesantias' )
                {
                    $this->valor_consolidado_mes_cesantias = $valor_consolidado_mes;
                }

                if ( $prestacion == 'intereses_cesantias' )
                {
                    $valor_consolidado_mes = $this->valor_consolidado_mes_cesantias * 12 / 100;
                    if ( $empleado->estado == 'Retirado' )
                    {
                        $valor_consolidado_mes = $this->valor_consolidado_mes_cesantias * 12 / 100 * $dias_totales_laborados / $dias_base_legales;
                    }
                }

                $registro_consolidado_mes_anterior = $this->get_registro_consolidado_mes_anterior( $fecha_final_promedios, $prestacion, $empleado->id );
                $valor_acumulado_mes_anterior = 0;
                if ( !is_null( $registro_consolidado_mes_anterior ) )
                {
                    $valor_acumulado_mes_anterior = $registro_consolidado_mes_anterior->valor_acumulado;
                }

                $valor_pagado_mes = $this->get_valor_pagado_mes( $fecha_inicial, $fecha_final_promedios, $empleado->id, $parametros_prestacion->nom_concepto_id, $prestacion );

                $valor_acumulado =  $valor_acumulado_mes_anterior - $valor_pagado_mes + $valor_consolidado_mes;
                $dias_acumulados = 0;

                if ( $request->almacenar_registros )
                {
                    $consolidado_mes = ConsolidadoPrestacionesSociales::where([
                                                                        [ 'fecha_fin_mes', '=', $fecha_final_promedios ],
                                                                        [ 'tipo_prestacion', '=', $prestacion ],
                                                                        [ 'nom_contrato_id', '=', $empleado->id ]
                                                                    ])->get()->first();

                    if ( !is_null( $consolidado_mes ) )
                    {
                        $lista_consolidados[] = (object)[
                                                        'tercero' => (object)[ 'descripcion' => $empleado->tercero->descripcion, 'numero_identificacion' => $empleado->tercero->numero_identificacion],
                                                        'tipo_prestacion'=> $prestacion,
                                                        'fecha_fin_mes' => $fecha_final_promedios,
                                                        'valor_acumulado_mes_anterior' => $valor_acumulado_mes_anterior,
                                                        'valor_pagado_mes' => $valor_pagado_mes,
                                                        'valor_consolidado_mes' => $valor_consolidado_mes,
                                                        'dias_consolidado_mes' => $dias_totales_liquidacion,
                                                        'valor_acumulado' => $valor_acumulado,
                                                        'dias_acumulados' => $dias_acumulados,
                                                        'observacion' => '',
                                                        'dias_totales_laborados' => $dias_totales_laborados,
                                                        'estado' => 'Advertencia. La prestación ya tiene almacenados registros consolidados para el mes actual.'
                                                    ];
                        continue;
                    }
                    
                    $lista_consolidados[] = ConsolidadoPrestacionesSociales::create(
                                                            [ 'nom_contrato_id' => $empleado->id ] + 
                                                            [ 'tipo_prestacion' => $prestacion ] + 
                                                            [ 'fecha_fin_mes' => $fecha_final_promedios ] + 
                                                            [ 'valor_acumulado_mes_anterior' => round( $valor_acumulado_mes_anterior, 0) ] + 
                                                            [ 'valor_pagado_mes' => round( $valor_pagado_mes, 0) ] + 
                                                            [ 'valor_consolidado_mes' => round( $valor_consolidado_mes, 0) ] + 
                                                            [ 'dias_consolidado_mes' => $dias_totales_liquidacion ] + 
                                                            [ 'valor_acumulado' => round( $valor_acumulado, 0) ] + 
                                                            [ 'dias_acumulados' => $dias_acumulados ] + 
                                                            [ 'observacion' => '' ] + 
                                                            [ 'dias_totales_laborados' => $dias_totales_laborados ] + 
                                                            [ 'estado' => 'Activo' ]
                                                        );
                }else{
                    $lista_consolidados[] = (object)[
                                                        'tercero' => (object)[ 'descripcion' => $empleado->tercero->descripcion, 'numero_identificacion' => $empleado->tercero->numero_identificacion],
                                                        'tipo_prestacion'=> $prestacion,
                                                        'fecha_fin_mes' => $fecha_final_promedios,
                                                        'valor_acumulado_mes_anterior' => $valor_acumulado_mes_anterior,
                                                        'valor_pagado_mes' => $valor_pagado_mes,
                                                        'valor_consolidado_mes' => $valor_consolidado_mes,
                                                        'dias_consolidado_mes' => $dias_totales_liquidacion,
                                                        'valor_acumulado' => $valor_acumulado,
                                                        'dias_acumulados' => $dias_acumulados,
                                                        'observacion' => '',
                                                        'dias_totales_laborados' => $dias_totales_laborados,
                                                        'estado' => 'Activo'
                                                    ];
                }
            }
        }

        return View::make( 'nomina.reportes.tabla_consolidados_prestaciones_sociales', compact( 'lista_consolidados' ) )->render();
    }

    public function get_valor_pagado_mes( $fecha_inicial, $fecha_final, $nom_contrato_id, $nom_concepto_id, $prestacion )
    {
        $total_devengos = NomDocRegistro::where( 'nom_contrato_id', $nom_contrato_id )
                                            ->where( 'nom_concepto_id', $nom_concepto_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );


        if ( $prestacion == 'cesantias' )
        {
            /*
                    modo_liquidacion_id: [ 15: Cesantías consignadas, 17: Cesantías pagadas ]
            */
            $conceptos_cesantias_pagadas_consignadas = NomConcepto::whereIn( 'modo_liquidacion_id', [ 15, 17 ] )
                                                        ->where( [ ['forma_parte_basico', '<>', 1] ] )
                                                        ->get()->pluck('id')->toArray();

            $total_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_cesantias_pagadas_consignadas )
                                            ->where( 'nom_contrato_id', $nom_contrato_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );
        }

        if ( is_null($total_devengos) )
        {
            return 0;
        }

        return $total_devengos;
    }

    public function get_registro_consolidado_mes_anterior( $fecha_final_promedios, $prestacion, $nom_contrato_id )
    {
        $fecha_mes_anterior = $this->get_fecha_mes_anterior( $fecha_final_promedios );
        return ConsolidadoPrestacionesSociales::where([
                                                        [ 'fecha_fin_mes', '=', $fecha_mes_anterior ],
                                                        [ 'tipo_prestacion', '=', $prestacion ],
                                                        [ 'nom_contrato_id', '=', $nom_contrato_id ]
                                                    ])->get()->first();
    }


    public function get_fecha_mes_anterior( $fecha_final_promedios )
    {
        $vec_fecha = explode("-", $fecha_final_promedios);
        
        $anio_actual = (int)$vec_fecha[0];
        $mes_actual = (int)$vec_fecha[1];
        $dia_actual = (int)$vec_fecha[2];


        $anio_anterior = $anio_actual;
        $mes_anterior = $mes_actual - 1;
        $dia_anterior = 30;

        if ( $mes_actual == 1 ) // Enero
        {
            $anio_anterior--;
            $mes_anterior = 12;
        }

        if ( $mes_actual == 3 ) // Marzo
        {
            $dia_anterior = 28;
        }

        $dia_anterior = $this->formatear_numero_a_texto_dos_digitos( $dia_anterior );
        $mes_anterior = $this->formatear_numero_a_texto_dos_digitos( $mes_anterior );

        return ($anio_anterior . '-' . $mes_anterior . '-' . $dia_anterior);

    }

    public function get_fecha_inicial_promedios( $fecha_final, $empleado )
    {
        $vec_fecha = explode("-", $fecha_final);

        $anio_inicial = $vec_fecha[0];
        $mes_inicial = $vec_fecha[1];
        $dia_inicial = '01';

        $fecha_inicial = $anio_inicial . '-' . $mes_inicial . '-' . $dia_inicial;
        $diferencia = $this->diferencia_en_dias_entre_fechas( $fecha_inicial, $empleado->fecha_ingreso );
        
        // Si la fecha_inicial es menor que la fecha_ingreso del empleado, la fecha inicial debe ser la del contrato
        if ( $diferencia > 0 )
        {
            return $empleado->fecha_ingreso;
        }

        // Si la diferencia es negativa, quiere decir que la fecha_final es superior a la fecha_ingreso
        return $fecha_inicial;
    }

    public function formatear_numero_a_texto_dos_digitos( $numero )
    {
        if ( strlen($numero) == 1 )
        {
            return "0" . $numero;
        }

        return $numero;
    }


    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return $fecha_ini->diffInDays( $fecha_fin, false); // false activa el calculo de diferencias negativas
    }


    public function get_valor_base_diaria( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id, $base_liquidacion )
    {
        $valor_base_diaria = 0;
        $valor_base_diaria_sueldo = 0;

        $cantidad_dias = $this->calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final );

        switch ( $base_liquidacion )
        {
            case 'sueldo':
                
                $valor_base_diaria = $empleado->salario_x_dia();

                break;
            
            case 'sueldo_mas_promedio_agrupacion':
                
                $valor_agrupacion_x_dia = 0;                

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final );
                
                if ( $cantidad_dias != 0 )
                {
                    $valor_agrupacion_x_dia = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                $valor_base_diaria = $empleado->salario_x_dia() + $valor_agrupacion_x_dia;
                break;
            
            case 'promedio_agrupacion':

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final );

                if ( $cantidad_dias != 0 )
                {
                    $valor_base_diaria = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                break;
            
            default:
                # code...
                break;
        }

        return $valor_base_diaria;
    }

    public function get_valor_acumulado_agrupacion_entre_meses( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        $total_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );

        $total_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_deduccion' );

        if ( $empleado->estado == 'Retirado' )
        {
            $concepto_vacaciones_id = $this->get_concepto_vacaciones_id( $empleado );

            $total_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->where( [['nom_concepto_id','<>', $concepto_vacaciones_id]] ) // Se saca vacaciones
                                            ->sum( 'valor_devengo' );
        }

        return ( $total_devengos - $total_deducciones );
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final )
    {
        $cantidad_horas_laboradas = NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                            ->whereBetween( 'nom_doc_registros.fecha', [ $fecha_inicial, $fecha_final ] )
                                            ->where( 'nom_conceptos.forma_parte_basico', 1 )
                                            ->where( 'nom_doc_registros.core_tercero_id', $empleado->core_tercero_id )
                                            ->sum( 'nom_doc_registros.cantidad_horas' );

        if ( $empleado->estado == 'Retirado' )
        {
            $concepto_vacaciones_id = $this->get_concepto_vacaciones_id( $empleado );

            $cantidad_horas_laboradas = NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                            ->whereBetween( 'nom_doc_registros.fecha', [ $fecha_inicial, $fecha_final ] )
                                            ->where( 'nom_conceptos.forma_parte_basico', 1 )
                                            ->where( 'nom_doc_registros.core_tercero_id', $empleado->core_tercero_id )
                                            ->where( [['nom_doc_registros.nom_concepto_id','<>', $concepto_vacaciones_id]] ) // Se saca vacaciones
                                            ->sum( 'nom_doc_registros.cantidad_horas' );
        }

        return ( $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );
    }

    public function get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos;

        $vec_conceptos = [];
        foreach ($conceptos_de_la_agrupacion as $concepto)
        {
            if (!$concepto->forma_parte_basico)
            {
                $vec_conceptos[] = $concepto->id;
            }
        }

        $total_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $vec_conceptos )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );

        $total_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $vec_conceptos )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_deduccion' );

        return ( $total_devengos - $total_deducciones );
    }

    public function get_concepto_vacaciones_id( $empleado )
    {
        $parametros_prestacion = $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
                                                                        ->where('grupo_empleado_id', $empleado->grupo_empleado_id)
                                                                        ->get()->first();
        if ( is_null( $parametros_prestacion) )
        {
            return 0;
        }

        return $parametros_prestacion->nom_concepto_id;
    }


    public function retirar_consolidado_prestaciones( $fecha_final_promedios )
    {
        $fecha_aux = explode( '-', $fecha_final_promedios );

        $fecha_inicial = $fecha_aux[0].'-'.$fecha_aux[1].'-01';

        ConsolidadoPrestacionesSociales::whereBetween('fecha_fin_mes', [ $fecha_inicial, $fecha_final_promedios ] )->delete();

        return '<h3><b>Registros retirados correctamente.</b></h3>';
    }
}