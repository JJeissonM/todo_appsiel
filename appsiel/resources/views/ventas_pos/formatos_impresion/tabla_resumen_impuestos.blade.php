
@if( (int)config('configuracion.liquidacion_impuestos') )

<style type="text/css">
    .tabla_resumen_impuestos{
        width: 100%;
        font-size: 12px;
        border-collapse: collapse;
    }

    .tabla_resumen_impuestos td {
        border: 1px solid #ddd;
        border-collapse: collapse;
    }

    .encabezado td {
        font-weight: bold;
        text-align: center;
    }
</style>
    <div id="div_resumen_impuestos" style="display: none;">
        <br>
        <div style="text-align: center; width: 100%; font-weight: bold; font-size: 12px;">Detalle impuestos</div>
        <div class="table-responsive">
            <table class="tabla_resumen_impuestos" id="tabla_resumen_impuestos">
                <thead>
                    <tr>
                        <th>Tipo producto</th>
                        <th>Vlr. Compra</th>
                        <th>Base</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endif