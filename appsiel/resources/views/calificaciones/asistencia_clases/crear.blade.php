@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Tomar asistencia</h4>
		    <hr>

		    {{Form::open(['url'=>'/calificaciones/asistencia_clases/continuar_creacion?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')],['class'=>'form-horizontal']) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsFecha('fecha',date('Y-m-d'),'Fecha',[],[]) }}
			    </div>
					
				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('curso_id', '', 'Curso', $registros, []) }}
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsSelect('id_asignatura', null, 'Asignatura',[], ['required'=>'required'])}}
				</div>

			    <br/>
			    <button class="btn btn-success" id="btn_continuar"> <i class="fa fa-btn fa-forward"></i> Continuar</button>

			{{Form::close()}}
		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#curso_id").focus();

			$("#curso_id").on('change',function(){
				$("#id_asignatura").focus();
			});

			$("#curso_id").on('change',function(){
				var curso_id = $(this).val();
				$("#id_asignatura").html('<option value=""></option>');
		    	
		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('get_select_asignaturas') }}" + "/" + curso_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    
		                    $('#div_cargando').hide();
							
							$("#id_asignatura").html(datos);
							
							$("#id_asignatura").focus();
				        },
				        error: function(xhr) {
		                    $('#div_cargando').hide();
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					$("#id_asignatura").html('<option value=""></option>');
				}
			});

			$("#id_asignatura").on('change',function(){
				$("#btn_continuar").focus();
			});


		});
	</script>
@endsection