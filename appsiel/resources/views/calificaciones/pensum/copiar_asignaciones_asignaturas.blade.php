@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
    	<div class="marco_formulario">

    		<br><br>

    		<div class="well">
				<h4 style="text-align: center; width: 100%;"> Copiar asignaciones </h4>
				Este proceso copia todas las asignaciones de asignaturas a cursos desde un año lectivo (origen) a otro año lectivo (destino).
				
				<br>
    			NOTA: No deben haber asignaturas asignadas en el año destino.
    			
    			<br>
    			<div class="alert alert-warning">
				  <strong>¡Sea cuidadoso al ejecutar este proceso!</strong> No se podrá revertir.
				</div>
			</div>

    		<div class="row">
    			<div class="col-md-5">

    				<div class="row" style="padding:5px;">					
						<label class="control-label col-sm-4" > <b> Año Lectivo origen: </b> </label>

						<div class="col-sm-8">
							{{ Form::select('periodo_lectivo_origen_id', $periodos_lectivos, null, ['id' => 'periodo_lectivo_origen_id', 'class' => 'form-control' ] ) }}
						</div>					 
					</div>

    			</div>

    			<div class="col-md-5">

    				<div class="row" style="padding:5px;">					
						<label class="control-label col-sm-4" > <b> Año Lectivo destino: </b> </label>

						<div class="col-sm-8">
							{{ Form::select('periodo_lectivo_destino_id', $periodos_lectivos, null, ['id' => 'periodo_lectivo_destino_id', 'class' => 'form-control'] ) }}
						</div>					 
					</div>

    			</div>

    			<div class="col-md-2">
    				<button class="btn btn-info btn-sm" id="btn_copiar" disabled="disabled"> <i class="fa fa-copy"></i> Copiar</button>
    			</div>    				
    		</div>
    		

			{{ Form::Spin('128') }}

			<div class="row">
				<div class="alert alert-success" id="mensaje_ok" style="display: none;">
				  <strong>¡Asignaciones copiadas exitosamente!</strong>
				</div>	
			</div>

	    </div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#periodo_lectivo_origen_id').focus();

			$('#periodo_lectivo_origen_id').on('change',function()
			{
				$('#popup_alerta_danger').hide();
				$('#periodo_lectivo_destino_id').val('');
				$('#btn_copiar').attr('disabled','disabled');

				if ( $(this).val() == '') { return false; }

				$('#periodo_lectivo_destino_id').focus();

			});

			$('#periodo_lectivo_destino_id').on('change',function()
			{
				$('#popup_alerta_danger').hide();
				$('#btn_copiar').attr('disabled','disabled');

				if ( $('#periodo_lectivo_origen_id').val() == '')
				{ 
					mostrar_popup( 'Debe seleccionar un año origen.' );
					$('#periodo_lectivo_origen_id').focus();
					return false;
				}

				if ( $(this).val() == '')
				{ 
					$('#popup_alerta_danger').hide();
					return false;
				}

				if ( $('#periodo_lectivo_origen_id').val() ==  $('#periodo_lectivo_destino_id').val() )
				{
					mostrar_popup('Los años lectivos origen y destino deben ser diferentes.');
					return false;
				}

				validar_asignaciones_periodo_destino();

			});

			function mostrar_popup( mensaje )
			{
				$('#popup_alerta_danger').show();
				$('#popup_alerta_danger').text( mensaje );
			}

			function validar_asignaciones_periodo_destino()
			{
				var periodo_lectivo_destino_id = $('#periodo_lectivo_destino_id').val();

	    		$('#div_cargando').show();

				var url = "{{ url('calificaciones/periodo_lectivo_tiene_asignaciones') }}" + "/" + periodo_lectivo_destino_id;

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

		        		$('#div_cargando').hide();

		        		if ( datos == 1 )
		        		{
		        			mostrar_popup('El Año destino ya tiene asignaciones. No se puede continuar.');
		        			$('#btn_copiar').attr('disabled','disabled');
		        			return false;
		        		}
	    				
	    				$('#popup_alerta_danger').hide();
						$('#btn_copiar').removeAttr('disabled');
						$('#btn_copiar').focus();
			        }
			    });
			}

			$("#btn_copiar").on('click',function(event){
		    	event.preventDefault();
		    	if ( confirm("Realmente quiere copiar todas las asignaciones del " + $('#periodo_lectivo_origen_id option:selected' ).text() + " al " + $('#periodo_lectivo_destino_id option:selected' ).text() + "?") )
			 	{
			 		$("#div_spin").show();
			 		$("#div_cargando").show();
					$('#btn_copiar').attr('disabled','disabled');

			 		var periodo_lectivo_origen_id = $('#periodo_lectivo_origen_id').val();
			 		var periodo_lectivo_destino_id = $('#periodo_lectivo_destino_id').val();

					var url = "{{ url('calificaciones/copiar_asignaciones/procesar') }}" + "/" + periodo_lectivo_origen_id + "/" + periodo_lectivo_destino_id;

					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

			        		$('#div_cargando').hide();
			        		$("#div_spin").hide();

			        		$("#mensaje_ok").show();
				        }
				    });

			 		
			 	}
		    });

		});

	</script>
@endsection