<?php 
	use App\Http\Controllers\Ventas\ReportesController;
	$fecha_hoy = date('Y-m-d');
    $tabla = ReportesController::grafica_ventas_diarias( date("Y-m-d",strtotime($fecha_hoy."- 30 days")) , $fecha_hoy );
?>
@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<?php 
				echo Lava::render('BarChart', 'ventas_diarias', 'grafica1');
				$cant = count($tabla);
				$totales = 0;
			?>
			<h3> Ventas de los últimos 30 días <small> <a href="#" data-toggle="tooltip" data-placement="right" title="IVA no incluido!"> <i class="fa fa-info-circle"></i> </a> </small> </h3>
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
							@for($i=0; $i < $cant; $i++)
								<tr>
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

	<br/>
@endsection