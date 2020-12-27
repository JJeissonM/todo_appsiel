<?php 

	$hay_logros = false;
	$lista = '<ul class="lista_logros">';

	$tbody = '';
	if ( !is_null($linea->logros) ) {
		foreach( $linea->logros as $un_logro )
		{
			switch ($convetir_logros_mayusculas) {
				case 'Si':
					$tbody .= '<li style="text-align: justify;">'.strtoupper($un_logro->descripcion).'</li>';
					break;
				case 'No':
					$tbody .= '<li style="text-align: justify;">'.$un_logro->descripcion.'</li>';
					break;
				
				default:
					# code...
					break;
			}
			$hay_logros = true;
		}
	}
		

	/*
		Para logros Adicionales
	*/
	if ( !is_null($linea->logros_adicionales) ) {
		foreach( $linea->logros_adicionales as $un_logro )
		{
			switch ($convetir_logros_mayusculas) {
				case 'Si':
					$tbody .= '<li style="text-align: justify;">'.strtoupper($un_logro->descripcion).'</li>';
					break;
				case 'No':
					$tbody .= '<li style="text-align: justify;">'.$un_logro->descripcion.'</li>';
					break;
				
				default:
					# code...
					break;
			}
			$hay_logros = true;
		}
	}


	$lista .= $tbody;

	$lista .= '</ul>';

	if ( $hay_logros ) 
	{
		echo $lista;
	}else{
		echo "&nbsp;";
	}
?>