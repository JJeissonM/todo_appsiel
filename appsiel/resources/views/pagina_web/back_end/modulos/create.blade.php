@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>
			
			<form action="{{ url('pagina_web/crear_nuevo_modulo?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}" method="POST" class="form-horizontal">
				{{ csrf_field() }}

				<!-- estudiante nombres 
				-->
				<div class="form-group">
						{{ Form::bsSelect('tipo_modulo',null,'Seleccionar un tipo de módulo',$tipos_modulos,['class'=>'combobox','required'=>'required']) }}
				</div>

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				
		        <!-- Add matricula Button -->
				<div class="form-group">
					<div class="col-sm-offset-4 col-sm-6">
						<button class="btn btn-success" id="btn_continuar">
							<i class="fa fa-btn fa-forward"></i> Continuar
						</button>
					</div>

				</div>
			</form>					
		</div>
	</div>
@endsection