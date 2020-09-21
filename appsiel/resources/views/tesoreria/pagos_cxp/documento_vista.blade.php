<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
        </td>
        <td style="border: solid 1px #ddd;">
        </td>
    </tr>
    <tr>        
        <td colspan="2" style="border: solid 1px #ddd;">
            @if( !is_null( $caja ) )
                <b>Caja: &nbsp;&nbsp;</b> {{ $caja->descripcion }}
                <br>
            @endif
            @if( !is_null( $cuenta_bancaria ) )
                <b>Cuenta bancaria: &nbsp;&nbsp;</b> Cuenta {{ $cuenta_bancaria->tipo_cuenta }} {{ $cuenta_bancaria->entidad_financiera->descripcion }} No. {{ $cuenta_bancaria->descripcion }}
                <br>
            @endif
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
</table>

<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Tercero','Documento','Fecha doc.','Detalle','Abono']) }}
    <tbody>
        <?php 
        
        $total_abono = 0;

        ?>
        @foreach($doc_pagados as $linea )

            <?php 
                $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->doc_cxp_transacc_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->doc_cxp_transacc_id)
                ->where('core_tipo_doc_app_id',$linea->doc_cxp_tipo_doc_id)
                ->where('consecutivo',$linea->doc_cxp_consecutivo)
                ->get()->first();
            ?>

            <tr>
                <td> {{ $linea->tercero_nombre_completo }} </td>
                <td> {{ $linea->documento_prefijo_consecutivo }} </td>
                <td> {{ $el_documento->fecha }} </td>
                <td> {{ $el_documento->descripcion }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->abono, 2, ',', '.') }} </td>
            </tr>
            <?php 
                $total_abono += $linea->abono;
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">&nbsp;</td>
            <td style="text-align: right;"> {{ number_format($total_abono, 2, ',', '.') }} </td>
        </tr>
    </tfoot>
</table>