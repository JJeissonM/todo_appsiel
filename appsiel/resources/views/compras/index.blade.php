<?php

	use App\Http\Controllers\Calificaciones\ReporteController;
	use App\Http\Controllers\Compras\ReportesController;

	$fecha_hoy = date('Y-m-d');
	$tabla = ReportesController::grafica_compras_diarias(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
	$vencidas = ReportesController::ordenes_vencidas();
	$futuras = ReportesController::ordenes_futuras();
	$semana = ReportesController::ordenes_semana();


	$entradas = ReportesController::entradas_pendientes_por_facturar();

?>
@section('estilos_2')
<style>
		thead>tr>th{
			text-align: center;
		}

		thead > tr{
			background-color: unset;
		}

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

	@if( !empty( $entradas->toArray() ) )
		<div class="marco_formulario">

				<div class="row">
					<div class="col-md-12">
						<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ddd; color: #636363;">Entradas pendientes por facturar</h4>
						<table class="table table-bordered table-responsive">
							<thead>
								<tr>
									<th>Fecha</th>
									<th>Doc.</th>
									<th>Proveedor</th>
								</tr>
							</thead>
							<tbody>
								@foreach($entradas as $entrada )
									<tr>
					                    <td>{{ $entrada->fecha }}</td>
					                    <td>{!! $entrada->enlace_show_documento() !!}</td>
					                    <td>{{ number_format( $entrada->tercero->numero_identificacion, 0, ',', '.' ) }} / {{ $entrada->tercero->descripcion }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>

					</div>
				</div>

		</div>
	@endif

	<div class="marco_formulario">

		<div class="row">
			<div class="col-md-12">
				<div class="card"  style="border: 2px solid #ffcd39">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ffcd39; color: #636363;">Órdenes de Compras ésta Semana</h4>
				<div class="table-responsive">
					@if($semana!=null)
					<table class="table table-bordered table-responsive">
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
									<?php
										$hoy = getdate();
										$fechah = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
										$date2 = strtotime($fechah);
										$ref = date('d-m-Y', $date2);
									?>
									@if($s['fecha'] != $ref)
									<h5 style="text-align: center; width: 100%; background-color: #ddd; color: #636363;">{{$s['fecha']}}</h5>
									@else
									<h5 style="text-align: center; width: 100%; background-color: #ffcd39; color: #636363;">{{$s['fecha']}}</h5>
									@endif
									@if($s['data']!=null)
									<ol>
										@foreach($s['data'] as $d)
										<li>
											<a style="color: #0b97c4;" target="_blank" href="{{url('orden_compra/'.$d['id'].'?id=9&id_modelo=177&id_transaccion=6')}}" title="{{ $d['proveedor'] }}">{{$d['documento']}}
													/ {{ substr( $d['proveedor'], 0, 10) }}... </a>
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
				<div class="card"  style="border: 2px solid #dc3545;">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #dc3545; color: #636363;">Órdenes de Compras Vencidas</h4>
					@if($vencidas!=null)
					<table class="table table-striped table-bordered">
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
								<td>{{ substr($v['proveedor'],0,50)}}...</td>
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
				<div class="card"  style="border: 2px solid #20c997">
					<h4 class="card-header" style="text-align: center; width: 100%; background-color: #20c997; color: #636363;">Órdenes de Compras Futuras</h4>
					@if($futuras!=null)
					<table class="table table-striped table-bordered">
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
								<td>{{ substr($v['proveedor'],0,50)}}...</td>
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
			<div class="col-md-3 col-md-offset-4">
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						@for($i=0; $i < $cant; $i++) 
						<tr>
							<td> {{ $tabla[$i]['fecha'] }} </td>
							<td style="text-align: right;"> ${{ number_format($tabla[$i]['valor'], 2, ',', '.') }} </td>
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
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

	</div>
</div>

<br />
@endsection