@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Impresión de formatos</h4>
		    <hr>
			{{ Form::open(['url' => 'gestion_documental/generar_formato',  'class' => 'form-horizontal' ]) }}

				<div class="row" style="padding:5px;">
	                {{ Form::bsSelect('formato_id', null, 'Seleccionar un formato', $formatos ,[]) }}
	            </div> 

	            <!-- <button>enviar</button> -->
				<div id="resultado_consulta">

				</div>

			{{Form::close()}}

		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#formato_id').change(function(){

				$('#div_cargando').show();
						
				var url = '../gestion_documental/cargar_controles/'+$('#formato_id').val();
				$.get( url, function( respuesta ) {
					$('#div_cargando').hide();
			        $('#resultado_consulta').html(respuesta);
				});

			});
			
			$(document).on('change', '#curso_id', function() 
			{
				var curso_id = $(this).val();
				$("#id_estudiante").html('<option value=""></option>');
		    	
		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('get_select_estudiantes_del_curso') }}" + "/" + curso_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    
		                    $('#div_cargando').hide();
							
							$("#id_estudiante").html(datos);
							
							$("#id_estudiante").focus();
				        },
				        error: function(xhr) {
		                    $('#div_cargando').hide();
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					$("#id_estudiante").html('<option value=""></option>');
				}

			});
		});
		
	</script>
@endsection