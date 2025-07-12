<ul class="lista_logros">

	@if( !config('calificaciones.ocultar_logros_si_hay_logros_adicionales') )
		@include('calificaciones.boletines.lista_logros_lineas')
	@else
		@if ( $linea->logros_adicionales != null )
			@include('calificaciones.boletines.lista_logros_lineas')
		@endif
	@endif

	@if ( $linea->logros_adicionales != null )
		@foreach( $linea->logros_adicionales as $un_logro )
			<?php
				$arr_logros = explode('•',$un_logro->descripcion);
			?>
			@foreach ($arr_logros as $texto_logro)
				
				<?php 
					if ($texto_logro == '') {
						continue;
					}
				?>
				
				@if ($convetir_logros_mayusculas == 'Si')
					<li> {!! strtoupper($texto_logro) !!} </li>
				@else
					<li> {!! $texto_logro !!} </li>
				@endif
				
			@endforeach
		@endforeach
		
	@endif
</ul>