@extends('layouts.principal')
@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		@include('academico_docente.calificar_desempenios.create_form_selections')
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

	<script src="{{ asset( 'assets/js/calificaciones/desempenios/create.js?aux=' . uniqid() )}}"></script>
	
	<script>
		$(document).ready(function(){

			$('#curso_id').on('change',function()
			{
				$('#div_form_ingreso').html( '' );
				$("#id_asignatura").html('<option value=""></option>');

				if ( $(this).val() == '') { return false; }				

				$('#div_cargando').show();

				var url = "../../../../calificaciones_opciones_select_asignaturas_del_curso_por_usuario/" + $('#curso_id').val() + "/" + $('#periodo_lectivo_id').val() + "/Activo";

				$.ajax({
					url: url,
					type: 'get',
					success: function(datos){

						$('#div_cargando').hide();
						
						$('#id_asignatura').html( datos );
						$('#id_asignatura').focus();
					},
					error: function(xhr) {
						$('#div_cargando').hide();
						alert('Error en los datos seleccionados. '+xhr);
					}
				});			
			});


		});
	</script>
@endsection