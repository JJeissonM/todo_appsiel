<?php

	use App\Http\Controllers\Contabilidad\ContabReportesController;

	// Hoy
	$fecha_hasta = date('Y-m-d');
	if ( !is_null( Input::get('fecha_hasta') ) )
	{
		$fecha_hasta = Input::get('fecha_hasta');
	}

	// Tres meses atrás
	$fecha_desde = date("Y-m-01", strtotime($fecha_hasta."- 3 month") );
	if ( !is_null( Input::get('fecha_desde') ) )
	{
		$fecha_desde = Input::get('fecha_desde');
	}

	$riqueza_neta = ContabReportesController::grafica_riqueza_neta( date('1900-01-01'), $fecha_hasta );
	$class_riqueza = 'success';
	
	if ($riqueza_neta->patrimonio < 0 )
	{
		$class_riqueza = 'danger';
	}


	$flujo_efectivo_neto = ContabReportesController::grafica_flujo_efectivo_neto( $fecha_desde, $fecha_hasta );
	$class_flujo_efectivo = 'success';
	
	if ($flujo_efectivo_neto->resultado > 0 )
	{
		$class_flujo_efectivo = 'danger';
	}

?>

@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<h4>
				Mapa de Situación Financiera
				<hr>
			</h4>
			<div class="row container-fluid">
				<div class="col-sm-6">

					<?php 
						echo Lava::render('PieChart', 'Riqueza', 'riqueza-neta-chart');
					?>
					<div id="riqueza-neta-chart"></div>
					
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-8">

								<div class="form-group">
									<label class="control-label col-sm-3" for="fecha_corte">Corte:</label>
									<div class="col-sm-9">
										{{ Form::date('fecha_corte', $fecha_hasta, ['id'=>'fecha_corte']) }}
									</div>
								</div>

							</div>
							<div class="col-md-4">
								<a href="#" class="btn btn-primary btn-xs btn_actualizar"> Actualizar </a>
							</div>
						</div>
					</div>

					<div class="alert alert-{{$class_riqueza}}" style="font-size: 13px; color: black;">
					  {{ Form::TextoMoneda( $riqueza_neta->activos, '(+) Activos:'  ) }}
					  <br>
					  {{ Form::TextoMoneda( $riqueza_neta->pasivos, '(-) Pasivos:'  ) }}
					  <br>
					  <hr>
					  {{ Form::TextoMoneda( $riqueza_neta->patrimonio, '(=) Patrimonio:'  ) }}
					</div>
				</div>

				<div  class="col-sm-6">
					<?php 
						echo Lava::render('PieChart', 'FlujoNeto', 'flujo-neto-chart');
					?>
					<div id="flujo-neto-chart"></div>
					
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-4">

								<div class="form-group">
									<label class="control-label col-sm-3" for="fecha_desde">Desde:</label>
									<div class="col-sm-9">
										{{ Form::date('fecha_desde', $fecha_desde, ['id'=>'fecha_desde']) }}
									</div>
								</div>
								
							</div>
							<div class="col-md-4">

								<div class="form-group">
									<label class="control-label col-sm-3" for="fecha_hasta">Hasta:</label>
									<div class="col-sm-9">
										{{ Form::date('fecha_hasta', $fecha_hasta, ['id'=>'fecha_hasta']) }}
									</div>
								</div>
								
							</div>
							<div class="col-md-4">
								&nbsp;&nbsp;&nbsp;<a href="#" class="btn btn-primary btn-xs btn_actualizar"> Actualizar </a>
							</div>
						</div>
					</div>

					<div class="alert alert-{{$class_flujo_efectivo}}" style="font-size: 13px; color: black;">
					  {{ Form::TextoMoneda( $flujo_efectivo_neto->ingresos * -1, '(+) Ingresos:'  ) }}
					  <br>
					  {{ Form::TextoMoneda( $flujo_efectivo_neto->costos_y_gastos * -1, '(-) Costos y Gastos:'  ) }}
					  <br>
					  <hr>
					  {{ Form::TextoMoneda( $flujo_efectivo_neto->resultado * -1, '(=) Flujo de Efectivo Neto:'  ) }}
					</div>
				</div>
			</div>
		</div>
	</div>

	<br/>
@endsection



@section('scripts')
	<script type="text/javascript">

		var cambio_fecha_desde = 0;

		var cambio_fecha_hasta = 0;

		$(document).ready(function(){

			$('#fecha_corte').change(function(event){
				$('#fecha_hasta').val( $('#fecha_corte').val() );
				cambiar_enlace_boton_actualizar();
			});

			$('#fecha_desde').change(function(event){
				cambiar_enlace_boton_actualizar();
			});

			$('#fecha_hasta').change(function(event){
				$('#fecha_corte').val( $('#fecha_hasta').val() );				
				cambiar_enlace_boton_actualizar();
			});

			function cambiar_enlace_boton_actualizar()
			{
				var id = getParameterByName('id');

				$('.btn_actualizar').attr( 'href', "{{ url('contabilidad')}}" + "?id=" + id + "&fecha_desde=" + $('#fecha_desde').val() + "&fecha_hasta=" + $('#fecha_hasta').val() );				
			}

			function getParameterByName( name )
			{
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
		});

		
	</script>
@endsection