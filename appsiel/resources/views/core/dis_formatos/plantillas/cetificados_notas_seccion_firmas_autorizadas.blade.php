<div style="width: 100%;">
	
	<div style="float: left; width: 50%; text-align: center;">
		<?php
			$url_firma = '';
			if ( $firma_autorizada_1->imagen != '' )
			{
				$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_1->imagen;
			}
		?>
		@if( $url_firma != '')
			<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
		@endif
		<br><br><br>
		<br>
		<p style="border-top: 1px solid black; margin: 0 50px;">
			{{ $firma_autorizada_1->tercero->descripcion }}
			<br>
			{{ $firma_autorizada_1->titulo_tercero }}
		</p>
	</div>

	<div style="float: left; width: 50%; text-align: center;">
		<?php
			$url_firma = '';
			if ( $firma_autorizada_2->imagen != '' )
			{
				$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_2->imagen;
			}
		?>
		@if( $url_firma != '')
			<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
		@endif
		<br><br><br>
		<br>
		<p style="border-top: 1px solid black; margin: 0 50px;">
			{{ $firma_autorizada_1->tercero->descripcion }}
			<br>
			{{ $firma_autorizada_2->titulo_tercero }}
		</p>
	</div>
	
</div>

<div style="clear: both"></div>