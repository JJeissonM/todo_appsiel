<ul class="lista_logros">
	@if ( !is_null($linea->logros) )
		@foreach( $linea->logros as $un_logro )
			<?php
				$arr_logros = explode('•',$un_logro->descripcion);
				$lista = '';
				foreach ($arr_logros as $texto_logro) {
					
					if ($texto_logro == '') {
						continue;
					}

					if ($convetir_logros_mayusculas == 'Si') {
						$lista .= ' • ' . strtoupper($texto_logro) . '<br>';
					}else{
						$lista .= ' • ' . $texto_logro . '<br>';
					}
					
				}
			?>
			<li> {!! $lista !!} </li>
		@endforeach
	@endif


	@if ( !is_null($linea->logros_adicionales) )
		<?php
			$lista = '';
		?>
		@foreach( $linea->logros_adicionales as $un_logro )
			<?php
				if ($convetir_logros_mayusculas == 'Si') {
					$lista .= ' • ' . strtoupper($un_logro->descripcion) . '<br>';
				}else{
					$lista .= ' • ' . $un_logro->descripcion . '<br>';
				}
			?>
		@endforeach
		<li> {!! $lista !!} </li>
	@endif
</ul>