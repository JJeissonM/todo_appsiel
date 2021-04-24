<?php
	$cant_fallas = 0;
	if( !empty($linea->fallas->toArray()) )
	{
		$cant_fallas = $linea->fallas->toArray()[0]['cantidad'];
	}
?>
{{ $cant_fallas }}