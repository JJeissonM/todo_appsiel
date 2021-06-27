<?php
	$convenciones = 'S= Siempre &nbsp;&nbsp;&nbsp;&nbsp;   CS= Casi siempre  &nbsp;&nbsp;&nbsp;&nbsp;      AV= Algunas veces  &nbsp;&nbsp;&nbsp;&nbsp; N= Nunca';
	
	switch ( config('matriculas.convenciones_valoraciones_aspectos') ) {
		case '1':
			$convenciones = 'S= Siempre &nbsp;&nbsp;&nbsp;&nbsp;   CS= Casi siempre  &nbsp;&nbsp;&nbsp;&nbsp;      AV= Algunas veces  &nbsp;&nbsp;&nbsp;&nbsp; N= Nunca';
		break;

		case '2':
			$convenciones = 'Db= Desempeño bajo &nbsp;&nbsp;&nbsp;&nbsp;  DB= Desempeño Básico &nbsp;&nbsp;&nbsp;&nbsp; DA= Desempeño Alto  &nbsp;&nbsp;&nbsp;&nbsp; DS= Desempeño Superior';
		break;

		default:
		break;
	}
?>
{{ $convenciones }}