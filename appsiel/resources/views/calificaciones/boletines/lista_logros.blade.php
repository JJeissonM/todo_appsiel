<?php 

	$hay_logros = false;
	$lista = '<ul>';

	if ( $calificacion->escala_id != 0 ) 
	{
		$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$calificacion->escala_id)->where('periodo_id',$periodo->id)->where('curso_id', $curso->id)->where('asignatura_id', $asignatura->id)->where('estado','Activo')->get();
	}else{
		$logros = (object) [ (object)['descripcion' => ''] ];
	}

	$tbody = '';
	foreach($logros as $un_logro)
	{
		switch ($convetir_logros_mayusculas) {
			case 'Si':
				$tbody.='<li style="text-align: justify;">'.strtoupper($un_logro->descripcion).'</li>';
				break;
			case 'No':
				$tbody.='<li style="text-align: justify;">'.$un_logro->descripcion.'</li>';
				break;
			
			default:
				# code...
				break;
		}
		$hay_logros = true;
	}

	$lista.=$tbody;

	$lista.='</ul>';

	if ( $hay_logros ) 
	{
		//echo '<b>Logro: </b>'.$lista;
		echo $lista;
	}else{
		echo "&nbsp;";
	}
?>