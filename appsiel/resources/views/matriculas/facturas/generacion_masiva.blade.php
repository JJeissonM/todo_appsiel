@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Generación de CxC</h4>
		    <hr>
			{{ Form::open(['url'=>'propiedad_horizontal','id'=>'form_generar_consulta_preliminar_cxc']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

				<!-- <button>Enviar</button> -->
				
			{{ Form::close() }}
			<div class="row">
				<div class="col-sm-offset-4 col-sm-6">
					<button class="btn btn-success btn-md" id="btn_generar_consulta_preliminar_cxc">
						<i class="fa fa-btn fa-forward"></i> Generar consulta
					</button>
				</div>
			</div>

			<ul class="nav nav-tabs">
			  <li class="active"><a href="#"><h3>Cuentas de cobro</h4></a></li>
			</ul>
			<br/>

		    <div class="well" id="div_alerta" style="display: none; font-size: 1.3em; padding: 10px;">		    	
			  	<div class="row">
			  		<div class="col-sm-6">
			  			<div class="list-group">
					        <a href="#" class="list-group-item active">
					            Resultados 
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="glyphicon glyphicon-list"></span> Total registros <span class="badge" id="total_cantidad"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="glyphicon glyphicon-home"></span> Total propiedades <span class="badge" id="total_propiedades"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="glyphicon glyphicon-usd"></span> Valor total <span class="badge" id="total_precio_total"></span>
					        </a>
					    </div>
			  		</div>
			  		<div class="col-sm-6">
			  			<div id="div_panel_derecho">
			  				<div class="alert alert-warning">
                                      <strong>¡Advertencia!</strong> Este proceso no se puede reversar.
                                    </div>
				  			<input type="checkbox" name="confirmacion" id="confirmacion"> Confirmar la creación de Cuentas de cobro
				  			<br/>
				  			<button class="btn btn-primary btn-md" id="btn_guardar_cxc">
								<i class="fa fa-btn fa-save"></i> Crear
							</button>
						</div>
			  		</div>			  	
			  	</div>
			  	<br/>
			</div>

			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			  <div class="modal-dialog modal-dialog-centered" role="document" align="center">
			  		Espere por favor... <br/>
			        <img src="{{ asset('assets/img/spinning-wheel.gif') }}" width="40px" height="40px">
			  </div>
			</div>

			<div id="btn_imprimir_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnPrint('cxc/imprimir_lote/') }}
			</div>
			<div id="btn_enviar_email_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnEmail('cxc/enviar_email_lote/') }}
				<br/><br/>
			</div>

			<h3 id="lbl_tabla" align="center"></h3>
		    <table class="table table-striped table-bordered" id="ingreso_cxc">
		        <thead>
		            <tr>
		                <th>Propiedad</th>
		                <th>Propietario</th>
		                <th width="280px">Servicio</th>
		                <th> Precio Unit. </th>
		                <th>Cantidad</th>
		                <th>Precio Total</th>
		            </tr>
		        </thead>
		        <tbody>
		            <tr>
		                <td colspan="4">&nbsp;</td>
		                <td> &nbsp;</td>
		                <td> &nbsp;</td>
		            </tr>
		        </tbody>
		    </table>			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = yyyy + '-' + mm + '-' + dd;

			$('#fecha').val( today );
			$('#fecha').focus();
			$('#core_tercero_id').removeAttr("required");

			$('#btn_generar_consulta_preliminar_cxc').click(function(e){			
				e.preventDefault();
				if (validar_requeridos()==1) {
					$('#myModal').modal({keyboard: false, backdrop: "static"});
					var form = $('#form_generar_consulta_preliminar_cxc');
					var url = form.attr('action').replace("propiedad_horizontal", "propiedad_horizontal/generar_consulta_preliminar_cxc");

					data = form.serialize();
					$.post(url,data,function(resultado){					
						$('#myModal').modal('hide');
						$('#lbl_tabla').html('Detalles');
						$('#ingreso_cxc').find('tbody').html(resultado[0]);
						$('#div_alerta').show();
						$('#total_precio_total').html('$'+resultado[1]);
						$('#total_cantidad').html(resultado[2]);
						$('#total_propiedades').html(resultado[3]);
					});
				}else{
					alert('Faltan campos por llenar.');
				}
			});

			$('#btn_guardar_cxc').click(function(e){
				e.preventDefault();
				if (validar_requeridos()==1) {
					if ($('#confirmacion').is(":checked")) {
						
						if(!confirm("¿Realmente desea generar todas las CxC consultadas?")){
							return false;
						}else{
							$('#myModal').modal({keyboard: false, backdrop: "static"});

							var form = $('#form_generar_consulta_preliminar_cxc');
							var url = form.attr('action');
							data = form.serialize();
							$.post(url,data,function(resultado){
								$('#btn_generar_consulta_preliminar_cxc').hide();		
								$('#myModal').modal('hide');
								$('#lbl_tabla').html('CxC generadas');
								$('#ingreso_cxc').find('thead').html(resultado[0]);
								$('#ingreso_cxc').find('tbody').html(resultado[1]);


								$('#total_precio_total').html('$'+resultado[2]);
								$('#total_propiedades').html(resultado[3]);
								$('#div_panel_derecho').html(resultado[4]);
								
								$('#btn_imprimir_lote').show(1000);
								var enlace = $('#btn_imprimir_lote').find('a').attr('href');
								$('#btn_imprimir_lote').find('a').attr('href',enlace+'/'+resultado[5]+'/'+resultado[6]+'/'+resultado[7]+'/'+resultado[8]);
								
								$('#btn_enviar_email_lote').show(1000);
								var enlace2 = $('#btn_enviar_email_lote').find('a').attr('href');
								$('#btn_enviar_email_lote').find('a').attr('href',enlace2+'/'+resultado[5]+'/'+resultado[6]+'/'+resultado[7]+'/'+resultado[8]);
							});
						}						
						
					}else{
						$('#confirmacion').focus();
						alert('Debe confirmar la creación de CxC.');
					}
				}else{
					alert('Faltan campos por llenar.');
				}								
			});

			function validar_requeridos(){
				var control = 1;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = 0;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = 1;
					}
				});
				return control;
			}
		});
	</script>
@endsection