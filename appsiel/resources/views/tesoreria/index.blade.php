<?php

use App\Http\Controllers\Tesoreria\ReporteController;

$fecha_hoy = date('Y-m-d');
$tabla = ReporteController::grafica_movimientos_diarios(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
//$cuentas = ReporteController::reporte_cuentas();
//$cajas = ReporteController::reporte_cajas();
?>
@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<h3> Saldos en Cuentas Bancarias y Cajas hasta la fecha ({{$fecha_hoy}})</h3>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped">
					<thead>
						
					</thead>
					<tbody>
						
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>

<div class="container-fluid">
	<div class="marco_formulario">

		<?php
		echo Lava::render('BarChart', 'movimiento_tesoreria', 'grafica1');
		$cant = count($tabla);
		$totales_entradas = 0;
		$totales_salidas = 0;
		?>
		<h3> Movimiento de tesorería de los últimos 30 días </h3>
		<hr>
		<div id="grafica1"></div>

		<br><br>
		<div class="row">
			<div class="col-md-6 col-md-offset-2">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Recaudos</th>
							<th>Pagos</th>
							<th>Saldo</th>
						</tr>
					</thead>
					<tbody>
						@for($i=0; $i < $cant; $i++) <tr>
							<td> {{ $tabla[$i]['fecha'] }} </td>
							<td style="text-align: right;"> ${{ number_format($tabla[$i]['valor_entradas'], 2, ',', '.') }} </td>
							<td style="text-align: right;"> ${{ number_format($tabla[$i]['valor_salidas'], 2, ',', '.') }} </td>
							<td style="text-align: right;"> ${{ number_format( $tabla[$i]['valor_entradas'] - $tabla[$i]['valor_salidas'], 2, ',', '.') }} </td>
							</tr>
							@php
							$totales_entradas += $tabla[$i]['valor_entradas'];
							$totales_salidas += $tabla[$i]['valor_salidas'];
							@endphp
							@endfor
					</tbody>
					<tfoot>
						<tr>
							<td> </td>
							<td style="text-align: right;"> <b> ${{ number_format($totales_entradas, 2, ',', '.') }} </b> </td>
							<td style="text-align: right;"> <b> ${{ number_format($totales_salidas, 2, ',', '.') }} </b> </td>
							<td style="text-align: right;"> <b> ${{ number_format( $totales_entradas - $totales_salidas, 2, ',', '.') }} </b> </td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

	</div>
</div>

<br />
@endsection