<?php
	if ($firmas[$i] != 'No cargada') {
		$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/'.$nombre_archivo.'.png';
	}else{
		$url_firma = '';
	}	
?>
@if( $url_firma != '')
	<img src="{{ $url_firma }}" style="margin-bottom: -20px; max-height:70px; width:auto;"/>
@else
	_____________________________
@endif