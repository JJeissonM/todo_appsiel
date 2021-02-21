<div style="border: solid 1px; border-bottom: solid 2px; border-right: solid 2px; border-radius: 5px; padding: 10px; margin: 10px;">
	<h5> <b> La actividad ya ha sido calificada o se venció la fecha de entrega. </b> </h5>
	<hr>
	<b> Respuesta enviada: </b> {!! $respuesta->respuesta_enviada !!}
	<br>
	<b>Archivo adjunto: </b>
	@if( $respuesta->adjunto != '' )
		&nbsp;&nbsp;
		<a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/img/adjuntos_respuestas_estudiantes/'.$respuesta->adjunto }}" class="btn btn-success btn-sm" target="_blank"> <i class="fa fa-file"></i> {{ $respuesta->adjunto }} </a>
	@endif

	<br><br>
	<hr>
	<div class="well">
		<b> Anotación del profesor: </b>
		<br>
		{{ $respuesta->calificacion }}
	</div>
</div>