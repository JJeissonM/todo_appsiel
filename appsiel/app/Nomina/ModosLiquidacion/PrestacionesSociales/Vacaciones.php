<?php

namespace App\Nomina\ModosLiquidacion\PrestacionesSociales;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

use Auth;
use Carbon\Carbon;

use App\Nomina\NomDocRegistro;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\LibroVacacion;
use App\Nomina\CambioSalario;
use App\Nomina\ProgramacionVacacion;

class Vacaciones implements Estrategia
{
    const DIAS_BASE_LEGALES = 360;

    protected $historial_vacaciones;
    protected $tabla_resumen = [];

    /*
        ** Hay vacaciones Compensadas y Disfrutadas
        ** Tener en cuenta días disfrutados (calendario) y días propios de las vacaciones
    */
	public function calcular(LiquidacionPrestacionSocial $liquidacion)
	{
        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion',$liquidacion['prestacion'])
                                                                        ->where('grupo_empleado_id',$liquidacion['empleado']->grupo_empleado_id)
                                                                        ->get()->first();

        $this->tabla_resumen['mensaje_error'] = '';

        if( is_null( $parametros_prestacion ) )
        {
            return [
                    [
                        'cantidad_horas' => 0,
                        'valor_devengo' => 0,
                        'valor_deduccion' => 0,
                        'tabla_resumen' => $this->tabla_resumen
                    ]
                ];
        }

        $this->historial_vacaciones = LibroVacacion::where( 'nom_contrato_id', $liquidacion['empleado']->id )->get();

        $dias_pendientes = $this->get_dias_pendientes( $liquidacion['empleado'], $liquidacion['documento_nomina'], $parametros_prestacion );
        $this->tabla_resumen['dias_pendientes'] = $dias_pendientes;

        $valor_base_diaria =  $this->get_valor_base_diaria( $liquidacion['empleado'], $liquidacion['documento_nomina'], $parametros_prestacion );

        $this->tabla_resumen['valor_base_diaria'] = $valor_base_diaria;


        if ( $liquidacion['documento_nomina']->tipo_liquidacion == 'terminacion_contrato' )
        {
            $valor_total_vacaciones = $dias_pendientes * $valor_base_diaria;
            $this->tabla_resumen['valor_total_vacaciones'] = $valor_total_vacaciones;

            $this->tabla_resumen['vlr_dias_habiles'] = $valor_total_vacaciones;
            $this->tabla_resumen['vlr_dias_no_habiles'] = 0;

            $valores = get_valores_devengo_deduccion( 'devengo',  $valor_total_vacaciones );

            return [ 
                        [
                            'cantidad_horas' => $dias_pendientes * (int)config('nomina.horas_dia_laboral'), // pendiente
                            'valor_devengo' => $valores->devengo,
                            'valor_deduccion' => $valores->deduccion,
                            'tabla_resumen' => $this->tabla_resumen  
                        ]
                    ];
        }


        $programacion_vacaciones = ProgramacionVacacion::where( [
                                                                    [ 'nom_contrato_id', '=', $liquidacion['empleado']->id ],
                                                                    [ 'tipo_novedad_tnl', '=', 'vacaciones' ],
                                                                    [ 'cantidad_dias_pendientes_amortizar', '>', 0 ]
                                                                ] )
                                                        ->get()
                                                        ->first();
        if ( is_null($programacion_vacaciones) )
        {
            $this->tabla_resumen['mensaje_error'] = '<br>Empleado ' . $liquidacion['empleado']->tercero->descripcion  . ' no tiene vacaciones programadas.';
            return [ 
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0,
                            'tabla_resumen' => $this->tabla_resumen
                        ]
                    ];
        }

        $libro_vacaciones = LibroVacacion::where( 'novedad_tnl_id', $programacion_vacaciones->id )->get()->first();

        $this->tabla_resumen['fecha_inicial_disfrute'] = $programacion_vacaciones->fecha_inicial_tnl;
        $this->tabla_resumen['fecha_final_disfrute'] = $programacion_vacaciones->fecha_final_tnl;

