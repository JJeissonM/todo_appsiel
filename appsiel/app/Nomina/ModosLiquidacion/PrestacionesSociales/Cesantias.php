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

class Cesantias implements Estrategia
{
    const DIAS_BASE_LEGALES = 360;

    protected $historial_vacaciones;
    protected $tabla_resumen = [];
    protected $fecha_final_promedios;
    protected $fecha_final_liquidacion;

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

        $this->fecha_final_promedios = $liquidacion['fecha_final_promedios'];
        $this->fecha_final_liquidacion = $liquidacion['fecha_final_liquidacion'];

        $dias_totales_liquidacion = $this->get_dias_liquidacion( $liquidacion['empleado'], $parametros_prestacion );

        $valor_base_diaria =  $this->get_valor_base_diaria( $liquidacion['empleado'], $this->fecha_final_promedios, $liquidacion['documento_nomina']->tipo_liquidacion, $parametros_prestacion );

        $this->tabla_resumen['valor_base_diaria'] = $valor_base_diaria;

        $this->tabla_resumen['valor_total_liquidacion'] = $dias_totales_liquidacion * $valor_base_diaria;

        $valores = get_valores_devengo_deduccion( 'devengo', $this->tabla_resumen['valor_total_liquidacion'] );

        return [
                    [
                        'cantidad_horas' => $dias_totales_liquidacion * (int)config('nomina.horas_dia_laboral'), // pendiente
                        'valor_devengo' => $valores->devengo,
                        'valor_deduccion' => $valores->deduccion,
                        'tabla_resumen' => $this->tabla_resumen
                    ]
                ];
	}

    /*
            PARA LOS ACUMULADOS
    */
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

        $cantidad_dias = $this->calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $parametros_prestacion->nom_agrupacion_id );

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

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );
                
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

                $valor_acumulado_agrupacion = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, $parametros_prestacion->nom_agrupacion_id, $fecha_inicial, $fecha_final );

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

    public function formatear_numero_a_texto_dos_digitos( $numero )
    {
        if ( strlen($numero) == 1 )
        {
            return "0" . $numero;
        }

        return $numero;
    }

    public function get_dias_liquidacion( $empleado, $parametros_prestacion )
    {
        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }

        $fecha_inicial = $parametros_prestacion->get_fecha_inicial_promedios( $this->fecha_final_liquidacion, $empleado );

        $dias_totales_laborados = $this->calcular_dias_reales_laborados( $empleado, $fecha_inicial, $this->fecha_final_liquidacion, $parametros_prestacion->nom_agrupacion_id );

        $dias_totales_liquidacion = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / self::DIAS_BASE_LEGALES;

        $this->tabla_resumen['fecha_liquidacion'] = $this->fecha_final_liquidacion;
        $this->tabla_resumen['dias_totales_laborados'] = $dias_totales_laborados;
        $this->tabla_resumen['dias_totales_no_laborados'] = 0;
        $this->tabla_resumen['dias_totales_liquidacion'] = $dias_totales_liquidacion;

        return $dias_totales_liquidacion;
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        $cantidad_horas_laboradas = NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                            ->whereBetween( 'nom_doc_registros.fecha', [$fecha_inicial,$fecha_final] )
                                            ->whereIn( 'nom_doc_registros.nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_conceptos.forma_parte_basico', 1 )
                                            ->where( 'nom_doc_registros.core_tercero_id', $empleado->core_tercero_id )
                                            ->sum( 'nom_doc_registros.cantidad_horas' );

        return ( $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );
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

    public function retirar(NomDocRegistro $registro)
    {

        if( is_null( $registro->contrato ) )
        {
            dd( [ 'TiempoNoLaborado Contrato NULL', $registro] );
        }

        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion','cesantias')
                                                                        ->where('grupo_empleado_id',$registro->contrato->grupo_empleado_id)
                                                                        ->get()
                                                                        ->first();
        
        if( is_null( $parametros_prestacion ) )
        {
            return 0;
        }


        // 15: Cesantías consignadas, 17: Cesantías pagadas
        if ( in_array( $registro->concepto->modo_liquidacion_id, [15,17] ) )
        {
            PrestacionesLiquidadas::where(
                                            ['nom_doc_encabezado_id' => $registro->nom_doc_encabezado_id ] + 
                                            ['nom_contrato_id' => $registro->nom_contrato_id ]
                                        )
                                    ->delete();

            $registro->delete();
        }
    }
}