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

			function mostrarCargaAsignaturas()
			{
				$("#spinner_asignaturas").show();
				$("#id_asignatura").prop('disabled', true);
			}

			function ocultarCargaAsignaturas()
			{
				$("#spinner_asignaturas").hide();
				$("#id_asignatura").prop('disabled', false);
			}

			function activarCargaContinuar()
			{
				$("#btn_continuar").prop('disabled', true);
				$("#btn_continuar .lbl_btn_continuar").hide();
				$("#btn_continuar .spinner_btn_continuar").show();
				$('#div_form_ingreso').html('');
				$('#div_cargando').show();
			}

			function desactivarCargaContinuar()
			{
				$("#btn_continuar").prop('disabled', false);
				$("#btn_continuar .spinner_btn_continuar").hide();
				$("#btn_continuar .lbl_btn_continuar").show();
				$('#div_cargando').hide();
			}

			$("#curso_id").on('change',function(){

				$('#div_form_ingreso').html( '' );
				$("#id_asignatura").html('<option value=""></option>');
				var curso_id = $(this).val();
				var periodo_id = $("#id_periodo").val();

				if(periodo_id == '')
				{
					periodo_id = 'null';
				}
		    	
		    	if( curso_id != '' ){

					mostrarCargaAsignaturas();
				    $('#div_cargando').show();

					var url = "{{ url('get_select_asignaturas') }}" + "/" + curso_id + "/" + periodo_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    
		                    $('#div_cargando').hide();
							ocultarCargaAsignaturas();
							
							$("#id_asignatura").html(datos);
							
							$("#id_asignatura").focus();
				        },
				        error: function(xhr) {
		                    $('#div_cargando').hide();
							ocultarCargaAsignaturas();
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					ocultarCargaAsignaturas();
					$("#id_asignatura").html('<option value=""></option>');
				}
			});

			window.activarCargaContinuar = activarCargaContinuar;
			window.desactivarCargaContinuar = desactivarCargaContinuar;
			
		});
	</script>
@endsection
