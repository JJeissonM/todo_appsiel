<?php


if (! function_exists('generado_por_appsiel'))
{
    function generado_por_appsiel()
    {
        return '<div style="width: 100%;text-align: right; font-size: 10px; color: #aaa;">
                    <i>Generado por <a href="https://appsiel.com.co" target="_blank">Appsiel</a></i>&nbsp;&nbsp;&nbsp;
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