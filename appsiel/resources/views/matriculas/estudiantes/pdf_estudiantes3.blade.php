<style type="text/css">
	.page-break {
	    page-break-after: always;
	}
</style>

<table class="table table-striped">
	<tr>
		<td>
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
					<th colspan="5">
						<div align="center"> <b> Lista de datos basicos de estudiantes </b> </div>
						<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
						<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
					</th>
				</tr>
				<tr>
					<th style="border: solid 1px;">Nombre completo</th>
					<th style="border: solid 1px;">Doc. Identidad</th>
					<th style="border: solid 1px;">Direccion</th>
					<th style="border: solid 1px;">Telefono</th>
					<th style="border: solid 1px;">Nombre acudiente</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ($estudiantes[$k]['listado'] as $registro){ 
					?>
				<tr>
					<td class="celda1" width="320px" style="border: solid 1px;"> {{ strtr( $registro->nombre_completo, $unwanted_array ) }}</td>
					<td class="celda1" width="100px" style="border: solid 1px;"> {{ $registro->tipo_y_numero_documento_identidad }}</td>
				
					<td class="celda1" style="border: solid 1px;">{{ $registro->direccion1 }}</td>
				
					<td class="celda2" style="border: solid 1px;">{{ $registro->telefono1 }}</td>
				
					<td class="celda2" style="border: solid 1px;">{{ $registro->acudiente }}</td>
				</tr>
				<?php } ?>
		</table>
		<div class="page-break"></div>
	@endfor
</div>
		</td>
	</tr>
</table>