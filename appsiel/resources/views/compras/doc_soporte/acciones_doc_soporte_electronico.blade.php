
@if(Input::get('id_transaccion') == 48)
    <?php 
        $tipo_operacion2 = 'support_doc';
    ?>
    @if( $doc_encabezado->enviado_electronicamente() )
        <a class="btn-gmail" href="{{ url( 'fe_consultar_documentos_emitidos/' . $doc_encabezado->id . '/' . $tipo_operacion2 . $variables_url ) }}" title="Representación gráfica (PDF)" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
    @else
        <?php 
            $color = 'red';
        ?>
        <a class="btn-gmail" href="{{ url( 'fe_doc_soporte_enviar/' . $doc_encabezado->id . $variables_url ) }}" title="Enviar"><i class="fa fa-btn fa-send"></i></a>
    @endif
@endif