<?php

use App\Http\Controllers\Calificaciones\ReporteController;
use App\Http\Controllers\Compras\ReportesController;

$fecha_hoy = date('Y-m-d');
$tabla = ReportesController::grafica_compras_diarias(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
$vencidas = ReportesController::ordenes_vencidas();
$futuras = ReportesController::ordenes_futuras();
$semana = ReportesController::ordenes_semana();
?>
@section('estilos_2')
<style>
	.card{
		border-radius: 12px 12px 0 0;
		border: 2px solid #ddd;
		margin-bottom: 20px;
		box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
	}

	.card-header{
		text-align: center;
		font-size: 24px;
		padding-top: .8rem;
		padding-bottom: .8rem;
		color: #333 !important;
		border-radius: 10px 10px 0 0;
		margin-bottom: 0;
		margin-top: 0;
	}
</style>

@endsection
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
				<div class="card"  style="border: 2px solid #ffc107">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ffc107; color: #636363;">Pendientes ésta semana</h4>
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
											<a style="color: #0b97c4;" target="_blank" href="{{url('orden_compra/'.$d['id'].'?id=9&id_modelo=177&id_transaccion=6')}}">{{$d['documento']}}</a>
													/ {{ $d['fecha'] }} > <span title="{{ $d['proveedor'] }}"> {{ substr( $d['proveedor'], 0, 10) }}... </span>
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

		<div class="row">
			<div class="col-md-6">
				<div class="card"  style="border: 2px solid #e35d6a;">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #e35d6a; color: #636363;">Órdenes vencidos</h4>
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
				
			</div>
			<div class="col-md-6">
				<div class="card"  style="border: 2px solid #479f76">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #479f76; color: #636363;">Órdenes Futuras</h4>
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