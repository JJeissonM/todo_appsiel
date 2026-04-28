<style type="text/css">
	.resumen-estudiantes {
		font-family: Arial, Helvetica, sans-serif;
		font-size: {{ $tam_letra }}mm;
		color: #27313a;
		white-space: normal;
	}

	.resumen-estudiantes h2,
	.resumen-estudiantes h3 {
		margin: 0;
	}

	.resumen-estudiantes .titulo {
		background-color: #24c79a;
		color: #fff;
		padding: 12px;
		text-align: center;
	}

	.resumen-estudiantes .subtitulo {
		margin-top: 6px;
		font-size: 0.9em;
		font-weight: normal;
	}

	.resumen-estudiantes .bloque {
		border: 1px solid #d8dee4;
		margin-bottom: 12px;
		padding: 12px;
	}

	.resumen-estudiantes .metricas {
		width: 100%;
		border-collapse: separate;
		border-spacing: 8px;
	}

	.resumen-estudiantes .metrica {
		border: 1px solid #d8dee4;
		padding: 10px;
		width: 25%;
		vertical-align: top;
	}

	.resumen-estudiantes .metrica-label {
		color: #5d6b78;
		font-size: 0.85em;
	}

	.resumen-estudiantes .metrica-valor {
		display: block;
		font-size: 1.9em;
		font-weight: bold;
		margin-top: 4px;
	}

	.resumen-estudiantes .grid {
		width: 100%;
	}

	.resumen-estudiantes .grid td {
		width: 50%;
		vertical-align: top;
		padding: 8px;
	}

	.resumen-estudiantes table.datos {
		width: 100%;
		border-collapse: collapse;
	}

	.resumen-estudiantes table.datos th,
	.resumen-estudiantes table.datos td {
		border: 1px solid #d8dee4;
		padding: 5px;
		text-align: left;
	}

	.resumen-estudiantes table.datos th {
		background-color: #eef3f7;
	}

	.resumen-estudiantes .barra-wrap {
		background-color: #eef3f7;
		height: 10px;
		width: 100%;
	}

	.resumen-estudiantes .barra {
		background-color: #5d4b9b;
		height: 10px;
	}

	.resumen-estudiantes .nota {
		color: #5d6b78;
		font-size: 0.85em;
	}

	.resumen-estudiantes .positivo {
		color: #16845c;
		font-weight: bold;
	}

	.resumen-estudiantes .negativo {
		color: #b42318;
		font-weight: bold;
	}
</style>

@php
	$total = $resumen['total'];
	$antiguedad = $resumen['antiguedad'];
	$comparativo = $resumen['comparativo'];
	$maxCurso = max(1, $resumen['por_curso']->max('cantidad'));
	$maxGrado = max(1, $resumen['por_grado']->max('cantidad'));
	$maxHistorico = $historico->count() > 0 ? max(1, $historico->max('total')) : 1;
@endphp

