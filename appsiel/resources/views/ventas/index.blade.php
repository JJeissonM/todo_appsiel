<?php

	use App\Http\Controllers\Ventas\ReportesController;

	$fecha_hoy = date('Y-m-d');
	$tabla = ReportesController::grafica_ventas_diarias(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
	$vencidas = ReportesController::pedidos_vencidos();
	$futuras = ReportesController::pedidos_futuros();
	$anulados = ReportesController::pedidos_anulados();
	$pedidos_de_la_semana = ReportesController::pedidos_semana();

	$vendedor = App\Ventas\Vendedor::where( 'user_id', Auth::user()->id )->get()->first();

	$vendedor_id = 0;
	if ( !is_null( $vendedor ) )
	{
		$vendedor_id = $vendedor->id;
	}


	$remisiones = ReportesController::remisiones_pendientes_por_facturar();
	$facturas = ReportesController::facturas_electronicas_pendientes_por_enviar();

	$user = \Illuminate\Support\Facades\Auth::user();
?>


@extends('layouts.principal')

@section('estilos_2')
	<style>

		div.boton {
		  border: 1px solid #ddd;
		  border-radius: 4px;
		  /*background: linear-gradient(90deg, rgba(110,41,183,1) 0%, rgba(79,138,232,1) 44%, rgba(13,214,159,1) 100%);
		  background-color: #ddd;*/
		  text-align: center;
		  margin: 20px 20px;
		}
		
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

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	@can('vtas_bloquear_vista_index')
		
	@else
	
		@if( !$user->hasRole('SupervisorCajas') )
			<!-- { !! $select_crear !!} -->
		@endif

	@endcan
	<hr>

	@include('layouts.mensajes')

	<input type="hidden" name="user_rol" id="user_rol" value="{{ $user->roles()->first()->name }}">

	@can('vtas_bloquear_vista_index')
		@include('ventas.index_vendedor')
	@else
		<div class="container-fluid">
			
			@if( !$user->hasRole('SupervisorCajas') )
				<div class="marco_formulario">
					<div class="row">
						<div class="col-md-6">
							@include('ventas.incluir.lista_remisiones',['titulo'=>'Remisiones pendientes por facturar'])
						</div>
						<div class="col-md-6">
							@include('ventas.incluir.lista_facturas_electronicas',['titulo'=>'Fact. Electrónicas pendientes por enviar'])
						</div>
					</div>
				</div>
			@endif

			<div class="marco_formulario">
				<div class="row">					
					<div class="col-md-12">						
						<div class="card" style="border: 2px solid #ffcd39;">
							<h4 class="card-header" style="text-align: center; width: 100%; background-color: #ffcd39; color: #636363;">Pedidos por Entregar</h4>
							@if($pedidos_de_la_semana!=null)
							<div style=" overflow-x: scroll">
							<table class="table table-bordered table-responsive">
								<thead>
									<tr style="background-color: #eee">
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
										@foreach($pedidos_de_la_semana as $s)
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
														@if($d['estado'] == "Cumplido")
															@php continue; @endphp
														@endif 
													<li>
														<a style="color: #0b97c4;" target="_blank" href="{{url('vtas_pedidos/'.$d['id'].'?id=13&id_modelo=175&id_transaccion=42')}}" title="{{ $d['cliente'] }}">
															{{$d['documento']}}	/ {{ substr( $d['cliente'], 0, 10) }}.
														</a>
													</li>
													@endforeach
												</ol>
											@endif
										</td>
										@endforeach
									</tr>
								</tbody>
							</table>	
							</div>
							
							@else
							<p>No hay pedidos pendientes esta semana</p>
							@endif
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-4">
						<div class="card" style="border: 2px solid #dc3545">
							<h4 class="card-header" style="text-align: center; width: 100%; background-color: #dc3545; color: #636363;">Pedidos Vencidos</h4>
							@if($vencidas!=null)
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Pedido</th>
										<th>Cliente</th>
										<th>F. Entrega</th>
									</tr>
								</thead>
								<tbody>
									@foreach($vencidas as $v)
									<tr>
										<td><a target="_blank" href="{{url('vtas_pedidos/'.$v['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$v['documento']}}</a></td>
										<td>{{ substr( $v['cliente'], 0, 25) }}...</td>
										<td>{{$v['fecha_entrega']}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
							@else
							<p>No hay pedidos pendientes vencidos</p>
							@endif	
						</div>
						
					</div>
					<div class="col-md-4">
						<div class="card" style="border: 2px solid #20c997">
							<h4 class="card-header" style="text-align: center; width: 100%; background-color: #20c997; color: #636363;">Pedidos Futuros</h4>
							<div class="card-body">
								@if($futuras!=null)
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Pedido</th>
											<th>Cliente</th>
											<th>F. Entrega</th>
										</tr>
									</thead>
									<tbody>
										@foreach($futuras as $v)
										<tr>
											<td><a target="_blank" href="{{url('vtas_pedidos/'.$v['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$v['documento']}}</a></td>
											<td>{{ substr( $v['cliente'], 0, 25) }}...</td>
											<td>{{$v['fecha_entrega']}}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
								@else
								<p>No hay pedidos pendientes futuros</p>
								@endif	
							</div>						
						</div>						
					</div>
					<div class="col-md-4">
						<div class="card" style="border: 2px solid #adb5bd">
							<h4 class="card-header" style="text-align: center; width: 100%; background-color: #adb5bd; color: #636363;">Pedidos Anulados</h4>

							@if($anulados!=null)
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Pedido</th>
										<th>Cliente</th>
										<th>F. Entrega</th>
									</tr>
								</thead>
								<tbody>
									@foreach($anulados as $a)
									<tr>
										<td><a target="_blank" href="{{url('vtas_pedidos/'.$a['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$a['documento']}}</a></td>
										<td>{{ substr( $a['cliente'], 0, 25) }}...</td>
										<td>{{$a['fecha_entrega']}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
							@else
							<p>No hay pedidos pendientes futuros</p>
							@endif	
						</div>
						
					</div>
				</div>

			</div>
		</div>

		@if( !$user->hasRole('SupervisorCajas') )
			<div class="container-fluid">
				<div class="marco_formulario">
					<?php
					echo Lava::render('BarChart', 'ventas_diarias', 'grafica1');
					$cant = count($tabla);
					$totales = 0;
					?>
					<h3> Ventas de los últimos 30 días <small> <i class="fa fa-info-circle" title="IVA incluido!"></i> </small> </h3>
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
									@for($i=0; $i < $cant; $i++) <tr>
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
		@endif

		<br />
	@endcan	
@endsection

@section('scripts')
	<script type="text/javascript">
			
		if( $('#user_rol').val() == 'SupervisorCajas')
		{
			$('.nav').attr('display','none');
		}
	</script>
@endsection