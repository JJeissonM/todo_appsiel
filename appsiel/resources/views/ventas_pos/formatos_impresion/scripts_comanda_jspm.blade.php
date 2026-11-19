@if( (int)$params_JSPrintManager->usar_complemento_JSPrintManager && config('ventas.metodo_impresion_pedido_restaurante') != 'apm' )
    <script src="{{ asset('assets/js/ventas_pos/external_print/cptable.js') }}"></script>
    <script src="{{ asset('assets/js/ventas_pos/external_print/cputils.js') }}"></script>
    <script src="{{ asset('assets/js/ventas_pos/external_print/JSESCPOSBuilder.js') }}"></script>
    <script src="{{ asset('assets/js/ventas_pos/external_print/JSPrintManager.js') }}"></script>
@endif
