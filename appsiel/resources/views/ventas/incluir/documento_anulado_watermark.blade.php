<?php
    $estado_documento_anulado = '';

    if (isset($estado_documento)) {
        $estado_documento_anulado = $estado_documento;
    } elseif (isset($doc_encabezado) && !is_null($doc_encabezado) && isset($doc_encabezado->estado)) {
        $estado_documento_anulado = $doc_encabezado->estado;
    } elseif (isset($datos_factura) && !is_null($datos_factura) && isset($datos_factura->estado)) {
        $estado_documento_anulado = $datos_factura->estado;
    }
?>

<style type="text/css">
    .documento-anulado-watermark,
    .lbl_doc_anulado {
        position: fixed;
        top: 42%;
        left: 0;
        z-index: 9999;
        width: 100%;
        background: transparent;
        color: rgba(220, 0, 0, 0.22);
        border-top: 4px solid rgba(220, 0, 0, 0.22);
        border-bottom: 4px solid rgba(220, 0, 0, 0.22);
        font-size: 48px;
        font-weight: bold;
        line-height: 1.2;
        text-align: center;
        text-transform: uppercase;
        transform: rotate(-35deg);
        pointer-events: none;
    }

    @media print {
        .documento-anulado-watermark,
        .lbl_doc_anulado {
            position: fixed;
        }
    }
</style>

@if($estado_documento_anulado == 'Anulado')
    <div class="documento-anulado-watermark">
        Documento Anulado
    </div>
@endif
