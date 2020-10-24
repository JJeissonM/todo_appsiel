<?php
	
	if ( !is_null( $linea->propositos ) ) 
	{
		$lista_propositos = '';
		foreach ($linea->propositos as $proposito)
		{
			switch ($convetir_logros_mayusculas) {
				case 'Si':
					$lista_propositos .= '<div style="text-align: justify;"><b>Propósito: </b>'.strtoupper($proposito->descripcion).'<hr style="border:0.8px solid gray;"></div>';
					break;
				case 'No':
					$lista_propositos .= '<div style="text-align: justify;"><b>Propósito: </b>'.$proposito->descripcion.'<hr style="border:0.8px solid gray;"></div>';
					break;
				
				default:
					# code...
					break;
			}
		}
			

	}
?>