<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;

use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use App\Nomina\PrestacionesLiquidadas;

use Carbon\Carbon;

class PrimaAntiguedad implements Estrategia
{
    const DIAS_ANIO = 365;

    protected $tabla_resumen = [];
    protected $dias_totales_laborados;
    protected $descripcion_prestacion;
    protected $prestacion;

	public function calcular(LiquidacionConcepto $liquidacion)
	{
        $prestacion_liquidar = $this->get_prestacion_liquidar( $liquidacion['empleado'], $liquidacion['documento_nomina'] );
        
        if( $prestacion_liquidar == '' )
        {
            return [
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0
                        ]
                    ];
        }

        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where( [
        																			[ 'concepto_prestacion', '=', $prestacion_liquidar ],
        																			[ 'grupo_empleado_id', '=', $liquidacion['empleado']->grupo_empleado_id ]
        																		] )
                                                                        ->get()->first();

        if( is_null( $parametros_prestacion ) )
        {
            return [
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0
                        ]
                    ];
        }

        if( $this->ya_esta_liquidada( $prestacion_liquidar, $liquidacion['empleado'] ) )
        {
            return [
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0
                        ]
                    ];
        }

        $this->tabla_resumen['fecha_liquidacion'] = $liquidacion['documento_nomina']->fecha;
        $this->tabla_resumen['dias_totales_laborados'] = $this->dias_totales_laborados;

        $this->tabla_resumen['descripcion_prestacion'] = $this->descripcion_prestacion;
        $this->tabla_resumen['base_liquidacion'] = $parametros_prestacion->base_liquidacion;


        $this->tabla_resumen['valor_acumulado_salario'] = $liquidacion['empleado']->sueldo;
        $this->tabla_resumen['valor_salario_x_dia'] = $liquidacion['empleado']->salario_x_dia();
        $this->tabla_resumen['cantidad_dias_salario'] = (int)config('nomina.horas_laborales') / (int)config('nomina.horas_dia_laboral');


        $this->tabla_resumen['dias_totales_liquidacion'] = $parametros_prestacion->dias_a_liquidar;


        $this->tabla_resumen['valor_total_liquidacion'] = (int)$parametros_prestacion->dias_a_liquidar * $liquidacion['empleado']->salario_x_dia();

        $valores = get_valores_devengo_deduccion( 'devengo', $this->tabla_resumen['valor_total_liquidacion'] );

        $array_prestacion = (object)[ 'prestacion' => $this->prestacion, 'tabla_resumen' => $this->tabla_resumen ];
        $this->almacenar_prestaciones_liquidadas( $liquidacion['documento_nomina']->id, $liquidacion['empleado']->id, $liquidacion['documento_nomina']->fecha, $array_prestacion );

		return [ 
					[
						'cantidad_horas' => (int)$parametros_prestacion->dias_a_liquidar * (int)config('nomina.horas_dia_laboral'),
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion,
						'detalle' => '(' . $this->descripcion_prestacion . ')' 
					]
				];
	}

    public function ya_esta_liquidada( $prestacion_liquidar, $empleado )
    {
        $prestaciones_liquidadas_empleado = PrestacionesLiquidadas::where( 'nom_contrato_id', $empleado->id )->get();

        foreach ($prestaciones_liquidadas_empleado as $registro )
        {
            $prestacion_liquidada = json_decode( $registro->prestaciones_liquidadas )[0];
            if( $prestacion_liquidada->prestacion == $prestacion_liquidar  )
            {
                return 1;// Si está liquidada
            }
        }
        
        return 0; // No está liquidada
    }

    public function almacenar_prestaciones_liquidadas( $nom_doc_encabezado_id, $nom_contrato_id, $fecha_final_promedios, $array_prestacion )
    {
        if ( empty( $array_prestacion ) )
        {
            return 0;
        }

        PrestacionesLiquidadas::create(
                                        ['nom_doc_encabezado_id' => $nom_doc_encabezado_id ] + 
                                        ['nom_contrato_id' => $nom_contrato_id ] + 
                                        ['fecha_final_promedios' => $fecha_final_promedios ] +  
                                        ['prestaciones_liquidadas' => '[' . json_encode( $array_prestacion ) . ']' ]
                                    );
    }

	public function get_prestacion_liquidar( $empleado, $documento_nomina )
	{
		$this->dias_totales_laborados = $this->diferencia_en_dias_entre_fechas( $empleado->fecha_ingreso, $documento_nomina->fecha );

		$anios_de_servicio = $this->dias_totales_laborados / self::DIAS_ANIO;

		if ( $anios_de_servicio >= 20 )
		{
			$this->prestacion = 'prima_antiguedad_20';
			$this->descripcion_prestacion = 'Veinte años';
			return 'prima_antiguedad_20';
		}

		if ( $anios_de_servicio >= 15 )
		{
			$this->prestacion = 'prima_antiguedad_15';
			$this->descripcion_prestacion = 'Quince años';
			return 'prima_antiguedad_15';
		}

		if ( $anios_de_servicio >= 10 )
		{
			$this->prestacion = 'prima_antiguedad_10';
			$this->descripcion_prestacion = 'Diez años';
			return 'prima_antiguedad_10';
		}

		if ( $anios_de_servicio >= 5 )
		{
			$this->prestacion = 'prima_antiguedad_5';
			$this->descripcion_prestacion = 'Cinco años';
			return 'prima_antiguedad_5';
		}

		$this->descripcion_prestacion = '';
		return '';
    }

    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return abs( $fecha_ini->diffInDays($fecha_fin) );
    }

	public function retirar(NomDocRegistro $registro)
	{
		$prestacion_liquidada = PrestacionesLiquidadas::where(
						                                        [ 'nom_doc_encabezado_id' => $registro->encabezado_documento->id ] + 
						                                        [ 'nom_contrato_id' => $registro->contrato->id ]
						                                    )
														->get()->first();

		if ( !is_null( $prestacion_liquidada ) )
		{
			$prestacion_liquidada->delete();
		}

        $registro->delete();
	}
}