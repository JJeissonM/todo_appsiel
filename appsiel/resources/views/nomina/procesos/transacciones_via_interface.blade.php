@extends('core.procesos.layout')

@section( 'titulo', 'Cargar archivo plano para liquidación de conceptos' )

@section('detalles')
	<p>
		Este proceso permiter cargar un archivo plano (.txt) con una estructura definida para liquidar conceptos de nómina en un documento específico.
	</p>
	
	Luego se almacenan los registros del documento de nómina según las líneas de registros cargadas en el archivo plano.
	
	<br>
@endsection

@section('formulario')
	<div class="row">

		<div class="row" style="padding:5px;">					
			<label class="control-label col-sm-4" > <b> Documento de liquidación: </b> </label>

			<div class="col-sm-8">
				{{ Form::select('nom_doc_encabezado_id',App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id' ]) }}
			</div>					 
		</div>

		<div class="row" style="padding:5px;">					
			<label class="control-label col-sm-4" > <b> Archivo plano: </b> </label>

			<div class="col-sm-8">
				{{ Form::file('archivo_plano', [ 'class' => 'form-control', 'id' => 'archivo_plano', 'accept' => 'text/plain' ]) }}
			</div>					 
		</div>

		<div class="col-md-4">
			<button class="btn btn-success" id="btn_cargar" disabled="disabled"> <i class="fa fa-calculator"></i> Cargar </button>
		</div>    				
	</div>
@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#periodo_lectivo_id').focus();

			$('#periodo_lectivo_id').on('change',function()
			{
				$("#mensaje_ok").html('');
				$("#div_spin").hide();
				$('#btn_calcular').attr('disabled', 'disabled');
				$('#popup_alerta_danger').hide();

				if ( $(this).val() == '')
				{ 
					$('#div_resultado').html( '' );
					return false;
				}

				$('#div_cargando').show();
				$('#div_advertencia').hide();

				var url = "{{ url('consultar_periodos_periodo_lectivo') }}" + "/" + $('#periodo_lectivo_id').val();

				$.get( url, function( datos ){

	        		$('#div_cargando').hide();

	        		$('#div_resultado').html( datos[0] );

	        		switch( datos[1] )
	        		{
						case 0: // Incorrecto. No hay periodo final
							$('#popup_alerta_danger').hide();
							$('#btn_calcular').attr('disabled', 'disabled');
							break;

						case 1: // Correcto. Hay un solo periodo final.
							$('#popup_alerta_danger').hide();
							$('#btn_calcular').removeAttr('disabled');
							break;

						default: // Incorrecto.
		        			mostrar_popup('Existe más de un (1) periodo final en el Año Lectivo seleccionado. No se puede continuar.');
		        			$('#btn_calcular').attr('disabled','disabled');
		        			return false;
							break;
					}    				
			    });

			});


			function mostrar_popup( mensaje )
			{
				$('#popup_alerta_danger').show();
				$('#popup_alerta_danger').text( mensaje );
			}



			$("#btn_calcular").on('click',function(event){
		    	event.preventDefault();

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				$('#btn_calcular').attr('disabled','disabled');

				var url = "{{ url('calcular_promedio_notas_periodo_final') }}" + "/" + $('#periodo_lectivo_id').val();

				$.get( url, function(datos){

	        		$('#div_cargando').hide();
	        		$("#div_spin").hide();

	        		$("#mensaje_ok").html( '<div class="alert alert-success"><strong>Promedios del periodo final generados correctamente!</strong><br> Se almacenaron '+ datos +' calificaciones. </div>' );
			    });
			
		    });



		});

	</script>
@endsection