<div class="resumen-estudiantes">
	@include('banner_colegio')

	<div class="titulo">
		<h2>Resumen de Estudiantes</h2>
		<div class="subtitulo">
			Año lectivo: {{ $periodo_lectivo != null ? $periodo_lectivo->descripcion : '' }}
			&nbsp; | &nbsp; Grado: {{ $resumen['filtros']['grado'] }}
			&nbsp; | &nbsp; Curso: {{ $resumen['filtros']['curso'] }}
		</div>
	</div>

	<table class="metricas">
		<tr>
			<td class="metrica">
				<span class="metrica-label">Estudiantes activos</span>
				<span class="metrica-valor">{{ number_format($total, 0, ',', '.') }}</span>
				<span class="nota">Matriculas activas en el periodo seleccionado.</span>
			</td>
			<td class="metrica">
				<span class="metrica-label">Antiguos</span>
				<span class="metrica-valor">{{ number_format($antiguedad['antiguos'], 0, ',', '.') }}</span>
				<span class="nota">{{ $total > 0 ? round(($antiguedad['antiguos'] / $total) * 100, 1) : 0 }}% del total.</span>
			</td>
			<td class="metrica">
				<span class="metrica-label">Nuevos</span>
				<span class="metrica-valor">{{ number_format($antiguedad['nuevos'], 0, ',', '.') }}</span>
				<span class="nota">{{ $total > 0 ? round(($antiguedad['nuevos'] / $total) * 100, 1) : 0 }}% del total.</span>
			</td>
			<td class="metrica">
				<span class="metrica-label">Variacion vs. año anterior</span>
				@if($comparativo == null)
					<span class="metrica-valor">N/D</span>
					<span class="nota">No hay periodo anterior para comparar.</span>
				@else
					<span class="metrica-valor {{ $comparativo->variacion < 0 ? 'negativo' : 'positivo' }}">
						{{ $comparativo->variacion > 0 ? '+' : '' }}{{ number_format($comparativo->variacion, 0, ',', '.') }}
					</span>
					<span class="nota">
						{{ $comparativo->variacion_porcentaje === null ? 'N/D' : $comparativo->variacion_porcentaje . '%' }}
						frente a {{ $comparativo->periodo }}.
					</span>
				@endif
			</td>
		</tr>
	</table>

	<table class="grid">
		<tr>
			<td>
				<div class="bloque">
					<h3>Distribucion por curso</h3>
					<table class="datos">
						<thead>
							<tr>
								<th>Curso</th>
								<th width="80px">Cantidad</th>
								<th width="90px">%</th>
								<th>Participacion</th>
							</tr>
						</thead>
						<tbody>
							@foreach($resumen['por_curso'] as $fila)
								<tr>
									<td>{{ $fila->label }}</td>
									<td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
									<td>{{ $total > 0 ? round(($fila->cantidad / $total) * 100, 1) : 0 }}%</td>
									<td>
										<div class="barra-wrap">
											<div class="barra" style="width: {{ round(($fila->cantidad / $maxCurso) * 100, 1) }}%;"></div>
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</td>
			<td>
				<div class="bloque">
					<h3>Distribucion por genero</h3>
					<table class="datos">
						<thead>
							<tr>
								<th>Genero</th>
								<th width="80px">Cantidad</th>
								<th width="90px">%</th>
							</tr>
						</thead>
						<tbody>
							@foreach($resumen['por_genero'] as $fila)
								<tr>
									<td>{{ $fila->label }}</td>
									<td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
									<td>{{ $total > 0 ? round(($fila->cantidad / $total) * 100, 1) : 0 }}%</td>
								</tr>
							@endforeach
							<tr>
								<th>TOTAL</th>
								<th>{{ number_format($total, 0, ',', '.') }}</th>
								<th>100%</th>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="bloque">
					<h3>Rangos de edad</h3>
					<table class="datos">
						<thead>
							<tr>
								<th>Rango</th>
								<th width="80px">Cantidad</th>
								<th width="90px">%</th>
							</tr>
						</thead>
						<tbody>
							@foreach($resumen['por_edad'] as $fila)
								<tr>
									<td>{{ $fila->label }}</td>
									<td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
									<td>{{ $total > 0 ? round(($fila->cantidad / $total) * 100, 1) : 0 }}%</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</table>

	<div class="bloque">
		<h3>Distribucion por grado</h3>
		<table class="datos">
			<thead>
				<tr>
					<th>Grado</th>
					<th width="90px">Cantidad</th>
					<th width="90px">%</th>
					<th>Participacion</th>
				</tr>
			</thead>
			<tbody>
				@foreach($resumen['por_grado'] as $fila)
					<tr>
						<td>{{ $fila->label }}</td>
						<td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
						<td>{{ $total > 0 ? round(($fila->cantidad / $total) * 100, 1) : 0 }}%</td>
						<td>
							<div class="barra-wrap">
								<div class="barra" style="width: {{ round(($fila->cantidad / $maxGrado) * 100, 1) }}%;"></div>
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	@if($mostrar_historico == 'Si')
		<div class="bloque">
			<h3>Historico por años lectivos</h3>
			<table class="datos">
				<thead>
					<tr>
						<th>Año lectivo</th>
						<th width="80px">Total</th>
						<th width="80px">Nuevos</th>
						<th width="80px">Antiguos</th>
						<th width="100px">Variacion</th>
						<th width="100px">Variacion %</th>
						<th>Comparativo</th>
					</tr>
				</thead>
				<tbody>
					@foreach($historico as $fila)
						<tr>
							<td>{{ $fila->periodo }}</td>
							<td>{{ number_format($fila->total, 0, ',', '.') }}</td>
							<td>{{ number_format($fila->nuevos, 0, ',', '.') }}</td>
							<td>{{ number_format($fila->antiguos, 0, ',', '.') }}</td>
							<td class="{{ $fila->variacion < 0 ? 'negativo' : 'positivo' }}">
								{{ $fila->variacion === null ? 'N/D' : ($fila->variacion > 0 ? '+' : '') . number_format($fila->variacion, 0, ',', '.') }}
							</td>
							<td>{{ $fila->variacion_porcentaje === null ? 'N/D' : $fila->variacion_porcentaje . '%' }}</td>
							<td>
								<div class="barra-wrap">
									<div class="barra" style="width: {{ round(($fila->total / $maxHistorico) * 100, 1) }}%;"></div>
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	@endif

	<div class="bloque">
		<h3>Lectura gerencial</h3>
		<table class="datos">
			<tbody>
				<tr>
					<th width="28%">Mayor concentracion</th>
					<td>
						@if($resumen['curso_mayor'] != null)
							{{ $resumen['curso_mayor']->label }} concentra {{ number_format($resumen['curso_mayor']->cantidad, 0, ',', '.') }} estudiantes.
						@else
							Sin datos.
						@endif
					</td>
				</tr>
				<tr>
					<th>Balance de permanencia</th>
					<td>
						{{ number_format($antiguedad['antiguos'], 0, ',', '.') }} estudiantes antiguos y
						{{ number_format($antiguedad['nuevos'], 0, ',', '.') }} nuevos.
					</td>
				</tr>
				<tr>
					<th>Seguimiento recomendado</th>
					<td>
						Revisar capacidad operativa en los cursos con mayor concentracion y acompañar la adaptacion de estudiantes nuevos.
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
