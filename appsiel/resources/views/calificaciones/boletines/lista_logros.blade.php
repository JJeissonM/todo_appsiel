<ul class="lista_logros">
	@if ( $linea->logros != null )
		@foreach( $linea->logros as $un_logro )
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