@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

    		<br><br>

    		<div class="well">
				<h4 style="text-align: center; width: 100%;"> Copiar carga académica </h4>
				Este proceso copia todas las cargas académicas de cada Profesor desde un año lectivo (origen) a otro año lectivo (destino).
				
				<br>
    			NOTA: No deben haber ninguna carga académica en el año destino.
    			
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
				  <strong>Cargas académicas copiadas exitosamente!</strong>
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
					mostrar_popup( 'Debe seleccionar un periodo origen.' );
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
					mostrar_popup('Los periodos lectivos origen y destino deben ser diferentes.');
					return false;
				}

				validar_carga_academica_periodo_destino();

			});

			function mostrar_popup( mensaje )
			{
				$('#popup_alerta_danger').show();
				$('#popup_alerta_danger').text( mensaje );
			}

			function validar_carga_academica_periodo_destino()
			{
				var periodo_lectivo_destino_id = $('#periodo_lectivo_destino_id').val();

	    		$('#div_cargando').show();

				var url = "{{ url('academico_docente/periodo_lectivo_tiene_carga_academica') }}" + "/" + periodo_lectivo_destino_id;

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

		        		$('#div_cargando').hide();

		        		if ( datos == 1 )
		        		{
		        			mostrar_popup('El Periodo destino ya tiene carga academica. No se puede continuar.');
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
		    	if ( confirm("Realmente quiere copiar todas las cargas academicas del " + $('#periodo_lectivo_origen_id option:selected' ).text() + " al " + $('#periodo_lectivo_destino_id option:selected' ).text() + "?") )
			 	{
			 		$("#div_spin").show();
			 		$("#div_cargando").show();
					$('#btn_copiar').attr('disabled','disabled');

			 		var periodo_lectivo_origen_id = $('#periodo_lectivo_origen_id').val();
			 		var periodo_lectivo_destino_id = $('#periodo_lectivo_destino_id').val();

					var url = "{{ url('academico_docente/copiar_carga_academica/procesar') }}" + "/" + periodo_lectivo_origen_id + "/" + periodo_lectivo_destino_id;

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