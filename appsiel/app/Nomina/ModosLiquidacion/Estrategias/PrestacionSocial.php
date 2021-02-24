<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

use Auth;
use Carbon\Carbon;

use App\Nomina\NovedadTnl;
use App\Nomina\NomDocRegistro;

use App\Nomina\AgrupacionConcepto;

use App\Nomina\ParametroLiquidacionPrestacionesSociales;

class PrestacionSocial
{
	

    /*
        Los días laborados se toman de todos los conceptos que Forman parte del salario.
        Se deben excluir las Vacaciones pagadas en documentos de liquidación de contratos.
        La vacaciones disfrutadas si se incluyen.
    */
    public static function get_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final )
    {
        /*$registros = NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                            ->whereBetween( 'nom_doc_registros.fecha', [ $fecha_inicial, $fecha_final ] )
                                            ->where( 'nom_conceptos.forma_parte_basico', 1 )
                                            ->where( 'nom_doc_registros.nom_contrato_id', $empleado->id )
                                            ->get();
        $cantidad_horas_laboradas = 0;
        $concepto_vacaciones_id = self::get_concepto_vacaciones_id( $empleado );
        foreach ( $registros as $registro )
        {
            // Se salta el concepto de vacaciones en liquidación de contratos
        	if ( ($registro->nom_concepto_id == $concepto_vacaciones_id) && ( $registro->encabezado_documento->tipo_liquidacion == 'terminacion_contrato' ) )
        	{
        		continue;
        	}

        	$cantidad_horas_laboradas += $registro->cantidad_horas;
        }

        return ( $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );*/

        $dias_calendario = self::calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $fecha_final );

        $registros = NomDocRegistro::whereBetween( 'nom_doc_registros.fecha', [ $fecha_inicial, $fecha_final ] )
                                            ->where( [
                                                        ['nom_doc_registros.nom_contrato_id', '=', $empleado->id],
                                                        ['nom_doc_registros.novedad_tnl_id', '<>', NULL]
                                                    ] )
                                            ->get();

        $cantidad_horas_laboradas = 0;
        $concepto_vacaciones_id = self::get_concepto_vacaciones_id( $empleado );
        foreach ( $registros as $registro )
        {
            // Se salta el concepto de Vacaciones, que es una TNL y puede tener valores en Cero
            if ( $registro->nom_concepto_id == $concepto_vacaciones_id )
            {
                continue;
            }

            // Los Tiempos No Laborados Con Valor Cero son Suspenciones y Permisos No Remunerados
            $suma = round( $registro->valor_devengo + $registro->valor_deduccion, 0 );
            // Se salta el concepto de vacaciones en liquidación de contratos
            if ( $suma == 0 )
            {
                $cantidad_horas_laboradas += $registro->cantidad_horas;
            }            
        }

        return ( $dias_calendario - $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );

    }

    public static function calcular_dias_laborados_calendario_30_dias( $fecha_inicial, $fecha_final )
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


   /*
        Se deben excluir las Vacaciones pagadas en documentos de liquidación de contratos.
        La vacaciones disfrutadas si se incluyen.
    */
    public static function get_valor_acumulado_agrupacion_entre_meses( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        $registros = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->get();

        $total_devengos = 0;
        $total_deducciones = 0;
        $concepto_vacaciones_id = self::get_concepto_vacaciones_id( $empleado );
        foreach ( $registros as $registro )
        {
        	if ( ($registro->nom_concepto_id == $concepto_vacaciones_id) && ( $registro->encabezado_documento->tipo_liquidacion == 'terminacion_contrato' ) )
        	{
        		continue;
        	}

        	$total_devengos += $registro->valor_devengo;
        	$total_deducciones += $registro->valor_deduccion;
        }

        return ( $total_devengos - $total_deducciones );
    }


    public static function get_valor_acumulado_agrupacion_entre_meses_conceptos_no_salario( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final )
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



    public static function get_fecha_inicial_promedios_un_mes( $fecha_final, $empleado )
    {
        $vec_fecha = explode("-", $fecha_final);

        $anio_inicial = $vec_fecha[0];
        $mes_inicial = $vec_fecha[1];
        $dia_inicial = '01';

        $fecha_inicial = $anio_inicial . '-' . $mes_inicial . '-' . $dia_inicial;
        $diferencia = self::diferencia_en_dias_entre_fechas( $fecha_inicial, $empleado->fecha_ingreso );
        
        // Si la fecha_inicial es menor que la fecha_ingreso del empleado, la fecha inicial debe ser la del contrato
        if ( $diferencia > 0 )
        {
            return $empleado->fecha_ingreso;
        }

        // Si la diferencia es negativa, quiere decir que la fecha_final es superior a la fecha_ingreso
        return $fecha_inicial;
    }


    public static function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return $fecha_ini->diffInDays( $fecha_fin, false); // false activa el calculo de diferencias negativas
    }


    public static function get_concepto_vacaciones_id( $empleado )
    {
        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
                                                                        ->where('grupo_empleado_id', $empleado->grupo_empleado_id)
                                                                        ->get()->first();
        if ( is_null( $parametros_prestacion) )
        {
            return 0;
        }

        return $parametros_prestacion->nom_concepto_id;
    }
}