<?php

	$logros = (object) array('descripcion' => '');
	$n_nom_logros = 0;
	if ( $escala != null ) 
	{
		if ( $escala->id != 0 ) 
		{
			$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)
												->where('periodo_id',$periodo_id)
												->where('curso_id',$curso_id)
												->where('asignatura_id',$asignatura_id)
												->where('estado','Activo')
												->get();

			$n_nom_logros = count($logros);
		}
	}

	// Para logros adicionales
	$vec_logros = [];
	$logros_adicionales = [];
	if ( !is_null($obj_calificacion) ) 
	{
		$vec_logros = explode( ",", $obj_calificacion->logros);
    	$logros_adicionales = App\Calificaciones\Logro::whereIn( 'codigo', $vec_logros )
							                    ->where( 'asignatura_id', $asignatura_id )
							                    ->get();							
	}

	$n_nom_logros_adicionales = count($logros_adicionales);

	$color_back = 'white';
	$color_font = 'black';
	if( ($n_nom_logros + $n_nom_logros_adicionales) == 0 )
	{
		$color_back = '#F08282;';
		$color_font = 'white';
	}

?>
	
<td style="background-color:{{$color_back}} color:{{$color_font}}; text-align: justify;">

	<?php
        $propositos = $metas_del_curso_en_el_periodo->where('asignatura_id', $asignatura_id )->all();

		$linea = (object) array();
		$linea->propositos = $propositos;
	?>

	@include('calificaciones.boletines.proposito',['convetir_logros_mayusculas'=>'No'])

	<ul style="list-style: none;">
		@foreach($logros as $un_logro)
			<?php
				if(empty($un_logro))
				{
					continue;
				}

				$arr_logros = explode('•',$un_logro->descripcion);
				$lista = '';
				foreach ($arr_logros as $texto_logro) {
					if ($texto_logro == '') {
						continue;
					}
					$lista .= '• ' . $texto_logro . '<br>';
				}
			?>

			@if( !config('calificaciones.ocultar_logros_si_hay_logros_adicionales') )
				<li> {!! $lista !!} </li>
			@else
				@if ( $n_nom_logros_adicionales == 0 )
					<li> {!! $lista !!} </li>
				@endif
			@endif			
		@endforeach

		@foreach($logros_adicionales as $un_logro_adicional)
			<?php
				if(empty($un_logro_adicional))
				{
					continue;
				}

				$arr_logros = explode('•',$un_logro_adicional->descripcion);
				$lista2 = '';
				foreach ($arr_logros as $texto_logro) {
					if ($texto_logro == '') {
						continue;
					}
					$lista2 .= '• ' . $texto_logro . '<br>';
				}
			?>
			<li> {!! $lista2 !!} </li>
		@endforeach
	</ul>
</td>