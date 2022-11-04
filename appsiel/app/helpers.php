<?php

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
        return '<div style="width: 100%;text-align: center; font-size: 11px; color: #aaa; text-decoration: none;">
                    <i>Generado por <a href="https://appsiel.com.co" target="_blank" style="color: #aaa;">Appsiel &reg;</a></i>&nbsp;&nbsp;&nbsp;
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