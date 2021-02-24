<style type="text/css">
	.page-break {
	    page-break-after: always;
	}
</style>
<div class="container">
	@for($k=0;$k < count($estudiantes) ;$k++)
		<table class="table table-bordered table-striped" id="tbDatos">
			<thead>
				<tr>
					<th colspan="14">
						<div align="center"> <b> Lista de datos básicos de estudiantes </b> </div>
						<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
						<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
					</th>
				</tr>
				<tr>
					<th>Cod. Matrícula</th>
					<th>Fecha Matrícula</th>
					<th>Nombre completo</th>
					<th>Tipo doc.</th>
					<th>Número doc.</th>
					<th>Curso</th>
					<th>Nombre acudiente</th>
					<th>Tipo doc. acudiente</th>
					<th>Núm. doc. acudiente</th>
					<th>Dir. acudiente</th>
					<th>Email acudiente</th>
					<th>Régimen</th>
					<th>Vlr. Matrícula</th>
					<th>Vlr. Pensión</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $estudiantes[$k]['listado'] as $registro )
					<?php
						$responsable_financiero = $registro->estudiante->responsable_financiero();
						$libretas_pagos = $registro->libretas_pagos;
						$ultima_libreta = $libretas_pagos->last();

						$valor_matricula = 0;
						$valor_pension = 0;
						if ( !is_null( $ultima_libreta ) )
						{
							$valor_matricula = $ultima_libreta->valor_matricula;
							$valor_pension = $ultima_libreta->valor_pension_mensual;
						}
					?>
					<tr>
						<td> {{ $registro->codigo }} </td>
						<td> {{ $registro->fecha_matricula }} </td>
						<td> {{ $registro->nombre_completo }} </td>
						<td> {{ $registro->tipo_documento }} </td>
						<td> {{ number_format( $registro->numero_identificacion, 0,',','.' ) }} </td>
						<td> {{ $registro->curso_descripcion }} </td>
						<td> {{ $responsable_financiero->tercero->descripcion }} </td>
						<td> {{ $responsable_financiero->tercero->tipo_doc_identidad->abreviatura }} </td>
						<td> {{ number_format( $responsable_financiero->tercero->numero_identificacion, 0,',','.' ) }} </td>
						<td> {{ $responsable_financiero->tercero->direccion1 }} </td>
						<td> {{ $responsable_financiero->tercero->email }} </td>
						<td> {{ $responsable_financiero->tercero->tipo }} </td>
						<td> ${{ number_format( $valor_matricula, 0,',','.' ) }} </td>
						<td> ${{ number_format( $valor_pension, 0,',','.' ) }} </td>
					</tr>
				@endforeach
		</table>
		<div class="page-break"></div>
	@endfor
</div>