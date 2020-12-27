<?php
	$nombre = '';
	if ( !is_null( $linea->profesor_asignatura ) ) 
	{
		$nombre = $linea->profesor_asignatura->profesor->name;
	}
?>
@if ( $mostrar_nombre_docentes == 'Si') 
	<div style="text-align: right; width: 100%; font-size: {{$tam_letra-1}}mm">
		<b>docente: </b> {{ ucwords( ($nombre) ) }}
	</div>
@endif