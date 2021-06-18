<?php
	$examenes = App\Salud\ExamenMedico::examenes_del_paciente( $consulta->paciente_id, $consulta->id );
	$cantidad = count( $examenes );
?>

<br>
<!-- Este for dibuja un botón en cada iteración -->
<div class="btns_examenes">
	@for($i = 0; $i < $cantidad; $i++ )
		{!! $examenes[$i] !!}
	@endfor
</div>