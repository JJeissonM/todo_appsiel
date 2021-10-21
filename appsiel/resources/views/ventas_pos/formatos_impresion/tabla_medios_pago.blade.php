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
<div id="div_resumen_medios_pago" style="display: none;">
    <br>
    <div class="table-responsive">
        <table class="tabla_resumen_medios_pago">
            <tr class="encabezado">
                <td>Medio pago</td>
                <td>Caja/Banco</td>
                <td>Valor</td>
            </tr>
            <tr>
                <td id="lbl_medio_pago"></td>
                <td id="lbl_caja_banco"></td>
                <td id="lbl_valor_medio_pago"></td>
            </tr>
        </table>
    </div>
</div>
    