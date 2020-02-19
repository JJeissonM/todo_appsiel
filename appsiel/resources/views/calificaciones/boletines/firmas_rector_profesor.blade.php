<?php
	if ($firmas[$i] != 'No cargada') {
		$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/'.$nombre_archivo.'.png';
	}else{
		$url_firma = '';
	}	
?>
@if( $url_firma != '')
	<img src="{{ $url_firma }}" width="250px" height="70px" style="margin-bottom: -20px;"/>
@else
	_____________________________
@endif