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
use App\Nomina\PrestacionesLiquidadas;

use App\Nomina\ModosLiquidacion\Estrategias\PrestacionSocial;

class Vacaciones implements Estrategia
{
    const DIAS_BASE_LEGALES = 360;

    protected $historial_vacaciones;
    protected $tabla_resumen = [];
    protected $novedad_id;

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
            $this->tabla_resumen['mensaje_error'] = 'No están configurados los parámetros de Vacaciones para el grupo de empleado ' . $liquidacion['empleado']->grupo_empleado->descripcion;

            return [
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0,
                            'tabla_resumen' => $this->tabla_resumen
                        ]
                    ];
        }

        $registro_concepto = NomDocRegistro::where(
                                                    ['nom_doc_encabezado_id' => $liquidacion['documento_nomina']->id ] + 
                                                    ['nom_contrato_id' => $liquidacion['empleado']->id ] + 
                                                    ['nom_concepto_id' => $parametros_prestacion->nom_concepto_id ]
                                                )
                                            ->get()->first();

        if ( !is_null($registro_concepto) )
        {
            $this->tabla_resumen['mensaje_error'] = '<br>Vacaciones. La prestación ya está liquidada en el documento.';

            return [
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0,
                            'tabla_resumen' => $this->tabla_resumen
                        ]
                    ];
        }

        $this->historial_vacaciones = LibroVacacion::where( 'nom_contrato_id', $liquidacion['empleado']->id )
                                                    ->orderBy('periodo_pagado_hasta')
                                                    ->get();

        $dias_pendientes = $this->get_dias_pendientes( $liquidacion['empleado'], $liquidacion['documento_nomina'], $parametros_prestacion );
        $this->tabla_resumen['dias_pendientes'] = $dias_pendientes;

        $valor_base_diaria =  $this->get_valor_base_diaria( $liquidacion['empleado'], $liquidacion['documento_nomina']->fecha, $liquidacion['documento_nomina']->tipo_liquidacion, $parametros_prestacion );

        $this->tabla_resumen['valor_base_diaria'] = $valor_base_diaria;

        if ( $liquidacion['documento_nomina']->tipo_liquidacion == 'terminacion_contrato' )
        {
            $valor_total_vacaciones = $dias_pendientes * $valor_base_diaria;
            $this->tabla_resumen['valor_total_vacaciones'] = $valor_total_vacaciones;

            $this->tabla_resumen['vlr_dias_habiles'] = $valor_total_vacaciones;
            $this->tabla_resumen['vlr_dias_no_habiles'] = 0;

            $this->novedad_id = 0;
            $this->tabla_resumen['fecha_inicial_disfrute'] = '0000-00-00';
            $this->tabla_resumen['fecha_final_disfrute'] = '0000-00-00';

            $this->set_periodo_causacion_vacaciones( $liquidacion['empleado'], $liquidacion['documento_nomina'] );

            if ( $liquidacion['almacenar_registros'] )
            {
                $this->actualizar_libro_vacaciones( $liquidacion['empleado'], $liquidacion['documento_nomina'], null );
            }

            $valores = get_valores_devengo_deduccion( 'devengo',  $valor_total_vacaciones );

            return [ 
                        [
                            'cantidad_horas' => 0, // No disfruta vacaciones
                            'valor_devengo' => $valores->devengo,
                            'valor_deduccion' => $valores->deduccion,
                            'tabla_resumen' => $this->tabla_resumen  
                        ]
                    ];
        }

        $programacion_vacaciones = ProgramacionVacacion::where( [
                                                                    [ 'nom_contrato_id', '=', $liquidacion['empleado']->id ],
                                                                    [ 'tipo_novedad_tnl', '=', 'vacaciones' ],
                                                                    [ 'cantidad_dias_amortizados', '=', 0 ]
                                                                ] )
                                                        ->get()
                                                        ->first();

        if ( is_null($programacion_vacaciones) )
        {
            $this->tabla_resumen['mensaje_error'] = '<br>Empleado ' . $liquidacion['empleado']->tercero->descripcion  . ' no tiene vacaciones programadas pendientes por liquidar.';
            return [ 
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0,
                            'tabla_resumen' => $this->tabla_resumen
                        ]
                    ];
        }

        $this->novedad_id = $programacion_vacaciones->id;
        // El registro en el libro de vacaciones se creó al crear la programación de la vacación 
        $libro_vacaciones = LibroVacacion::where( 'novedad_tnl_id', $programacion_vacaciones->id )->get()->first();

        $this->tabla_resumen['fecha_inicial_disfrute'] = $programacion_vacaciones->fecha_inicial_tnl;
        $this->tabla_resumen['fecha_final_disfrute'] = $programacion_vacaciones->fecha_final_tnl;

        $this->tabla_resumen['dias_habiles'] = $libro_vacaciones->dias_pagados;
        $this->tabla_resumen['dias_no_habiles'] = $libro_vacaciones->dias_no_habiles;
        $this->tabla_resumen['vlr_dias_habiles'] = $valor_base_diaria * $libro_vacaciones->dias_pagados;
        $this->tabla_resumen['vlr_dias_no_habiles'] = $valor_base_diaria * $libro_vacaciones->dias_no_habiles;

        $this->tabla_resumen['valor_total_vacaciones'] = $this->tabla_resumen['vlr_dias_habiles'] + $this->tabla_resumen['vlr_dias_no_habiles'];

        $this->set_periodo_causacion_vacaciones( $liquidacion['empleado'], $liquidacion['documento_nomina'] );

        $cantidad_dias_amortizar = $this->get_cantidad_dias_amortizar( $programacion_vacaciones, $liquidacion['documento_nomina'] );

        if ( $liquidacion['almacenar_registros'] )
        {    
            // Actualiza programación de vacaciones (TNL)
            $programacion_vacaciones->cantidad_dias_amortizados += $cantidad_dias_amortizar;
            $programacion_vacaciones->cantidad_dias_pendientes_amortizar -= $cantidad_dias_amortizar;
            $programacion_vacaciones->save();

            // Almacenar registro para los días no hábiles
            $this->crear_registro_concepto_vacaciones_dias_no_habiles( $programacion_vacaciones->id, $liquidacion['documento_nomina'], $liquidacion['empleado'], $libro_vacaciones->dias_no_habiles * (int)config('nomina.horas_dia_laboral') );

            $this->actualizar_libro_vacaciones( $liquidacion['empleado'], $liquidacion['documento_nomina'], $libro_vacaciones );
        }        

        $valores = get_valores_devengo_deduccion( 'devengo', $this->tabla_resumen['vlr_dias_habiles'] );

        return [
                    [
                        'cantidad_horas' => $cantidad_dias_amortizar * (int)config('nomina.horas_dia_laboral'), // Se almacenan solo los días a amortizar, el resto del tiempo de vacaciones se almacenan en el documento siguiente de nómina
                        'valor_devengo' => $valores->devengo,
                        'valor_deduccion' => $valores->deduccion,
                        'novedad_tnl_id' => $this->novedad_id,
                        'tabla_resumen' => $this->tabla_resumen
                    ]
                ];
	}

    public function crear_registro_concepto_vacaciones_dias_no_habiles( $novedad_tnl_id, $documento_nomina, $empleado, $cantidad_horas_a_liquidar)
    {
        if ( $this->tabla_resumen['vlr_dias_no_habiles'] > 0 )
        {
            NomDocRegistro::create(
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
                                    [ 'valor_devengo' => round( $this->tabla_resumen['vlr_dias_no_habiles'], 0) ] +
                                    [ 'valor_deduccion' => 0 ] +
                                    [ 'novedad_tnl_id' => $novedad_tnl_id ]
                                );
        }
    }

    public function get_valor_base_diaria( $empleado, $fecha_final, $tipo_liquidacion, $parametros_prestacion )
    {
        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }

        $valor_base_diaria = 0;
        $valor_base_diaria_sueldo = 0;

        $fecha_inicial = $parametros_prestacion->get_fecha_inicial_promedios( $fecha_final, $empleado );

        $this->tabla_resumen['fecha_inicial_promedios'] = $fecha_inicial;
        $this->tabla_resumen['fecha_final_promedios'] = $fecha_final;

        $nom_agrupacion_id = $parametros_prestacion->nom_agrupacion_id;
        if ( $tipo_liquidacion == 'terminacion_contrato' )
        {
            $nom_agrupacion_id = $parametros_prestacion->nom_agrupacion2_id;
        }

        $cantidad_dias = PrestacionSocial::get_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final );

        $this->tabla_resumen['cantidad_dias'] = $cantidad_dias;

        $this->tabla_resumen['base_liquidacion'] = $parametros_prestacion->base_liquidacion;

        $this->tabla_resumen['cantidad_dias_salario'] = (int)config('nomina.horas_laborales') / (int)config('nomina.horas_dia_laboral');

        switch ( $parametros_prestacion->base_liquidacion )
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

                $valor_acumulado_agrupacion = PrestacionSocial::get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );
                
                if ( $cantidad_dias != 0 )
                {
                    $valor_agrupacion_x_dia = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                $this->tabla_resumen['descripcion_agrupacion'] = AgrupacionConcepto::find( $nom_agrupacion_id )->descripcion;
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

                $valor_acumulado_agrupacion = PrestacionSocial::get_valor_acumulado_agrupacion_entre_meses( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );

                if ( $cantidad_dias != 0 )
                {
                    $valor_base_diaria = $valor_acumulado_agrupacion / $cantidad_dias;
                }

                $this->tabla_resumen['descripcion_agrupacion'] = AgrupacionConcepto::find( $nom_agrupacion_id )->descripcion;
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


    public function get_cantidad_dias_amortizar( $programacion_vacaciones, $documento_nomina )
    {
        $fecha_final = $documento_nomina->lapso()->fecha_final;

        if ( $fecha_final > $programacion_vacaciones->fecha_final_tnl )
        {
            $fecha_final = $programacion_vacaciones->fecha_final_tnl;
        }

        return $this->diferencia_en_dias_entre_fechas( $programacion_vacaciones->fecha_inicial_tnl, $fecha_final ) + 1;
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

        $dias_pagados_vacaciones = LibroVacacion::where([
                                                            ['nom_contrato_id','=',$empleado->id],
                                                            ['periodo_pagado_hasta','<>','0000-00-00']
                                                        ])
                                                ->sum('dias_pagados');

        if ( is_null($dias_pagados_vacaciones) )
        {
            $dias_pagados_vacaciones = 0;
        }

        $dias_totales_laborados = PrestacionSocial::get_dias_reales_laborados( $empleado, $empleado->fecha_ingreso, $documento_nomina->fecha );

        $dias_calendario_laborados = PrestacionSocial::calcular_dias_laborados_calendario_30_dias( $empleado->fecha_ingreso, $documento_nomina->fecha );

        $dias_totales_no_laborados = $dias_calendario_laborados - $dias_totales_laborados;
        
        /*
            Falta calcular los días no laborados
        */

        $dias_totales_vacaciones = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / self::DIAS_BASE_LEGALES;

        $dias_pendientes = $dias_totales_vacaciones - $dias_pagados_vacaciones;

        $this->tabla_resumen['fecha_ingreso'] = $empleado->fecha_ingreso;
        $this->tabla_resumen['fecha_liquidacion'] = $documento_nomina->fecha;
        $this->tabla_resumen['dias_totales_laborados'] = $dias_totales_laborados;
        $this->tabla_resumen['dias_totales_no_laborados'] = $dias_totales_no_laborados;
        $this->tabla_resumen['dias_totales_vacaciones'] = $dias_totales_vacaciones;
        $this->tabla_resumen['dias_pagados_vacaciones'] = $dias_pagados_vacaciones;
        $this->tabla_resumen['dias_pendientes'] = $dias_pendientes;

        $this->tabla_resumen['fecha_inicial_disfrute'] = $documento_nomina->fecha;
        $this->tabla_resumen['fecha_final_disfrute'] = $documento_nomina->fecha;

        $this->tabla_resumen['dias_habiles'] = $dias_pendientes;
        $this->tabla_resumen['dias_no_habiles'] = 0;

        return $dias_pendientes;
    }

    public function set_periodo_causacion_vacaciones( $empleado, $documento_nomina )
    {            
        $periodo_pagado_desde = $empleado->fecha_ingreso;
        if ( !empty( $this->historial_vacaciones->toArray() ) )
        {
            if ( $this->historial_vacaciones->last()->periodo_pagado_hasta != '0000-00-00' )
            {
                // Se agrega un día a la fecha del "Úlltimo día pagado en el libro de vacaciones"
                $periodo_pagado_desde = $this->sumar_dias_calendario_a_fecha( $this->historial_vacaciones->last()->periodo_pagado_hasta, 1 );
            }                
        }

        if ( $documento_nomina->tipo_liquidacion == 'terminacion_contrato' )
        {
            $periodo_pagado_hasta = $documento_nomina->fecha;

        }else{
            // 15 días de vacaciones por cada 360 días del año
            // 24.35 días calendario por cada 1 día de vacaciones
            $dias_calendario_vacaciones = $this->tabla_resumen['dias_habiles'] * (float)config('nomina.dias_calendario_por_dia_vacacion_legal');
            $periodo_pagado_hasta = $this->sumar_dias_calendario_a_fecha( $periodo_pagado_desde, ($dias_calendario_vacaciones ) );
        }

        $this->tabla_resumen['periodo_pagado_desde'] = $periodo_pagado_desde;
        $this->tabla_resumen['periodo_pagado_hasta'] = $periodo_pagado_hasta;
    }

    public function actualizar_libro_vacaciones( $empleado, $documento_nomina, $libro_vacaciones )
    {
        if ( $documento_nomina->tipo_liquidacion == 'terminacion_contrato' )
        {
            // Se crea un registro en el Libro de vacaciones
            LibroVacacion::create(
                                    [ 'nom_contrato_id' => $empleado->id ] +
                                    [ 'nom_doc_encabezado_id' => $documento_nomina->id ] +
                                    [ 'novedad_tnl_id' => 0 ] +
                                    [ 'periodo_pagado_desde' => $this->tabla_resumen['periodo_pagado_desde'] ] +
                                    [ 'periodo_pagado_hasta' => $this->tabla_resumen['periodo_pagado_hasta'] ] +
                                    [ 'periodo_disfrute_vacacion_desde' => '0000-00-00' ] +
                                    [ 'periodo_disfrute_vacacion_hasta' => '0000-00-00' ] +
                                    [ 'dias_pagados' => $this->tabla_resumen['dias_pendientes'] ] +
                                    [ 'dias_compensados' => 0 ] +
                                    [ 'dias_disfrutados' => 0 ] +
                                    [ 'dias_no_habiles' => 0 ] +
                                    [ 'valor_vacaciones' => $this->tabla_resumen['valor_total_vacaciones'] ]
                                );
        }else{
            // Ya el registro en el libro de vacaciones se creó al programar la Vacación.
            $libro_vacaciones->nom_doc_encabezado_id = $documento_nomina->id;
            $libro_vacaciones->periodo_pagado_desde = $this->tabla_resumen['periodo_pagado_desde'];
            $libro_vacaciones->periodo_pagado_hasta = $this->tabla_resumen['periodo_pagado_hasta'];
            $libro_vacaciones->valor_vacaciones = $this->tabla_resumen['valor_total_vacaciones'];
            $libro_vacaciones->save();
        }
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

    public function sumar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->addDays( $cantidad_dias )->format('Y-m-d');
    }

    /*
        Este método se ejecuta por cada registro del documento de nómina.
        Pueden haber liquidaciones de conceptos distintos a vacaciones 
    */
    public function retirar( NomDocRegistro $registro )
    {
        if ( $registro->encabezado_documento->tipo_liquidacion == 'terminacion_contrato' )
        {

            if( is_null( $registro->contrato ) || is_null( $registro->concepto ) )
            {
                return 0;
            }

            $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion','vacaciones')
                                                                        ->where('grupo_empleado_id',$registro->contrato->grupo_empleado_id)
                                                                        ->get()
                                                                        ->first();

            if( is_null( $parametros_prestacion ) )
            {
                return 0;
            }

            if ( $registro->nom_concepto_id != $parametros_prestacion->nom_concepto_id )
            {
                return 0;
            }
            
            // Borrar registro prestaciones liquidadas
            PrestacionesLiquidadas::where(
                                            ['nom_doc_encabezado_id' => $registro->nom_doc_encabezado_id ] + 
                                            ['nom_contrato_id' => $registro->nom_contrato_id ]
                                        )
                                    ->delete();
            
            // Borrar registro libro de vacaciones
            LibroVacacion::where(
                                    ['nom_doc_encabezado_id' => $registro->nom_doc_encabezado_id ] + 
                                    ['nom_contrato_id' => $registro->nom_contrato_id ]
                                )
                            ->delete();

            $registro->delete();

            return 0;
        }
        
        $novedad = $registro->novedad_tnl;
        
        if( is_null( $novedad ) )
        {
            return 0;
        }

        if( is_null( $registro->contrato ) )
        {
            return 0;
        }

        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion','vacaciones')
                                                                        ->where('grupo_empleado_id',$registro->contrato->grupo_empleado_id)
                                                                        ->get()
                                                                        ->first();
        
        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }

        if ( $registro->nom_concepto_id == $parametros_prestacion->nom_concepto_id )
        {
            // 1. Actualiza programación de vacaciones (TNL)
            $programacion_vacaciones = ProgramacionVacacion::find( $novedad->id );

            $programacion_vacaciones->cantidad_dias_amortizados -= $this->get_cantidad_dias_amortizar( $programacion_vacaciones, $registro->encabezado_documento );
            $programacion_vacaciones->cantidad_dias_pendientes_amortizar += $this->get_cantidad_dias_amortizar( $programacion_vacaciones, $registro->encabezado_documento );
            $programacion_vacaciones->save();
            
            // 2.
            PrestacionesLiquidadas::where(
                                            ['nom_doc_encabezado_id' => $registro->nom_doc_encabezado_id ] + 
                                            ['nom_contrato_id' => $registro->nom_contrato_id ]
                                        )
                                    ->delete();
            
            // 3.
            $libro_vacaciones = LibroVacacion::where( 'novedad_tnl_id', $programacion_vacaciones->id )->get()->first();
            $libro_vacaciones->nom_doc_encabezado_id = 0;
            $libro_vacaciones->periodo_pagado_desde = '0000-00-00';
            $libro_vacaciones->periodo_pagado_hasta = '0000-00-00';
            $libro_vacaciones->valor_vacaciones = 0;
            $libro_vacaciones->save();

            $registro->delete();
        }

        if ( $registro->nom_concepto_id == (int)config('nomina.concepto_vacaciones_dias_no_habiles') )
        {
            $registro->delete();
        }
    }
}