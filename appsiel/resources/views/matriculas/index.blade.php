@extends('layouts.principal')

@section('content')
<style>
	li.botonConfig {
		border-top: 1px solid gray;
		border-left: 1px solid gray;
		border-right: 2px solid gray;
		border-bottom: 2px solid gray;
		margin-left: 50px;
		width: 220px;
		height: 100px;
		text-align: center;  
		-moz-text-align-last: center; /* Code for Firefox */
		text-align-last: center;
		list-style-type: none;
	}

	.chart-matriculas {
		min-height: 260px;
		width: 100%;
	}
</style>

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

<div class="row">
	<div class="col col-sm-8 col-sm-offset-2">
		<div class="panel panel-success">
			<div class="panel-heading" align="center">
				<b>Estadísticas {{ $periodo_lectivo->descripcion }}</b>
			</div>
			
			<div class="panel-body collapse in" id="demo2">
				<div class="row">
					<div  class="col-sm-6">
						<b>Cantidad de estudiantes por antiguedad</b>
						<div id="stocks-chart3" class="chart-matriculas"></div>
						<table>
							@php $total=0 @endphp
							@foreach($nuevos_matriculados as $fila)
								<tr>
									<td width="100px">{{ $fila[0] }}: </td><td>{{ $fila[1] }}</td>
								</tr>
								@php $total += $fila[1] @endphp
							@endforeach
							<tr><td width="100px">TOTAL</td><td>{{ $total }} Estudiantes</td></tr>
						</table>
					</div>
					<div  class="col-sm-6">
						&nbsp;
					</div>
				</div>
				<hr>
				<div class="row">
					<div  class="col-sm-6">
						<b>Cantidad de estudiantes por curso</b>
						<div id="stocks-chart" class="chart-matriculas"></div>
						<table>
							@php $total=0 @endphp
						@foreach($alumnos_por_curso as $curso)
							<tr>
							@if($curso->curso=="")
								<td width="100px">Indefinido: </td><td>{{ $curso->Cantidad }}</td>
							@else
								<td width="100px">{{ $curso->curso }}: </td><td>{{ $curso->Cantidad }}</td>
							@endif
							</tr>
							@php $total = $total + $curso->Cantidad @endphp
						@endforeach
							<tr><td width="100px">TOTAL</td><td>{{ $total }} Estudiantes</td></tr>
						</table>
					</div>
					<div  class="col-sm-6">
						<b>Cantidad por géneros</b>
						<div id="stocks-chart2" class="chart-matriculas"></div>
						<table>
							@php $total=0 @endphp
						@foreach($generos as $genero)
							<tr>
							@if($genero->Genero=="")
								<td width="100px">Indefinido: </td><td>{{ $genero->Cantidad }}</td>
							@else
								<td width="100px">{{ $genero->Genero }}: </td><td>{{ $genero->Cantidad }}</td>
							@endif
							</tr>
							@php $total = $total + $genero->Cantidad @endphp
						@endforeach
							<tr><td width="100px">TOTAL</td><td>{{ $total }} Estudiantes</td></tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
	<?php
		$datos_antiguedad = [['ESTUDIANTES', 'CANTIDAD']];
		foreach ($nuevos_matriculados as $fila) {
			$datos_antiguedad[] = [$fila[0] == '' ? 'Indefinido' : $fila[0], (int) $fila[1]];
		}

		$datos_cursos = [['Curso', 'Cantidad']];
		foreach ($alumnos_por_curso as $curso) {
			$datos_cursos[] = [$curso->curso == '' ? 'Indefinido' : $curso->curso, (int) $curso->Cantidad];
		}

		$datos_generos = [['Genero', 'Cantidad']];
		foreach ($generos as $genero) {
			$datos_generos[] = [$genero->Genero == '' ? 'Indefinido' : $genero->Genero, (int) $genero->Cantidad];
		}
	?>

	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<script>
		(function () {
			var datosAntiguedad = {!! json_encode($datos_antiguedad) !!};
			var datosCursos = {!! json_encode($datos_cursos) !!};
			var datosGeneros = {!! json_encode($datos_generos) !!};

			google.charts.load('current', { packages: ['corechart'] });
			google.charts.setOnLoadCallback(dibujarGraficasMatriculas);

			function dibujarGraficasMatriculas() {
				dibujarBarras('stocks-chart3', datosAntiguedad);
				dibujarBarras('stocks-chart', datosCursos);
				dibujarTorta('stocks-chart2', datosGeneros);
			}

			function dibujarBarras(elementId, datos) {
				var elemento = document.getElementById(elementId);

				if (!elemento || datos.length < 2) {
					return;
				}

				var dataTable = google.visualization.arrayToDataTable(datos);
				var chart = new google.visualization.BarChart(elemento);

				chart.draw(dataTable, {
					height: 260,
					legend: { position: 'none' },
					chartArea: { left: 95, top: 20, width: '70%', height: '75%' }
				});
			}

			function dibujarTorta(elementId, datos) {
				var elemento = document.getElementById(elementId);

				if (!elemento || datos.length < 2) {
					return;
				}

				var dataTable = google.visualization.arrayToDataTable(datos);
				var chart = new google.visualization.PieChart(elemento);

				chart.draw(dataTable, {
					height: 260,
					chartArea: { left: 20, top: 20, width: '90%', height: '80%' }
				});
			}

			$(window).on('resize', dibujarGraficasMatriculas);
		})();
	</script>
@endsection
