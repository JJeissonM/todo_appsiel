<div style="width: 100%;">
	
	<div style="float: left; width: 50%; text-align: center;">
		<?php
			$url_firma = '';
			$tercero_descripcion = '';
			$titulo_tercero = '';
			if ( $firma_autorizada_1 != null ) {
				if ( $firma_autorizada_1->imagen != '' )
				{
					$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_1->imagen;
				}

				$tercero_descripcion = $firma_autorizada_1->tercero->descripcion;
				$titulo_tercero = $firma_autorizada_1->titulo_tercero;
			}			
		?>
		@if( $url_firma != '' && $mostrar_imagen_firma_autorizada_1 )
			<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
		@endif
		<br><br>
		<br>
		<p style="border-top: 1px solid black; margin: 0 50px;">
			{{ $tercero_descripcion }}
			<br>
			{{ $titulo_tercero }}
		</p>
	</div>

	<div style="float: left; width: 50%; text-align: center;">
		<?php
			$url_firma = '';
			$tercero_descripcion = '';
			$titulo_tercero = '';
			if ( $firma_autorizada_1 != null ) {
				if ( $firma_autorizada_2->imagen != '' )
				{
					$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_2->imagen;
				}
				$tercero_descripcion = $firma_autorizada_2->tercero->descripcion;
				$titulo_tercero = $firma_autorizada_2->titulo_tercero;
			}
		?>
		@if( $url_firma != ''  && $mostrar_imagen_firma_autorizada_2 )
			<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
		@endif
		<br><br>
		<br>
		<p style="border-top: 1px solid black; margin: 0 50px;">
			{{ $tercero_descripcion }}
			<br>
			{{ $titulo_tercero }}
		</p>
	</div>
	
</div>