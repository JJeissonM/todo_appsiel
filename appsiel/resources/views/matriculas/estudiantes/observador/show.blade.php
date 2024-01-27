@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'matriculas/estudiantes/observador/imprimir_observador/'.$id . '?matricula_id=' . $matricula_a_mostrar->id ) }}

	&nbsp;&nbsp;&nbsp;<a class="btn-gmail" href="{{ url('matriculas/estudiantes/gestionresponsables/estudiante_id') . '?id=1&id_modelo=29&estudiante_id=' . $id }}" title="Gestionar responsables del estudiante"><i class="fa fa-btn fa-users"></i></a>
	
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'matriculas/estudiantes/observador/valorar_aspectos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&matricula_id=' . $matricula_a_mostrar->id ) }}
	
	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'matriculas/estudiantes/observador/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'matriculas/estudiantes/observador/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			{{ Form::bsSelect('matricula_id', $matricula_a_mostrar->id, 'Matrículas', $vec_matriculas ,['required' => 'required']) }}
			
			<br><br>
			<p style="text-align: center;">
				<button type="button" class="btn btn-primary btn-xs" id="btn_cambiar_matricula"> Cambiar curso
				</button>
			</p>

			<input type="hidden" name="estudiante_id" id="estudiante_id" value="{{$id}}">

			<input type="hidden" name="app_id" id="app_id" value="{{ Input::get('id') }}">
			<input type="hidden" name="modelo_id" id="modelo_id" value="{{ Input::get('id_modelo') }}">
			<input type="hidden" name="transaccion_id" id="transaccion_id" value="{{ Input::get('id_transaccion') }}">
		</div>

		<div class="marco_formulario">

			<?php
				echo $view_pdf;
			?>
			
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var URL = "{{ url('/') }}";

			$('#btn_cambiar_matricula').click(function(event){

				if ($('#matricula_id').val() == '') {
					Swal.fire({
						icon: 'info',
						title: 'Alerta!',
						text: 'Debe seleccionar una matrícula.'
					});

					return false;
				}

				location.href = URL + '/matriculas/estudiantes/observador/show/' + $('#estudiante_id').val() + '?id=1&id_modelo=180&id_transaccion=&matricula_id=' + $('#matricula_id').val();
				
			});			

			$('#matricula_id').on('change', function(event){

				if ($('#matricula_id').val() == '') {
					return false;
				}

				$('#btn_print').attr('href', 'http://localhost/appsiel_2021/matriculas/estudiantes/observador/imprimir_observador/' + $('#estudiante_id').val() + '?matricula_id=' + $('#matricula_id').val() );

				$('#btn_edit').attr('href', 'http://localhost/appsiel_2021/matriculas/estudiantes/observador/valorar_aspectos/' + $('#estudiante_id').val() + '?id=' + $('#app_id').val() + '&id_modelo=' + $('#modelo_id').val() + '&matricula_id='  + $('#matricula_id').val() );

			});
		});		
	</script>
@endsection