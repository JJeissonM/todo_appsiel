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

        $registro_concepto = NomDocRegistro::where(
                                                    ['nom_doc_encabezado_id' => $liquidacion['documento_nomina']->id ] + 
                                                    ['nom_contrato_id' => $liquidacion['empleado']->id ] + 
                                                    ['nom_concepto_id' => $parametros_prestacion->nom_concepto_id ]
                                                )
                                            ->get()->first();

        if ( !is_null($registro_concepto) )
        {
            $this->tabla_resumen['mensaje_error'] = '<br>Cesantías. La prestación ya está liquidada en el documento.';

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
                        'cantidad_horas' => $dias_totales_liquidacion * (float)config('nomina.horas_dia_laboral'),
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

        $fecha_inicial = $parametros_prestacion->get_fecha_inicial_promedios( $fecha_final, $empleado );

        $this->tabla_resumen['fecha_inicial_promedios'] = $fecha_inicial;
        $this->tabla_resumen['fecha_final_promedios'] = $fecha_final;

        $nom_agrupacion_id = $parametros_prestacion->nom_agrupacion_id;

        $cantidad_dias = PrestacionSocial::get_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final );

        $this->tabla_resumen['cantidad_dias'] = $cantidad_dias;

        $this->tabla_resumen['base_liquidacion'] = $parametros_prestacion->base_liquidacion;

        $this->tabla_resumen['cantidad_dias_salario'] = (float)config('nomina.horas_laborales') / (float)config('nomina.horas_dia_laboral');

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

        $dias_totales_laborados = PrestacionSocial::get_dias_reales_laborados( $empleado, $fecha_inicial, $this->fecha_final_liquidacion );
        
        // 22 = Profesor de establecimiento particular
        if ( $empleado->tipo_cotizante == 22) {
            $dias_totales_laborados += $empleado->dias_laborados_adicionales_docentes();
        }

        $dias_calendario_laborados = PrestacionSocial::calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $this->fecha_final_liquidacion );

        $dias_totales_no_laborados = $dias_calendario_laborados - $dias_totales_laborados;
        
        // 22 = Profesor de establecimiento particular
        if ( $empleado->tipo_cotizante == 22) {
            $dias_totales_no_laborados += $empleado->dias_laborados_adicionales_docentes();
        }

        $dias_totales_liquidacion = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / self::DIAS_BASE_LEGALES;

        $this->tabla_resumen['fecha_liquidacion'] = $this->fecha_final_liquidacion;
        $this->tabla_resumen['dias_totales_laborados'] = $dias_totales_laborados;
        $this->tabla_resumen['dias_totales_no_laborados'] = $dias_totales_no_laborados;
        $this->tabla_resumen['dias_totales_liquidacion'] = $dias_totales_liquidacion;

        return $dias_totales_liquidacion;
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