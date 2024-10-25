$(document).ready(function () {

    $("#btn_imprimir_prefactura").click(function (event) {
        event.preventDefault();

        if (hay_productos == 0) {
            Swal.fire({
                icon: "error",
                title: "Alerta!",
                text: "No ha ingresado productos.",
            });
            return false;
        }

        if ( !validar_producto_con_contorno() ) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Has ingresado productos que necesitan Contorno, pero NO está agregado el Contorno.'
            });
        
            return false;
        }

        //$("#linea_ingreso_default").remove();

        var table = $("#ingreso_registros").tableToJSON();

        json_table2 = get_json_registros_medios_recaudo();

        if ($("#manejar_propinas").val() == 1) {
            // Si hay propina, siempre va a venir una sola linea de medio de pago
            json_table2 = separar_json_linea_medios_recaudo(json_table2);
        }

        if ($("#manejar_datafono").val() == 1) {
            // Si hay Comision por datafono, siempre va a venir una sola linea de medio de pago
            json_table2 = separar_json_linea_medios_recaudo(json_table2);
        }

        // Se asigna el objeto JSON a un campo oculto del formulario
        $("#lineas_registros").val(JSON.stringify(table));
        $("#lineas_registros_medios_recaudos").val(json_table2);

        // Nota: No se puede enviar controles disabled

        var url = $("#form_create").attr("action");
        var data = $("#form_create").serialize();

        if ($("#manejar_propinas").val() == 1) {
            data += "&valor_propina=" + $("#valor_propina").val();
        }

        if ($("#manejar_datafono").val() == 1) {
            data += "&valor_datafono=" + $("#valor_datafono").val();
        }

        $("title").append('PRE-FACTURA');

        //var label_documento = $(".lbl_consecutivo_doc_encabezado").parent('td').html();
        //$(".lbl_consecutivo_doc_encabezado").parent('td').text('-- PRE-FACTURA --');

        $(".lbl_consecutivo_doc_encabezado").text('-- PRE-FACTURA --');

        $("#tabla_productos_facturados").find("tbody").html('');

        llenar_tabla_productos_facturados( false );
        
        $('#tr_total_recibido').hide();
        $('#tr_total_cambio').hide();
        $('#tabla_resumen_medios_pago').hide();

        enviar_impresion( 'prefactura' );

        $('#tr_total_recibido').show();
        $('#tr_total_cambio').show();
        $('#tabla_resumen_medios_pago').show();
        //$(".lbl_consecutivo_doc_encabezado").parent('td').text( label_documento );
        $("#tabla_productos_facturados").find("tbody").html('');
        
        $('#msj_ventana_impresion_abierta').hide();
    });

});
