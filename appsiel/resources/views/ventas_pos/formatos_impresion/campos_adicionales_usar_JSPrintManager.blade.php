<input type="hidden" id="enviar_impresion_directamente_a_la_impresora" name="enviar_impresion_directamente_a_la_impresora" value="{{ $params_JSPrintManager->enviar_impresion_directamente_a_la_impresora }}">

<input type="hidden" id="impresora_principal_por_defecto" name="impresora_principal_por_defecto" value="{{ $params_JSPrintManager->impresora_principal_por_defecto }}">

<input type="hidden" id="impresora_cocina_por_defecto" name="impresora_cocina_por_defecto" value="{{ $params_JSPrintManager->impresora_cocina_por_defecto }}">

<div id="div_formato_impresion_remision" style="display: none;">
    @include('ventas_pos.formatos_impresion.remision')
</div>
