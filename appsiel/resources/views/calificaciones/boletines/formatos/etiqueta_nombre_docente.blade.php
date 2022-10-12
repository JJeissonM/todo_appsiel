<?php
	$nombre = 'no asignado';
	if ( $linea->profesor_asignatura != null ) 
	{
		if ($linea->profesor_asignatura->profesor != null) {
			$nombre = $linea->profesor_asignatura->profesor->name;
		}		
	}
?>
@if ( $mostrar_nombre_docentes == 'Si') 
	<div style="text-align: right; width: 100%; font-size: {{$tam_letra-1}}mm">
		<b>docente: </b> {{ ucwords( ($nombre) ) }}
	</div>
@endif