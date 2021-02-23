<textarea class="form-control" rows="4" name="respuesta_enviada_2" id="respuesta_enviada_2" cols="250" required="required">{{ $respuesta->respuesta_enviada }}</textarea>

<br><br>
@if( $respuesta->adjunto == '')
	<h4>Adjuntar un archivo</h4>
	<input type="file" name="adjunto" id="adjunto" accept=".xlsx,.pdf,.docx,.ppt,.pptx,.doc,.xls,.jpg,.png,.jpeg" class="form-control">
	<b style="color: red;"> El tamaño máximo del archivo debe ser de 20M </b>
@else
	<div class="row">
		<div class="col-md-8">
			<b>Archivo adjunto: </b>
			&nbsp;&nbsp;
			<a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/img/adjuntos_respuestas_estudiantes/'.$respuesta->adjunto }}" class="btn btn-success btn-sm" target="_blank"> <i class="fa fa-file"></i> {{ $respuesta->adjunto }} </a>

		</div>
		<div class="col-md-4">
			<a href="{{ url('remover_archivo_adjunto/'.$respuesta->id.'?id='.Input::get('id') ) }}" class="btn btn-danger btn-xs"> <i class="fa fa-trash"></i>&nbsp;Remover adjunto</a>
			<br>
			<b style="color: red;">NOTA: Guarde primero su respuesta antes de eliminar el archivo. Si quita el archivo adjunto <u>SIN GUARDAR</u> se borra también todo lo ingresado. </b>
		</div>
	</div>
	
@endif
<br><br>

<div class="form-group">
	<a href="#" class="btn btn-primary btn-xs" id="btn_guardar"> <i class="fa fa-save"></i>&nbsp;Guardar respuesta</a>
</div>