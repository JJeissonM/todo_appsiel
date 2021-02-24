<style>
table {
	width: 100%;
}

th, td {
    border-bottom: 1px solid #ddd;
}

th {
	background-color: #CACACA;
}

td.celda {
	width: 50px;
	border-left: 1px solid #ddd;
	border-right: 1px solid #ddd;
}

h3 {
	text-align:center;
}

div.recuadro{
	
}

.page-break {
    page-break-after: always;
}
</style>
<div class="container">
	@for($k=0;$k < count($estudiantes) ;$k++)
		<!-- TITULOS -->
		

		<table class="table table-bordered table-striped" id="tbDatos">
			<thead>
				<tr>
					<th colspan="5">
						<div align="center"> <b> Lista de datos básicos de estudiantes </b> </div>
						<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
						<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
					</th>
				</tr>
				<tr>
					<th>Nombre completo</th>
					<th>Doc. Identidad</th>
					<th>Dirección</th>
					<th>Teléfono</th>
					<th>Nombre acudiente</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ($estudiantes[$k]['listado'] as $registro){ 
					?>
				<tr>
					<td class="celda1" width="320px"> {{ $registro->nombre_completo }}</td>
					<td class="celda1" width="100px"> {{ $registro->tipo_y_numero_documento_identidad }}</td>
				
					<td class="celda1">{{ $registro->direccion1 }}</td>
				
					<td class="celda2">{{ $registro->telefono1 }}</td>
				
					<td class="celda2">{{ $registro->acudiente }}</td>
				</tr>
				<?php } ?>
		</table>
		<div class="page-break"></div>
	@endfor
</div>