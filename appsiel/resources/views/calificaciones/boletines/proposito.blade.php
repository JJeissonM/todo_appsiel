<?php
	$proposito = App\Calificaciones\Meta::where('periodo_id',$periodo->id)->where('curso_id', $curso->id)->where('asignatura_id', $asignatura->id)->where('estado','Activo')->get();

	if ( count($proposito) > 0 ) 
	{
		
		switch ($convetir_logros_mayusculas) {
			case 'Si':
				echo '<div style="text-align: justify;"><b>Propósito: </b>'.strtoupper($proposito[0]->descripcion).'<hr style="border:0.8px solid gray;"></div>';
				break;
			case 'No':
				echo '<div style="text-align: justify;"><b>Propósito: </b>'.$proposito[0]->descripcion.'<hr style="border:0.8px solid gray;"></div>';
				break;
			
			default:
				# code...
				break;
		}

	}
?>