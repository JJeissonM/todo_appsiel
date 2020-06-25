<?php 

	$hay_logros = false;
	$lista = '<ul class="lista_logros">';

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

	/*
		Para logros Adicionales
	*/
	if( $calificacion->escala_id != 0 )
	{
		$vec_logros = explode( ",", $calificacion->logros);
	}else{
		$vec_logros[0] = 0;
	}

	for( $j = 0; $j < count($vec_logros); $j++ )
	{
		$logro = App\Calificaciones\Logro::where( 'codigo', $vec_logros[$j] )
											->where( 'id_colegio', $colegio->id)
											->where( 'asignatura_id', $asignatura->id)
											->get()
											->first();

		if( !is_null($logro) )
		{
			switch ($convetir_logros_mayusculas)
			{
				case 'Si':
					$tbody.='<li style="text-align: justify;">'.strtoupper( $logro->descripcion ).'</li>';
					break;
				case 'No':
					$tbody.='<li style="text-align: justify;">'.$logro->descripcion.'</li>';
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