        $this->tabla_resumen['dias_habiles'] = $libro_vacaciones->dias_pagados;
        $this->tabla_resumen['dias_no_habiles'] = $libro_vacaciones->dias_no_habiles;
        $this->tabla_resumen['vlr_dias_habiles'] = $valor_base_diaria * $libro_vacaciones->dias_pagados;
        $this->tabla_resumen['vlr_dias_no_habiles'] = $valor_base_diaria * $libro_vacaciones->dias_no_habiles;

        $this->tabla_resumen['valor_total_vacaciones'] = $this->tabla_resumen['vlr_dias_habiles'] + $this->tabla_resumen['vlr_dias_no_habiles'];


        if ( $liquidacion['almacenar_registros'] )
        {
            // Actualiza programación de vacaciones (TNL)
            $programacion_vacaciones->cantidad_dias_amortizados += $libro_vacaciones->dias_pagados;
            $programacion_vacaciones->cantidad_dias_pendientes_amortizar -= $libro_vacaciones->dias_pagados;
            $programacion_vacaciones->save();

            // Almacenar registro para los días no hábiles
            $this->crear_registro_concepto_vacaciones_dias_no_habiles( $programacion_vacaciones->id, $liquidacion['documento_nomina'], $liquidacion['empleado'], $libro_vacaciones->dias_no_habiles * (int)config('nomina.horas_dia_laboral') );

            // Actualizar libro de vacaciones---------------FALTA
            //$libro_vacaciones->periodo_pagado_desde = 
        }
        

        $valores = get_valores_devengo_deduccion( 'devengo', $this->tabla_resumen['valor_total_vacaciones'] );

