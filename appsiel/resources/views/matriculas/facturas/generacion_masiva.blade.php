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
		    <h4>Generación de facturas de ventas</h4>
		    <hr>
			{{ Form::open(['url'=>'facturacion_masiva_estudiantes','id'=>'form_generar_consulta_preliminar_cxc']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::bsSelect( 'concepto_id', null, 'Concepto a facturar', [ '' => '', config('matriculas.inv_producto_id_default_matricula') => 'Matrícula', config('matriculas.inv_producto_id_default_pension') => 'Pensión' ], [] ) }}

				<br><br>

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">
				
			{{ Form::close() }}
			<div class="row">
				<div class="col-sm-offset-4 col-sm-6">
					<button class="btn btn-success btn-md" id="btn_generar_consulta_preliminar_cxc">
						<i class="fa fa-btn fa-forward"></i> Generar consulta
					</button>
				</div>
			</div>

			<ul class="nav nav-tabs">
			  <li class="active"><a href="#"><h3>Facturas de Ventas</h4></a></li>
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
					            <span class="fa fa-list"></span> Total registros <span class="badge" id="total_cantidad_registros"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="fa fa-users"></span> Total estudiantes <span class="badge" id="total_cantidad_estudiantes"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="fa fa-usd"></span> Valor total <span class="badge" id="total_precio_total"></span>
					        </a>
					    </div>
			  		</div>
			  		<div class="col-sm-6">
			  			<div id="div_panel_derecho">
			  				<div class="alert alert-warning">
                                      <strong>¡Advertencia!</strong> Este proceso no se puede reversar.
                                    </div>
				  			<input type="checkbox" name="confirmacion" id="confirmacion"> Confirmar la creación de facturas de ventas
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
			<div class="table-responsive">
			    <table class="table table-striped table-bordered" id="tablas_registros">
			        <thead>
			        </thead>
			        <tbody>
			        </tbody>
			    </table>
			</div>		
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

				if ( !validar_requeridos() )
				{
					return false;
				}

				$('#myModal').modal({keyboard: false, backdrop: "static"});
				var form = $('#form_generar_consulta_preliminar_cxc');
				var url = form.attr('action').replace("facturacion_masiva_estudiantes", "facturacion_masiva_estudiantes/generar_consulta_preliminar");

				data = form.serialize();
				$.post(url,data,function( resultado ){					
					$('#myModal').modal('hide');
					$('#lbl_tabla').html('Detalles <br> <span class="small" style="color: red;"> Nota: A las filas de color rojo no se les creará factura </span>');
					$('#tablas_registros').find('thead').html( resultado.thead );
					$('#tablas_registros').find('tbody').html( resultado.tbody );
					$('#div_alerta').show();
					$('#total_precio_total').html( '$' + resultado.precio_total );
					$('#total_cantidad_registros').html( resultado.cantidad_registros );
					$('#total_cantidad_estudiantes').html( resultado.cantidad_estudiantes );
				});
			});

			$('#btn_guardar_cxc').click(function(e){
				e.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				if ($('#confirmacion').is(":checked"))
				{
					
					if(!confirm("¿Realmente desea generar todas las facturas consultadas?")){
						return false;
					}else{
						$('#myModal').modal({keyboard: false, backdrop: "static"});

						// Se transfoma la tabla a formato JSON a través de un plugin JQuery
						var table = $('#tablas_registros').tableToJSON();

						// Se asigna el objeto JSON a un campo oculto del formulario
				 		$('#lineas_registros').val(JSON.stringify(table));

						var form = $('#form_generar_consulta_preliminar_cxc');
						var url = form.attr('action');
						data = form.serialize();

						$('#tablas_registros').find('thead').html( '' );
						$('#tablas_registros').find('tbody').html( '' );

						$.post(url,data,function(resultado){
							$('#btn_generar_consulta_preliminar_cxc').hide();		
							$('#myModal').modal('hide');
							$('#lbl_tabla').html('Facturas generadas');
							$('#tablas_registros').find('thead').html( resultado.thead );
							$('#tablas_registros').find('tbody').html( resultado.tbody );


							$('#total_precio_total').html( '$' + resultado.precio_total );
							$('#total_cantidad_registros').html( resultado.cantidad_facturas );
							$('#total_cantidad_estudiantes').html( resultado.cantidad_estudiantes );

							$('#div_panel_derecho').html( resultado.mensaje );
							
							$('#btn_imprimir_lote').show(1000);
							var enlace = $('#btn_imprimir_lote').find('a').attr('href');
							$('#btn_imprimir_lote').find('a').attr( 'href', enlace );
							
							$('#btn_enviar_email_lote').show(1000);
							var enlace2 = $('#btn_enviar_email_lote').find('a').attr('href');
							$('#btn_enviar_email_lote').find('a').attr( 'href', enlace2 );
						});
					}						
					
				}else{
					$('#confirmacion').focus();
					alert('Debe confirmar la creación de todas las facturas.');
				}
			});

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				fila.remove();
				$('#btn_nuevo').show();
				calcular_totales();
			});

			function calcular_totales()
			{
				var sum = 0.0;
				$('.valor').each(function() {
				    sum += parseFloat( $(this).text() );
				});

				$('#total_facturas').text( "$" + sum.toFixed(2) );
				$('#total_precio_total').html( "$" + sum.toFixed(2) );
				$('#total_cantidad_registros').html( '-' );
				$('#total_cantidad_estudiantes').html( '-' );
			}

		});
	</script>
@endsection