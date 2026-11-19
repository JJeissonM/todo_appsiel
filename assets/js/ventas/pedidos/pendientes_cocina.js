$(document).ready(function () {
    var imprimiendo = false;

    $('.btn_imprimir_en_cocina').on('click', function (event) {
        event.preventDefault();
        if (imprimiendo) {
            return;
        }

        var boton = $(this);
        var metodo = $('#metodo_impresion_pedido_ventas').val() || 'normal';
        if (metodo !== 'apm' && metodo !== 'jspm') {
            window.open(boton.attr('href'), '_blank');
            return;
        }

        imprimiendo = true;
        $('.btn_imprimir_en_cocina').addClass('disabled').attr('aria-disabled', 'true');

        Promise.resolve().then(function () {
            var lineas = boton.data('lineas_registros');
            if (!lineas || !Object.keys(lineas).length) {
                throw new Error('El pedido no contiene productos para imprimir.');
            }

            $.each([
                'lbl_consecutivo_doc_encabezado', 'lbl_fecha', 'lbl_cliente_descripcion',
                'lbl_descripcion_doc_encabezado', 'lbl_total_factura', 'nombre_vendedor',
                'core_tipo_transaccion_id', 'core_tipo_doc_app_id'
            ], function (i, campo) {
                $('#' + campo).val(boton.data(campo));
            });

            var tabla = $('#tabla_lineas_registros tbody').empty();
            $.each(lineas, function (i, linea) {
                var fila = $('<tr>').addClass('linea_registro');
                $.each({
                    lbl_producto_descripcion: linea.lbl_producto_descripcion,
                    cantidad: linea.cantidad_numerica,
                    precio_unitario: linea.precio_unitario_numerico,
                    precio_total: linea.precio_total_numerico
                }, function (clase, valor) {
                    fila.append($('<td>').addClass(clase).text(valor));
                });
                tabla.append(fila);
            });

            return metodo === 'apm' ? print_comanda_apm() : print_comanda_jspm();
        }).then(function () {
            Swal.fire({
                icon: 'info',
                title: 'Impresión enviada',
                text: 'Pedido enviado a la impresora de COCINA.'
            });
        }).catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de impresión',
                text: error && (error.ErrorMessage || error.message) || 'No fue posible enviar el pedido a la cocina.'
            });
        }).then(function () {
            $('#tabla_lineas_registros tbody').empty();
            $('.btn_imprimir_en_cocina').removeClass('disabled').removeAttr('aria-disabled');
            imprimiendo = false;
        });
    });
});
