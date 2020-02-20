<style>
table {
	width: 100%;
	font-size: 13px;
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
		<div align="center"> <b> Lista de usuarios de estudiantes </b> </div>
		<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
		<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}

				<?php 
				foreach ($estudiantes[$k]['listado'] as $registro){ 
						$nombre_completo = $registro->apellido1." ".$registro->apellido2." ".$registro->nombres;
					?>

		<table class="table">
			<thead>	
				<tr>
					<th>Nombre completo</th>
					<th>Usuario</th>
					<th>Contraseña</th>
					<th>Nota</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="celda1" width="200px"> {{ $nombre_completo }}</td>
					<td class="celda1">{{ $registro->email }}</td>
				
					<td class="celda1" width="120px"> <b>123456iq</b></td>
				
					<td class="celda2"> Debe cambiar la contraseña en el perfil.</td>
				</tr>
		</table>
				<?php } ?>
		<div class="page-break"></div>
	@endfor
</div>