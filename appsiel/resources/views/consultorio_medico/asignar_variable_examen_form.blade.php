{{ Form::open(array('url'=>'pqr/guardar_nota')) }}
	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
			<br/><br/>
			<div class="row">
				<div class="col-md-12">
					{{ Form::bsTextArea('detalle',null,'Respuesta',$opciones,[]) }}
				</div>

				{{ Form::hidden('registro_modelo_padre_id',$registro_modelo_padre_id) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
			</div>
			<div align="center">
				<br/>
				{{ Form::submit('Guardar', array('class' => 'btn btn-primary btn-sm')) }}
			</div>
			<br/><br/>
		</div>
	</div>
{{ Form::close() }}
<br/><br/>