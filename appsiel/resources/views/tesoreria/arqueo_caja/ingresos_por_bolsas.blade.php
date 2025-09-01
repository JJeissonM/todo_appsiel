<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();
    $result = $service->resumen_ingresos_bolsas($registro->fecha, $registro->teso_caja_id);
?>

@if( $result->status == 'success')
    <div class="row">
        <div class="well">
            <b>Recargo bolsas (incluido en ventas): </b> $ {{ number_format( $result->valor_total_bolsas, 0, ',','.') }} <br>
        </div>
    </div>
    <br>
@else
    <b>Nota:</b> {{ $result->message }}
@endif
