@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-sm-offset-2 col-sm-8">
			<div class="panel panel-success">
				<div class="panel-heading" align="center">
					<h4>Creación de una nueva matricula</h4>
				</div>
				
				<div class="panel-body">
					<form action="{{ url('matriculas/crear_nuevo?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}" method="POST" class="form-horizontal">
						{{ csrf_field() }}

						<input type="hidden" name="id_colegio" id="id_colegio" value="{{ Auth::user()->id_colegio }}">

						<!-- estudiante nombres 
						-->
						<div class="form-group">
								{{ Form::bsSelect('id_inscripcion',null,'Ingrese nombre o número de documento del estudiante',$candidatos,['class'=>'combobox','required'=>'required']) }}
						</div>
						
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
		</div>
	</div>	
@endsection