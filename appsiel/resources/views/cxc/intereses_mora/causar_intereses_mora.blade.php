@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			{{ Form::open(['url'=>'cxc_ajax_causar_intereses','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-md-10">
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'modo_causacion', null, 'Modo de causación', [ 'Programada' => 'Programada: Asignar a facturación masiva', 'Inmediata' => 'Inmediata: Generar cuentas de cobro'], [ 'id' => 'modo_causacion', 'required' => 'required' ] ) }}
					            </div>
								<div class="row" style="padding:5px;">
									<div class="alert alert-warning">
                                      <strong>¡Advertencia!</strong> Este proceso no se puede reversar.
                                    </div>
					            </div>
					        </div>
							<div class="col-md-6">
								<div id="controles" style="display: none;">
									<div class="row" style="padding:5px;">
										{{ Form::bsFecha( 'fecha', null, 'Fecha', '', [ 'id' => 'fecha' ] ) }}
						            </div>
									<div class="row" style="padding:5px;">

			                            <div class="checkbox">
										  <label><input type="checkbox" name="confirmacion" id="confirmacion" checked="checked">Confirmar la creación de Cuentas de cobro</label>
										</div>
						            </div>
						        </div>
					        </div>
					    </div>
			        </div>

					<div class="col-md-2">
						<div class="row" style="padding:5px;">
							<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
			            </div>
			        </div>
				</div>

				{{ Form::hidden('core_empresa_id',\Auth::user()->empresa_id) }}
				{{ Form::hidden('core_tipo_doc_app_id', 29) }}
				{{ Form::hidden('core_tipo_transaccion_id', 15) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

			{{ Form::close() }}
			 <!--<button id="btn_ir">ir</button>	-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			<div id="resultado_consulta">
				<h4>Intereses Calculados</h4>
				<hr>

				{!! $tabla !!}
			</div>

		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#modo_causacion').change(function(){
				if ( $('#modo_causacion').val() == 'Inmediata' ) 
				{
					$('#controles').show(500);
					$('#fecha').attr('required','required');
					$('#confirmacion').removeAttr('checked');
					$('#fecha').focus();
				}else{
					$('#controles').hide();
					$('#confirmacion').attr('checked','checked');
					$('#fecha').removeAttr('required');
					$('#btn_generar').focus();
				}
			});

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#confirmacion').focus();				
				}
			});

			$('#confirmacion').click(function(event){
				$('#btn_generar').focus();
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event)
			{
				if (validar_requeridos()) 
				{
					if ($('#confirmacion').is(":checked")) 
					{
					
						$('#resultado_consulta').html( '' );
						$('#div_cargando').show();

						// Preparar datos de los controles para enviar formulario
						var form_consulta = $('#form_consulta');
						var url = form_consulta.attr('action');
						var datos = form_consulta.serialize();
						// Enviar formulario de ingreso de productos vía POST
						$.post(url,datos,function(respuesta){
							$('#div_cargando').hide();
							$('#resultado_consulta').html(respuesta);
						});
					}else{
						$('#confirmacion').focus();
						alert('Debe confirmar la creación de CxC.');
					}
				}
			});

			function validar_requeridos(){
				var control = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = true;
					}
				});
				return control;
			}
		});

		
	</script>
@endsection