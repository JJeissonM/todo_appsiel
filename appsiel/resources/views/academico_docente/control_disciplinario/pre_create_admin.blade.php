@extends('academico_docente.control_disciplinario.pre_create')

@section('campos_selects')

	<div class="row" style="padding:5px;">
		{{ Form::bsSelect('curso_id', null, 'Curso', $cursos, ['required' => 'required'] ) }}
	</div>

	<div class="row" style="padding:5px;">
		{{Form::bsSelect('asignatura_id', null, 'Asignatura',[], ['required' => 'required'])}}
	</div>

	{{ Form::hidden('aux_curso_id', 0) }}

@endsection

@section('scripts')
	<script>
		$(document).ready(function(){

			$("#curso_id").on('change',function(){
				var curso_id = $(this).val();
				$("#asignatura_id").html('<option value=""></option>');
		    	
		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('get_select_asignaturas') }}" + "/" + curso_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    
		                    $('#div_cargando').hide();
							
							$("#asignatura_id").html(datos);
							
							$("#asignatura_id").focus();
				        },
				        error: function(xhr) {
		                    $('#div_cargando').hide();
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					$("#asignatura_id").html('<option value=""></option>');
				}
			});

			$("#asignatura_id").on('change',function(){
				$("#btn_continuar").focus();
			});


		});
	</script>
@endsection