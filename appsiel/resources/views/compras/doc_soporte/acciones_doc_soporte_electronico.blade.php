
@if(Input::get('id_transaccion') == 48 && config('facturacion_electronica.documento_soporte_activo') == '1')
    <?php 
        $tipo_operacion2 = 'support_doc';
        $ultimo_envio = $doc_encabezado->resultados_envios_fe_doc_soporte()->last();
        $estado_envio = '';
        if ($ultimo_envio != null) {
            if ( $ultimo_envio->resultado == 'Rechazado por la DIAN') {
                $estado_envio = '<i class="fa fa-circle" style="color: red;"> </i> ' . $ultimo_envio->resultado;
            }
            if ( $ultimo_envio->resultado == 'Procesado') {
                $estado_envio = '<i class="fa fa-circle" style="color: green;"> </i> ' . $ultimo_envio->resultado;
            }
        }
    ?>
    @if( $doc_encabezado->enviado_electronicamente() )
        <a class="btn-gmail" href="{{ url( 'fe_consultar_documentos_emitidos/' . $doc_encabezado->id . '/' . $tipo_operacion2 . $variables_url ) }}" title="Representación gráfica (PDF)" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
        {!! $estado_envio !!}
    @else
        <?php
            $color = 'red';
        ?>
        <a class="btn-gmail" href="{{ url( 'fe_doc_soporte_enviar/' . $doc_encabezado->id . $variables_url ) }}" title="Enviar"><i class="fa fa-btn fa-send"></i></a>
        <i class="fa fa-circle" style="color: orange;"> Sin enviar </i>
    @endif
@endif