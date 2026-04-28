@extends('layouts.principal')

@section('content')
	<style>
		.chart-inventarios {
			min-height: 600px;
			width: 100%;
		}
	</style>

	{{ Form::bsMigaPan($miga_pan) }}
	<!-- { !! $select_crear !!} -->
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<h4>Existencias por bodega</h4>
		    <hr>
		    <br/>
			@for ($i = 0; $i < $cantidad_graficas; $i++)
				<div class="row">
					<div class="col-md-12" style="border: 1px solid;">
						<b>
							{{ $titulos[$i]['bodega_nombre'] }}
							<a href="{{ url('inv_consultar_existencias/'.$titulos[$i]['bodega_id'].'?id='.Input::get('id')).'&fecha_corte='.date('Y-m-d') }}" title="Consultar existencias">
								<i class="fa fa-search"></i>
							</a>
						</b>
						<div id="div_chart_{{ $i }}" class="chart-inventarios"></div>
					</div>
				</div>
				<br/>
			@endfor
		</div>
	</div>

	<br/>
@endsection

@section('scripts')
	<?php
		$datos_graficas = [];

		for ($i = 0; $i < $cantidad_graficas; $i++) {
			$datos_graficas[$i] = [['Producto', 'Cantidad']];

			foreach ($titulos[$i]['registros'] as $registro) {
				$datos_graficas[$i][] = [
					$registro['Producto'] == '' ? 'Indefinido' : $registro['Producto'],
					(float) round($registro['Cantidad'], 2)
				];
			}
		}
	?>

	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<script>
		(function () {
			var datosGraficas = {!! json_encode($datos_graficas) !!};

			google.charts.load('current', { packages: ['corechart'] });
			google.charts.setOnLoadCallback(dibujarGraficasInventarios);

			function dibujarGraficasInventarios() {
				for (var i = 0; i < datosGraficas.length; i++) {
					dibujarBarras('div_chart_' + i, datosGraficas[i]);
				}
			}

			function dibujarBarras(elementId, datos) {
				var elemento = document.getElementById(elementId);

				if (!elemento || datos.length < 2) {
					return;
				}

				var dataTable = google.visualization.arrayToDataTable(datos);
				var chart = new google.visualization.BarChart(elemento);

				chart.draw(dataTable, {
					height: 600,
					legend: { position: 'none' },
					chartArea: { left: 220, top: 20, width: '70%', height: '85%' },
					vAxis: { gridlines: { count: 30 } }
				});
			}

			$(window).on('resize', dibujarGraficasInventarios);
		})();
	</script>
@endsection
