<?php
	if ( !is_null($escala) ) 
	{
		$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)
											->where('periodo_id',$periodo_id)
											->where('curso_id',$curso_id)
											->where('asignatura_id',$asignatura_id)
											->where('estado','Activo')
											->get();

		$n_nom_logros = count($logros);
	}else{
		$logros = (object) array('descripcion' => '');
		$n_nom_logros = 0;
	}

	// Para logros adicionales
	$vec_logros = [];
	if ( !is_null($obj_calificacion) ) 
	{
		$vec_logros = explode( ",", $obj_calificacion->logros);									
	}	

    $logros_adicionales = App\Calificaciones\Logro::whereIn( 'codigo', $vec_logros )
							                    ->where( 'asignatura_id', $asignatura_id )
							                    ->get();

	$n_nom_logros_adicionales = count($logros_adicionales);

	$style = '';
	if( ($n_nom_logros + $n_nom_logros_adicionales) == 0 )
	{
		$style = 'style="background-color:#F08282; color:white;"';
	}

?>
	
<td {{$style}}>
	<ul style="list-style: none;">
		@foreach($logros as $un_logro)
			<?php
				$arr_logros = explode('•',$un_logro->descripcion);
				$lista = '';
				foreach ($arr_logros as $texto_logro) {
					if ($texto_logro == '') {
						continue;
					}
					$lista .= '• ' . $texto_logro . '<br>';
				}
			?>
			<li> {!! $lista !!} </li>
		@endforeach

		@foreach($logros_adicionales as $un_logro)
			<li> {{ '• ' . $un_logro->descripcion }} </li>
		@endforeach
	</ul>
</td>