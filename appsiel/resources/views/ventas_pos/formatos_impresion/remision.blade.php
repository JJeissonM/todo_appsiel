
    <table border="0" style="margin-top: 0px !important;" width="100%">
        <td>
            <h4 style="text-align: center;">
                <span>--- (Copia Cocina) ---</span>
                <br>
                <b>
                    <?php 
                    if (!isset($pdv_descripcion)) {
                        $pdv_descripcion = '';
                    }
                        if (!isset($tipo_doc_app)) {
                            $tipo_doc_app = (object)[
                                'descripcion' => '',
                                'prefijo' => ''
                            ];
                        }
                    ?>
                    {{ $pdv_descripcion }}</b>
            </h4>
        </td>
        <tr>
            <td>
                <b>{{ $tipo_doc_app->descripcion }} No.</b> 
                @if( !is_null( $resolucion ) )
                    {{ $resolucion->prefijo }}
                @else
                    {{ $tipo_doc_app->prefijo }}
                @endif
                <div class="lbl_consecutivo_doc_encabezado" style="display: inline;"></div>
            </td>
        </tr>
    </table>

    <div class="subheadp" >
        <b>Cliente:</b> <div class="lbl_cliente_descripcion" style="display: inline;"> {{ $cliente->tercero->descripcion }} </div> 
        <br>
        <b>Atendido por: &nbsp;&nbsp;</b> 
        <div class="lbl_atendido_por" style="display: inline;"> {{ $cliente->vendedor->tercero->descripcion }} </div>
        <br>
    </div>

    <table style="width: 100%; font-size: 15px;" id="tabla_productos_facturados2">
        {{ Form::bsTableHeader(['Producto','Cant.']) }}
        <tbody>
        </tbody>
    </table>
    <br>
    <b> Cantidad de items&nbsp;: </b> <div style="display: inline;" id="cantidad_total_productos" ></div>
    <br>
    <b> Despachado por &nbsp;&nbsp;&nbsp;: </b> _____________________    
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> </div>
    <br><br>