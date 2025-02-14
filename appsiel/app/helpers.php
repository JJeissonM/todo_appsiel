<?php

use Carbon\Carbon;

if ( !function_exists('repetir_caracter') )
{
    
    /* 
        orientacion_relleno: 
            derecha= completar con caracter de relleno hacia la derecha
            izquierda= completar con caracter de relleno hacia la izquierda

    */
    function repetir_caracter( $valor_campo, $caracter_relleno, $orientacion_relleno, $longitud_campo )
    {
        $largo_campo = strlen($valor_campo);
        $longitud_campo -= $largo_campo;
        switch ( $orientacion_relleno)
        {
            case 'izquierda':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $caracter_relleno . $valor_campo;
                }
                break;            
            
            case 'derecha':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $valor_campo . $caracter_relleno;
                }
                break;
            
            default:
                # code...
                break;
            
        }

        return $valor_campo;
    }
}

if (! function_exists('generado_por_appsiel'))
{
    function generado_por_appsiel()
    {
        /*
        return '<div style="width: 100%;text-align: center; font-size: 11px; color: #aaa; text-decoration: none;">
                    <i>Generado por <a href="https://colmilmurillotoro.edu.co/" target="_blank" style="color: #aaa;">Software Katrina &reg;</a></i>&nbsp;&nbsp;&nbsp;
                </div>';
        */
        /*   */  
        return '<div style="width: 100%;text-align: center; font-size: 11px; color: #aaa; text-decoration: none;">
                    <i>Generado por <a href="https://appsiel.com" target="_blank" style="color: #aaa;">Appsiel &reg;</a></i>&nbsp;&nbsp;&nbsp;
                </div>';
        
    }
}


// NÓMINA
if (! function_exists('get_valores_devengo_deduccion'))
{
    function get_valores_devengo_deduccion($naturaleza, $valor)
    {
        switch ($naturaleza)
        {
            case 'devengo':
                $valor_devengo = $valor;
                $valor_deduccion = 0;
                break;
            case 'deduccion':
                $valor_devengo = 0;
                $valor_deduccion = $valor;
                break;
            
            default:
                $valor_devengo = 0;
	        	$valor_deduccion = 0;
                break;
        }

        return (object)['devengo' => $valor_devengo, 'deduccion' => $valor_deduccion];
    }
}

if (! function_exists('formatear_fecha_factura_electronica'))
{
    function formatear_fecha_factura_electronica(string $fecha)
    {
        return date_format( date_create( $fecha ),'d/m/Y');
    }
}

if (! function_exists('diferencia_en_dias_entre_fechas'))
{
    function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return $fecha_ini->diffInDays($fecha_fin, false);
    }
}

/**
 * 
 */
if (! function_exists('sumar_dias_calendario_a_fecha'))
{
    function sumar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->addDays( $cantidad_dias )->format('Y-m-d');
    }
}

/**
 * 
 */
if (! function_exists('restar_dias_calendario_a_fecha'))
{
    function restar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->subDays( $cantidad_dias )->format('Y-m-d');
    }
}

