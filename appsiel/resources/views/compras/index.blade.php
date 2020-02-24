<?php

use App\Http\Controllers\Calificaciones\ReporteController;
use App\Http\Controllers\Compras\ReportesController;

$fecha_hoy = date('Y-m-d');
$tabla = ReportesController::grafica_compras_diarias(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
$vencidas = ReportesController::ordenes_vencidas();
$futuras = ReportesController::ordenes_futuras();
$semana = ReportesController::ordenes_semana();
?>
@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
{!! $select_crear !!} 
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<h3> Órdenes de compras (órdenes pendientes, actuales, futuras y vencidas)</h3>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<div class="col-md-6">
					<div style="font-size: 20px; font-weight: bold; padding: 5px; text-align: center;" class="alert alert-danger" role="alert">Órdenes vencidas</div>
					@if($vencidas!=null)
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Órden</th>
								<th>Proveedor</th>
								<th>Venció</th>
							</tr>
						</thead>
						<tbody>
							@foreach($vencidas as $v)
							<tr>
								<td><a target="_blank" href="{{url('orden_compra/'.$v['id'].'?id=9&id_modelo=177&id_transaccion=6')}}">{{$v['documento']}}</a></td>
								<td>{{$v['proveedor']}}</td>
								<td>{{$v['fecha_recepcion']}}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@else
					<p>No hay órdenes pendientes vencidas</p>
					@endif
				</div>
				<div class="col-md-6">
					<div style="font-size: 20px; font-weight: bold; padding: 5px; text-align: center;" class="alert alert-success" role="alert">Órdenes Futuras</div>
					@if($futuras!=null)
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Órden</th>
								<th>Proveedor</th>
								<th>Vence</th>
							</tr>
						</thead>
						<tbody>
							@foreach($futuras as $v)
							<tr>
								<td><a target="_blank" href="{{url('orden_compra/'.$v['id'].'?id=9&id_modelo=177&id_transaccion=6')}}">{{$v['documento']}}</a></td>
								<td>{{$v['proveedor']}}</td>
								<td>{{$v['fecha_recepcion']}}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@else
					<p>No hay órdenes pendientes futuras</p>
					@endif
				</div>
				<div class="col-md-12">
					<div style="font-size: 20px; font-weight: bold; padding: 5px; text-align: center;" class="alert alert-warning" role="alert">Pendientes ésta semana</div>
					<div class="table-responsive">
						@if($semana!=null)
						<table class="table table-striped table-responsive">
							<thead>
								<tr>
									<th>LUNES</th>
									<th>MARTES</th>
									<th>MIERCOLES</th>
									<th>JUEVES</th>
									<th>VIERNES</th>
									<th>SABADO</th>
									<th>DOMINGO</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									@foreach($semana as $s)
									<td>
										<div class="alert alert-success" style="padding: 5px" role="alert">
											{{$s['fecha']}}
										</div>
										@if($s['data']!=null)
										<ol>
											@foreach($s['data'] as $d)
											<li>
												<a target="_blank" href="{{url('orden_compra/'.$d['id'].'?id=9&id_modelo=177&id_transaccion=6')}}">{{$d['documento']}}</a>
												- {{$d['proveedor']." - ".$d['fecha_recepcion']}}
											</li>
											@endforeach
										</ol>
										@else
										<p>---</p>
										@endif
									</td>
									@endforeach
								</tr>
							</tbody>
						</table>
						@else
						<p>No hay órdenes pendientes esta semana</p>
						@endif
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="container-fluid">
	<div class="marco_formulario">

		<?php
		echo Lava::render('BarChart', 'compras_diarias', 'grafica1');
		$cant = count($tabla);
		$totales = 0;
		?>
		<h3> Compras de los últimos 30 días <small> <a href="#" data-toggle="tooltip" data-placement="right" title="IVA no incluido!"> <i class="fa fa-info-circle"></i> </a> </small> </h3>
		<hr>
		<div id="grafica1"></div>

		<br><br>
		<div class="row">
			<div class="col-md-4 col-md-offset-4">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Total</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@for($i=0; $i < $cant; $i++) <tr>
							<td> {{ $tabla[$i]['fecha'] }} </td>
							<td style="text-align: right;"> ${{ number_format($tabla[$i]['valor'], 2, ',', '.') }} </td>
							<td> </td>
							</tr>
							@php
							$totales += $tabla[$i]['valor'];
							@endphp
							@endfor
					</tbody>
					<tfoot>
						<tr>
							<td> </td>
							<td style="text-align: right;"> <b> ${{ number_format($totales, 2, ',', '.') }} </b> </td>
							<td> </td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

	</div>
</div>

<br />
@endsection