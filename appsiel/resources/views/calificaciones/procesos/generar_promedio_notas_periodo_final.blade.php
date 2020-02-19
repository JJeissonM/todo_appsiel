@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<div class="container-fluid">
	    		<br><br>

	    		<div class="well">
					<h4 style="text-align: center; width: 100%;"> Calcular promedios para las calificaciones del periodo final </h4>
					Este proceso calcula el promedio de las asignaturas del Año Lectivo seleccionado.
					
					<br>
	    			Luego almacena los promedios calculados en el PERIODO FINAL (Debe estar creado con el indicador: <b> Es periodo de promedios </b>)
	    			
	    			<br>
				</div>

	    		<div class="row">
	    			<div class="col-md-5">

	    				<div class="row" style="padding:5px;">					
							<label class="control-label col-sm-4" > <b> Año Lectivo origen: </b> </label>

							<div class="col-sm-8">
								{{ Form::select('periodo_lectivo_id', $periodos_lectivos, null, ['id' => 'periodo_lectivo_id', 'class' => 'form-control' ] ) }}
							</div>					 
						</div>

	    			</div>

	    			<div class="col-md-5">

	    				<div class="row" style="padding:5px;" id="div_resultado">

						</div>

	    			</div>

	    			<div class="col-md-2">
	    				<button class="btn btn-success" id="btn_calcular" disabled="disabled"> <i class="fa fa-calculator"></i> Calcular y almacenar</button>
	    			</div>    				
	    		</div>
	    		

				{{ Form::Spin('128') }}

				<div class="row" id="mensaje_ok">
						
				</div>

			</div>
	    </div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
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

	        		$("#mensaje_ok").html( '<div class="alert alert-success" style="display: none;"><strong>Promedios del periodo final generados correctamente!</strong><br> Se almacenaron '+ datos +' calificaciones. </div>' );
			    });
			
		    });



		});

	</script>
@endsection