        return [
                    [
                        'cantidad_horas' => $libro_vacaciones->dias_pagados * (int)config('nomina.horas_dia_laboral'), // pendiente
                        'valor_devengo' => $valores->devengo,
                        'valor_deduccion' => $valores->deduccion,
                        'tabla_resumen' => $this->tabla_resumen
                    ]
                ];
	}

    public function crear_registro_concepto_vacaciones_dias_no_habiles( $novedad_tnl_id, $documento_nomina, $empleado, $cantidad_horas_a_liquidar)
    {
        if ( $this->tabla_resumen['vlr_dias_no_habiles'] > 0 )
        {
            $registro = NomDocRegistro::create(
                            [ 'nom_doc_encabezado_id' => $documento_nomina->id ] + 
                            [ 'fecha' => $documento_nomina->fecha] + 
                            [ 'core_empresa_id' => $documento_nomina->core_empresa_id] +  
                            [ 'nom_concepto_id' => (int)config('nomina.concepto_vacaciones_dias_no_habiles') ] + 
                            [ 'core_tercero_id' => $empleado->core_tercero_id ] + 
                            [ 'nom_contrato_id' => $empleado->id ] +
                            [ 'estado' => 'Activo' ] + 
                            [ 'creado_por' => Auth::user()->email ] + 
                            [ 'modificado_por' => '' ] +
                            [ 'cantidad_horas' => $cantidad_horas_a_liquidar ] +
                            [ 'valor_devengo' => $this->tabla_resumen['vlr_dias_no_habiles'] ] +
                            [ 'valor_deduccion' => 0 ] +
                            [ 'novedad_tnl_id' => $novedad_tnl_id ]
                        );
        }
    }

    public function get_valor_base_diaria( $empleado, $documento_nomina, $parametros_prestacion )
    {
        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }

        $valor_base_diaria = 0;
        $valor_base_diaria_sueldo = 0;

        $fecha_inicial = $this->get_fecha_inicial_promedios( $documento_nomina->fecha, $parametros_prestacion->cantidad_meses_a_promediar );
        if ( $fecha_inicial < $empleado->fecha_ingreso)
        {
            $fecha_inicial = $empleado->fecha_ingreso;
        }
        $fecha_final = $documento_nomina->fecha;
        $this->tabla_resumen['fecha_inicial_promedios'] = $fecha_inicial;
        $this->tabla_resumen['fecha_final_promedios'] = $fecha_final;

        $nom_agrupacion_id = $parametros_prestacion->nom_agrupacion_id;
        if ( $documento_nomina->tipo_liquidacion == 'terminacion_contrato' )
        {
            $nom_agrupacion_id = $parametros_prestacion->nom_agrupacion2_id;
        }

        $cantidad_dias = $this->calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id );

        $this->tabla_resumen['cantidad_dias'] = $cantidad_dias;

        $this->tabla_resumen['base_liquidacion'] = $parametros_prestacion->base_liquidacion;

        $this->tabla_resumen['cantidad_dias_salario'] = (int)config('nomina.horas_laborales') / (int)config('nomina.horas_dia_laboral');

        switch ($parametros_prestacion->base_liquidacion)
        {
            case 'sueldo':
                
                $valor_base_diaria = $empleado->salario_x_dia();

                $this->tabla_resumen['descripcion_agrupacion'] = '';
                $this->tabla_resumen['valor_acumulado_salario'] = $empleado->sueldo;
                $this->tabla_resumen['valor_acumulado_agrupacion'] = 0;
                $this->tabla_resumen['valor_salario_x_dia'] = $empleado->salario_x_dia();
                $this->tabla_resumen['valor_agrupacion_x_dia'] = 0;

                break;
            
            case 'sueldo_mas_promedio_agrupacion':
                
                $valor_agrupacion_x_dia = 0;                

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );
                
                if ( $cantidad_dias != 0 )
                {
                    $valor_agrupacion_x_dia = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                $this->tabla_resumen['descripcion_agrupacion'] = AgrupacionConcepto::find($parametros_prestacion->nom_agrupacion_id)->descripcion;
                $this->tabla_resumen['valor_acumulado_salario'] = $empleado->sueldo;
                $this->tabla_resumen['valor_acumulado_agrupacion'] = $valor_acumulado_agrupacion;
                $this->tabla_resumen['valor_salario_x_dia'] = $empleado->salario_x_dia();
                $this->tabla_resumen['valor_agrupacion_x_dia'] = $valor_agrupacion_x_dia;

                if ( $valor_acumulado_agrupacion == 0 )
                {
                    $this->tabla_resumen['cantidad_dias'] = 0;
                }

                $valor_base_diaria = $empleado->salario_x_dia() + $valor_agrupacion_x_dia;
                break;
            
            case 'promedio_agrupacion':

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );

                if ( $cantidad_dias != 0 )
                {
                    $valor_base_diaria = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                $this->tabla_resumen['descripcion_agrupacion'] = AgrupacionConcepto::find($parametros_prestacion->nom_agrupacion_id)->descripcion;
                $this->tabla_resumen['valor_acumulado_salario'] = 0;
                $this->tabla_resumen['valor_acumulado_agrupacion'] = $valor_acumulado_agrupacion;
                $this->tabla_resumen['valor_salario_x_dia'] = 0;
                $this->tabla_resumen['valor_agrupacion_x_dia'] = $valor_base_diaria;
                $this->tabla_resumen['cantidad_dias_salario'] = 0;

                if ( $valor_acumulado_agrupacion == 0 )
                {
                    $this->tabla_resumen['cantidad_dias'] = 0;
                }

                break;
            
            default:
                # code...
                break;
        }

        return $valor_base_diaria;
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

        return ( $total_devengos - $total_deducciones );
    }

    public function get_fecha_inicial_promedios( $fecha_final, $cantidad_meses_a_promediar )
    {
        $vec_fecha_documento = explode("-", $fecha_final);
        
        $anio_final = (int)$vec_fecha_documento[0];
        $mes_final = (int)$vec_fecha_documento[1];
        $dia_final = $vec_fecha_documento[2];

        $anio_inicial = $anio_final;
        $mes_inicial = 0;
        $dia_inicial = '01';

        $mes_anterior = $mes_final + 1;
        for ( $i = $cantidad_meses_a_promediar; $i > 0; $i--)
        {
            $mes_iteracion = $mes_anterior - 1;
            if ( $mes_iteracion <= 0 )
            {
                $mes_inicial = 12 + $mes_iteracion;
                $anio_inicial = $anio_final - 1;
            }else{
                $mes_inicial = $mes_iteracion;
            }
            $mes_anterior = $mes_iteracion;
        }

        $mes_inicial = $this->formatear_numero_a_texto_dos_digitos( $mes_inicial );
        $anio_inicial =  $this->formatear_numero_a_texto_dos_digitos( $anio_inicial );

        return ( $anio_inicial . '-' . $mes_inicial . '-' . $dia_inicial );
    }

    public function formatear_numero_a_texto_dos_digitos( $numero )
    {
        if ( strlen($numero) == 1 )
        {
            return "0" . $numero;
        }

        return $numero;
    }

    public function get_dias_pendientes( $empleado, $documento_nomina, $parametros_prestacion )
    {

        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }

        $dias_pagados_vacaciones = 0;

        if ( !is_null( $this->historial_vacaciones ) )
        {
            $dias_pagados_vacaciones = $this->historial_vacaciones->sum('dias_pagados');
        }

        $dias_totales_laborados = $this->calcular_dias_laborados_calendario_30_dias( $empleado->fecha_ingreso, $documento_nomina->fecha );

        $dias_totales_laborados_2 = $this->diferencia_en_dias_entre_fechas( $documento_nomina->fecha, $empleado->fecha_ingreso );

        $dias_totales_vacaciones = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / self::DIAS_BASE_LEGALES;

        $dias_pendientes = $dias_totales_vacaciones - $dias_pagados_vacaciones;

        $this->tabla_resumen['fecha_ingreso'] = $empleado->fecha_ingreso;
        $this->tabla_resumen['fecha_liquidacion'] = $documento_nomina->fecha;
        $this->tabla_resumen['dias_totales_laborados'] = $dias_totales_laborados;
        $this->tabla_resumen['dias_totales_no_laborados'] = 0;
        $this->tabla_resumen['dias_totales_vacaciones'] = $dias_totales_vacaciones;
        $this->tabla_resumen['dias_pagados_vacaciones'] = $dias_pagados_vacaciones;
        $this->tabla_resumen['dias_pendientes'] = $dias_pendientes;

        $periodo_pagado_hasta = $empleado->fecha_ingreso;
        $ultimo_periodo_pagado = LibroVacacion::where('nom_contrato_id',$empleado->id)->orderBy('periodo_pagado_hasta')->get()->last();
        if ( !is_null($ultimo_periodo_pagado) )
        {
            $periodo_pagado_hasta = $ultimo_periodo_pagado->periodo_pagado_hasta;
        }

        $this->tabla_resumen['fecha_inicial_disfrute'] = $periodo_pagado_hasta;
        $this->tabla_resumen['fecha_final_disfrute'] = $documento_nomina->fecha;

        $this->tabla_resumen['dias_habiles'] = $dias_pendientes;
        $this->tabla_resumen['dias_no_habiles'] = 0;

        return $dias_pendientes;
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        $cantidad_horas_laboradas = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
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

    public function get_valor_acumulado_provision()
    {
        return 0;
    }

    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return abs( $fecha_ini->diffInDays($fecha_fin) );
    }

    /*public function salario_acumulado_entre_fechas( $empleado, $fecha_inicial, $fecha_final)
    {
        $salario_acumulado = 0;
        $cambios_salario = CambioSalario::where('nom_contrato_id',$empleado->id)->get();

        foreach ($cambios_salario as $registro)
        {
            $cantidad_dias = $this->calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $registro->fecha_modificacion );

            $salario_diario = $registro->salario_anterior / (int)config('nomina.horas_laborales') * (int)config('nomina.horas_dia_laboral');

            $salario_acumulado += ( $salario_diario * $cantidad_dias );
        }

        if ( $salario_acumulado == 0 )
        {
            $cantidad_dias = $this->calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $fecha_final );

            $salario_acumulado = $empleado->salario_x_dia() * $cantidad_dias;
        }

        return $salario_acumulado;
    }
    */

    public function retirar(NomDocRegistro $registro)
    {
        dd(['retirar vacaciones',$registro]);

        $novedad = $registro->novedad_tnl;
        
        if( is_null( $novedad ) )
        {
            dd( [ 'TiempoNoLaborado Novedad NULL', $registro] );
        }

        if( is_null( $registro->contrato ) )
        {
            dd( [ 'TiempoNoLaborado Contrato NULL', $registro] );
        }


        // FALTA
    }
}