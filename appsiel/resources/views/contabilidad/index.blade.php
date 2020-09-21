<?php

	use App\Http\Controllers\Contabilidad\ContabReportesController;

	$fecha_hoy = date('Y-m-d');

	if ( !is_null( Input::get('fecha_corte') ) )
	{
		$fecha_hoy = Input::get('fecha_corte');
	}

	$riqueza_neta = ContabReportesController::grafica_riqueza_neta( $fecha_hoy );
	$class_riqueza = 'success';
	
	if ($riqueza_neta->patrimonio < 0 )
	{
		$class_riqueza = 'danger';
	}


	$flujo_efectivo_neto = ContabReportesController::grafica_flujo_efectivo_neto( $fecha_hoy );
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