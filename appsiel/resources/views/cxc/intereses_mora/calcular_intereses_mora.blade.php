@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			{{ Form::open(['url'=>'cxc_ajax_calcular_intereses','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-md-10">
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_corte', date('Y-m-d'), 'Fecha corte', '', [ 'id' => 'fecha_corte', 'required' => 'required' ] ) }}
					            </div>
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'calculado_sobre', null, 'Calcular sobre', [ 'Última factura vencida' => 'Última factura vencida', 'Saldo total vencido' => 'Saldo total vencido'], [ 'id' => 'calculado_sobre', 'required' => 'required' ] ) }}
					            </div>
					        </div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'cxc_servicio_id', null, 'Concepto de interés', $servicios, [ 'id' => 'cxc_servicio_id', 'required' => 'required' ] ) }}
					            </div>
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'tasa_interes', null, 'Tasa de interés', [ 'id' => 'tasa_interes', 'required' => 'required' ] ) }}
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
			{{ Form::close() }}
			<!-- <button id="btn_ir">ir</button>	-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_corte').focus();

			$('#fecha_corte').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#calculado_sobre').focus();				
				}
			});

			$('#calculado_sobre').change(function(){
				if ($('#cxc_servicio_id').val()!='') {
					$('#cxc_servicio_id').focus();
				}
			});

			$('#cxc_servicio_id').change(function(){
				if ($('#cxc_servicio_id').val()!='') {
					$('#tasa_interes').focus();
				}
			});

			$('#tasa_interes').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#btn_generar').focus();				
				}		
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				
				if(!valida_campos()){
					alert('Debe diligencias todos los campos.');
					return false;
				}
				
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
			});

			function valida_campos(){
				var valida = true;
				if( $('#fecha_corte').val()=='' || $('#cxc_servicio_id').val()==''|| $('#tasa_interes').val()=='' ){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection