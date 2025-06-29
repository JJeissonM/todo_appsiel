<style type="text/css">
    .tabla_resumen_medios_pago{
        width: 100%;
        font-size: 12px;
        border-collapse: collapse;
    }

    .tabla_resumen_medios_pago td {
        border: 1px solid #ddd;
        border-collapse: collapse;
    }

    .encabezado td {
        font-weight: bold;
        text-align: center;
    }
</style>

@if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
    <div id="div_resumen_medios_pago" style="display: none;">
        <br>
        <div style="text-align: center; width: 100%; font-weight: bold; font-size: 12px;">Detalle Medios de pago</div>
        <div class="table-responsive">
            <table class="tabla_resumen_medios_pago" id="tabla_resumen_medios_pago">
                <thead>
                    <tr class="encabezado">
                        <td>Medio pago</td>
                        <td>Caja/Banco</td>
                        <td>Valor</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endif