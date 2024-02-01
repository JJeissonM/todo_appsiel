@extends('layouts.principal')

@section('content')

	<style>
		table th {
		    padding: 15px;
		    text-align: center;
			border-bottom:solid 2px;
			background-color: #E5E4E3;
		}
		table td {
		    padding: 2px;
		}
	</style>

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		@include('calificaciones.create_form_selections')
	</div>
	
	<div class="container-fluid">
		<div class="marco_formulario">
			<div id="div_form_ingreso">
				{{ Form::Spin( 42 ) }}
				
			</div>
		</div>
	</div>

	<br/><br/>	

@endsection


@section('scripts')
	
	<script src="{{ asset( 'assets/js/calificaciones/create.js?aux=' . uniqid() )}}"></script>
	
	<script>
		$(document).ready(function(){

			$("#curso_id").on('change',function(){

				$('#div_form_ingreso').html( '' );
				$("#id_asignatura").html('<option value=""></option>');
		    	
				var curso_id = $(this).val();
		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('get_select_asignaturas') }}" + "/" + curso_id + "/" + periodo_id;
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
			
		});
	</script>
@endsection