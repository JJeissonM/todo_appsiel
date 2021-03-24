@foreach( $documentos_anticipos AS $key => $registro )
	@if( $registro['saldo_pendiente'] < 0 )
		<h5 class="text-info" style="background: #ddd; padding: 5px; border-radius: 4px;">Nota: El responsable financiero tiene documentos de anticipos pendientes por cruzar. Antes de hacer recaudos, por favor verifique <a href="{{ url('vista_reporte?id=' . Input::get('id') . '&reporte_id=22') }}" target="_blank">Aquí</a>. </h5>
		<?php
			break;
		?>
	@endif
@endforeach