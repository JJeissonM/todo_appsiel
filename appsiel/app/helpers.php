<?php



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