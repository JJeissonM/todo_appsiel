<?php	

	switch ( $mostrar_etiqueta_final )
	{
		case 'aprobo_reprobo':
			$etiqueta = 'APROBÓ( &nbsp;&nbsp; )  &nbsp;&nbsp;&nbsp;&nbsp;    REPROBÓ( &nbsp;&nbsp; )    &nbsp;&nbsp;&nbsp;&nbsp;    APLAZÓ( &nbsp;&nbsp; )'; 
			break;
		
		default:
			$etiqueta = '';
			break;
	}

	echo $etiqueta;
?>