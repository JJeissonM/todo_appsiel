<h4 style="width: 100%; text-align: center;"> Entradas de almacén pendientes por facturar </h4>
<div class="table-responsive">
    <table id="myTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="display: none;"> ID Encabezado Entrada </th>
                <th> Tercero </th>
                <th> Documento </th>
                <th> Fecha </th>
                <th> Detalle </th>
                <th> Valor Entrada </th>
                <th> Valor Factura (IVA incluido) </th>
                <th class="td_boton"> </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $gran_total_valor_documento = 0;
                $gran_total_documento_mas_iva = 0;
            ?>
               @foreach( $entradas as $linea)         
                    <tr class="fila-{{$j}}" id="{{ $linea->id }}">
                        <td style="display: none;"> {{ $linea->id }} </td>
                        <td> {{ $linea->tercero_nombre_completo }} </td>
                        <td> {{ $linea->documento_transaccion_prefijo_consecutivo }} </td>
                        <td> {{ $linea->fecha }} </td>
                        <td> {{ $linea->descripcion }} </td>
                        <td> ${{ number_format($linea->total_documento, 2, ',', '.') }} </td>
                        <td> ${{ number_format($linea->total_documento_mas_iva, 2, ',', '.') }} </td>
                        <td style="display: none;" class="td_boton">
                            <a href="#" class="btn btn-success btn-xs btn_agregar_documento" style="display: none;"><i class="fa fa-check"></i></a>
                        </td>                
                    </tr>
                    <?php
                        $j++;
                        if ($j==3) {
                            $j=1;
                        }
                        $gran_total_valor_documento += $linea->total_documento;
                        $gran_total_documento_mas_iva += $linea->total_documento_mas_iva;
                    ?>
                @endforeach

        </tbody>
        <tfoot>
            <tr class="fila-{{$j}}" >
                <td style="display: none;"> &nbsp; </td>
                <td colspan="4"> &nbsp; </td>
                <td> ${{ number_format($gran_total_valor_documento, 2, ',', '.') }} </td>
                <td> ${{ number_format($gran_total_documento_mas_iva, 2, ',', '.') }} </td>
                <td style="display: none;" class="td_boton"> &nbsp; </td>
            </tr>        
        </tfoot>
    </table>
</div>