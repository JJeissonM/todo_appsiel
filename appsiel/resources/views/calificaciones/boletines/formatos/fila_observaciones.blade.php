<tr>
	<td colspan="{{$cant_columnas}}" style="font-size: {{$tam_letra}}mm; padding: 10px;">
		<b> {{ config('calificaciones.etiqueta_observaciones') }}: </b>
		<ul style="padding: 10px;">
			@if( !is_null( $registro->observacion ) )

				<?php
					$arr_observacion = explode( '•', $registro->observacion->observacion );
				?>

				@foreach ($arr_observacion as $texto_observacion)
					
					<?php 
						if ($texto_observacion == '') {
							continue;
						}
					?>
					
					<li> {!! $texto_observacion !!} </li>
					
				@endforeach
				
			@endif
		</ul>
	</td>
</tr>