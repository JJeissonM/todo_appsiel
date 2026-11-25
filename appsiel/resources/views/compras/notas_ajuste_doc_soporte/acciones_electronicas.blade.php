
@if(config('facturacion_electronica.documento_soporte_activo') == '1')
    <?php 
        $tipo_operacion2 = 'support_doc_note';
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

        @if( config('facturacion_electronica.proveedor_tecnologico_default') == 'OSEI' )
            <a class="btn-gmail btn-reenviar-correo-osei-nota" href="#" title="Reenviar correo" data-url="{{ url('fe_reenviar_correo/' . $doc_encabezado->id . '/' . $tipo_operacion2) }}" data-email="{{ $doc_encabezado->tercero ? $doc_encabezado->tercero->email : '' }}"><i class="fa fa-envelope"></i></a>

            <script type="text/javascript">
                $('.btn-reenviar-correo-osei-nota').click(function(event){
                    event.preventDefault();

                    var boton = $(this);
                    var correo = prompt('Correo(s) destino (separe con coma):', boton.data('email') || '');
                    if (correo === null) return;

                    boton.children('i').attr('class', 'fa fa-spinner fa-spin');

                    $.post(boton.data('url'), {_token: '{{ csrf_token() }}', emails: correo})
                        .done(function(respuesta){ alert(respuesta.message); })
                        .fail(function(){ alert('No se pudo reenviar el correo.'); })
                        .always(function(){ boton.children('i').attr('class', 'fa fa-envelope'); });
                });
            </script>
        @endif

        {!! $estado_envio !!}
    @else
        <?php
            $color = 'red';
        ?>
        <a class="btn-gmail" href="{{ url( 'fe_nota_ajuste_doc_soporte_enviar/' . $doc_encabezado->id . $variables_url ) }}" title="Enviar"><i class="fa fa-btn fa-send"></i></a>
        <i class="fa fa-circle" style="color: orange;"> Sin enviar </i>
    @endif
@endif