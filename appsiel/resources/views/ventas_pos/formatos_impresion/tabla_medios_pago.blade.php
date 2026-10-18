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

    .encabezado td, .encabezado th {
        font-weight: bold;
        text-align: center;
    }
</style>

@if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
    <div id="div_resumen_medios_pago" style="display: none;">
        <br>
        <div style="text-align: center; width: 100%; font-weight: bold; font-size: 12px; background: #ddd;">Pagos</div>
        <div class="table-responsive">
            <table class="tabla_resumen_medios_pago" id="tabla_resumen_medios_pago">
                <thead>
                    <tr class="encabezado">
                        <th>Medio</th>
                        <th>Caja/Banco</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td>&nbsp;</td>
                        <td style="text-align: center;">Total</td>
                        <td id="total_resumen_medios_pago" style="text-align: right;">$0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif