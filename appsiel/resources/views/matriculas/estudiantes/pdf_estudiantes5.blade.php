<style type="text/css">
	.page-break {
	    page-break-after: always;
	}
</style>

<table class="table table-striped">
	<tr>
		<td style="border: solid 1px;">
			<?php  
				$unwanted_array = array('À'=>'A', 'Á'=>'A', 'È'=>'E', 'É'=>'E',
                                'Ì'=>'I', 'Í'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'à'=>'a', 'á'=>'a', 'è'=>'e', 'é'=>'e', 'ì'=>'i', 'í'=>'i', 'Ñ'=>'N', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ù'=>'u', 'ú'=>'u' );
			?>
<div class="container">
	@for($k=0;$k < count($estudiantes) ;$k++)
		<table class="table table-bordered table-striped" id="tbDatos">
			<thead>
				<tr>
					<th colspan="14"  style="border: solid 1px;">
						<div align="center"> <b> Lista de datos basicos de estudiantes </b> </div>
						<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
						<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
					</th>
				</tr>
				<tr>
					<th style="border: solid 1px;">Cod. Matricula</th>
					<th style="border: solid 1px;">Fecha Matricula</th>
					<th style="border: solid 1px;">Nombre completo</th>
					<th style="border: solid 1px;">Tipo doc.</th>
					<th style="border: solid 1px;">Numero doc.</th>
					<th style="border: solid 1px;">Curso</th>
					<th style="border: solid 1px;">Nombre acudiente</th>
					<th style="border: solid 1px;">Tipo doc. acudiente</th>
					<th style="border: solid 1px;">Num. doc. acudiente</th>
					<th style="border: solid 1px;">Dir. acudiente</th>
					<th style="border: solid 1px;">Email acudiente</th>
					<th style="border: solid 1px;">Regimen</th>
					<th style="border: solid 1px;">Vlr. Matricula</th>
					<th style="border: solid 1px;">Vlr. Pension</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $estudiantes[$k]['listado'] as $registro )
					<?php

						$responsable_financiero = (object)[ 'descripcion' => '', 'tipo_doc_identidad' => (object)['abreviatura' => ''], 'numero_identificacion' => 0, 'direccion1' => '', 'email' => '', 'tipo' => '' ];
						if ( !is_null( $registro->estudiante->responsable_financiero() ) )
						{
							$responsable_financiero = $registro->estudiante->responsable_financiero()->tercero;
						}
						
						$libretas_pagos = $registro->libretas_pagos;
						$ultima_libreta = $registro->get_libreta_del_anio( $registro->matricula_id, $registro->periodo_lectivo->fecha_desde );

						$valor_matricula = 0;
						$valor_pension = 0;
						if ( $ultima_libreta != null )
						{
							$valor_matricula = $ultima_libreta->valor_matricula;
							$valor_pension = $ultima_libreta->valor_pension_mensual;
						}
					?>
					<tr>
						<td style="border: solid 1px;"> {{ $registro->codigo }} </td>
						<td style="border: solid 1px;"> {{ $registro->fecha_matricula }} </td>
						<td style="border: solid 1px;"> {{ strtr( $registro->nombre_completo, $unwanted_array) }} </td>
						<td style="border: solid 1px;"> {{ $registro->tipo_documento }} </td>
						<td style="border: solid 1px;"> {{ number_format( $registro->numero_identificacion, 0,',','.' ) }} </td>
						<td style="border: solid 1px;"> {{ $registro->curso_descripcion }} </td>
						<td style="border: solid 1px;"> {{ $responsable_financiero->descripcion }} </td>
						<td style="border: solid 1px;"> {{ $responsable_financiero->tipo_doc_identidad->abreviatura }} </td>
						<td style="border: solid 1px;"> {{ number_format( $responsable_financiero->numero_identificacion, 0,',','.' ) }} </td>
						<td style="border: solid 1px;"> {{ $responsable_financiero->direccion1 }} </td>
						<td style="border: solid 1px;"> {{ $responsable_financiero->email }} </td>
						<td style="border: solid 1px;"> {{ $responsable_financiero->tipo }} </td>
						<td style="border: solid 1px;"> ${{ number_format( $valor_matricula, 0,',','.' ) }} </td>
						<td style="border: solid 1px;"> ${{ number_format( $valor_pension, 0,',','.' ) }} </td>
					</tr>
				@endforeach
		</table>
		<div class="page-break"></div>
	@endfor
</div>
</td>
</tr>
</table>