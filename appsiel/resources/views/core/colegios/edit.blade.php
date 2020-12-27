<div class="row" style="padding:5px;">
	{{ Form::bsButtonsForm(Input::get('return'))}}
</div>

<div class="row" style="padding:5px;">
	<div class="form-group">
		<label for="escudo" class="col-sm-3 control-label">Escudo</label>
		<img alt="escudo.jpg" src="{{ asset(config('configuracion.url_instancia_cliente').$ruta_imgescudo.'?'.rand(1,1000)) }}" style="width: 150px; height: 150px;" />
	</div>
	<label for="escudo" class="control-label">Cambiar escudo</label>
	{{ Form::file('escudo',['accept'=>'.jpg']) }}
</div>

<div class="row" style="padding:5px;">
	{{ Form::bsText('descripcion',null,'Descripción',[]) }}
</div>

<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('resolucion',null,'Resolución',[]) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('slogan',null,'Slogan',[]) }}
		</div>		
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('direccion',null,'Dirección',[]) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('telefonos',null,'Teléfono',[]) }}
		</div>		
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('ciudad',null,'Ciudad',[]) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('maneja_puesto',null,'Maneja puesto',['N'=>'No','S'=>'Si'],[]) }}
		</div>		
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('piefirma1',null,'Pie de firma #1',[]) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText('piefirma2',null,'Pie de firma #2',[]) }}
		</div>		
	</div>
</div>

{{ Form::hidden('return',Input::get('return')) }}
{{ Form::hidden('id_app',Input::get('id'))}}