$(document).ready(function () {

    // ── Select2 en todos los selects del mapeo ────────────────────────
    $('.select2-mapeo').select2({
        placeholder: '-- Seleccionar producto --',
        allowClear: true,
        width: '100%',
        templateSelection: function (data) {
            if (!data || !data.element) return data.text;
            var um = $(data.element).attr('data-um') || '';
            if (um) return data.text + ' (' + um + ')';
            return data.text;
        },
        templateResult: function (data) {
            // Dejar el dropdown consistente con la selección
            if (!data || !data.element) return data.text;
            var um = $(data.element).attr('data-um') || '';
            if (um) return data.text + ' (' + um + ')';
            return data.text;
        }
    });

    function toNumber(v, fallback) {
        var n = parseFloat(v);
        return isNaN(n) ? (fallback || 0) : n;
    }

    function calcularCantidadConvertida(cantidadXml, factor, tipoFactor) {
        if (factor <= 0) factor = 1;
        if (tipoFactor === 'multiplicacion') {
            return cantidadXml * factor;
        }
        // division
        return cantidadXml / factor;
    }

    function recalcularPrecioUnitarioDesdeTotal($fila) {
        var totalXml = toNumber($fila.find('.total-xml').val(), 0);
        var cantidadConvertida = toNumber($fila.find('.cantidad-convertida').val(), 0);
        if (cantidadConvertida > 0) {
            var precio = totalXml / cantidadConvertida;
            // Sugerimos el valor pero el usuario puede editarlo después.
            $fila.find('.precio-unitario-final').val(precio.toFixed(6));
        }
    }

    function recalcularCantidadYPrecioDesdeFactor($fila) {
        var cantidadXml = toNumber($fila.find('.cantidad-xml').val(), 0);
        var factor = toNumber($fila.find('.factor-conversion').val(), 1);
        var tipoFactor = $fila.find('.tipo-factor').val() || 'division';
        var cantidadConvertida = calcularCantidadConvertida(cantidadXml, factor, tipoFactor);
        if (cantidadConvertida > 0) {
            $fila.find('.cantidad-convertida').val(cantidadConvertida);
        }
        recalcularPrecioUnitarioDesdeTotal($fila);
    }

    function cargarDatosProducto($fila, productoId) {
        var proveedorId = $('#form_mapeo_xml input[name="proveedor_id"]').val();
        var fechaDoc = $('#form_mapeo_xml input[name="fecha_doc"]').val();

        if (!productoId) {
            return;
        }

        $.ajax({
            // Usar URL absoluta para evitar que caiga en /compras/{id} (resource) al estar en la vista show
            url: '/compras_consultar_existencia_producto',
            method: 'GET',
            data: {
                bodega_id: 0,
                proveedor_id: proveedorId,
                producto_id: productoId,
                fecha: fechaDoc,
                liquida_impuestos: 1
            }
        }).done(function (resp) {
            var um = (resp && resp.unidad_medida1) ? resp.unidad_medida1 : '—';
            var $select = $fila.find('.select2-mapeo');
            var $opt = $select.find('option[value="' + productoId + '"]');
            $opt.attr('data-um', um);
            
            // Actualizar la etiqueta U.M. Appsiel en la nueva columna
            $fila.find('.label-um-appsiel').text(um);

            // Refrescar el texto mostrado por select2
            $select.trigger('change.select2');
        }).fail(function () {
            $fila.find('.label-um-appsiel').text('—');
        });
    }

    // ── Al seleccionar producto: traer U.M. Appsiel ───────────────────
    $(document).on('change', '.select2-mapeo', function () {
        var $fila = $(this).closest('tr');
        var productoId = $(this).val();
        cargarDatosProducto($fila, productoId);
    });

    // ── Recalcular precio unitario al cambiar cantidad convertida ─────
    $(document).on('change input', '.cantidad-convertida', function () {
        var $fila = $(this).closest('tr');
        // Si el usuario define la cantidad final, derivamos el factor (memoria) según la operación elegida
        var cantidadXml = toNumber($fila.find('.cantidad-xml').val(), 0);
        var cantidadConvertida = toNumber($fila.find('.cantidad-convertida').val(), 0);
        var tipoFactor = $fila.find('.tipo-factor').val() || 'division';
        if (cantidadXml > 0 && cantidadConvertida > 0) {
            var factor;
            if (tipoFactor === 'multiplicacion') {
                factor = cantidadConvertida / cantidadXml;
            } else {
                // division
                factor = cantidadXml / cantidadConvertida;
            }
            if (factor > 0) {
                $fila.find('.factor-conversion').val(factor);
            }
        }
        recalcularPrecioUnitarioDesdeTotal($fila);
    });

    // ── Al cambiar factor u operación: recalcular cantidad + precio ───
    $(document).on('change input', '.factor-conversion, .tipo-factor', function () {
        var $fila = $(this).closest('tr');
        recalcularCantidadYPrecioDesdeFactor($fila);
    });

    // ── Inicialización: cargar U.M. Appsiel de filas ya seleccionadas ─
    $('.select2-mapeo').each(function () {
        var $fila = $(this).closest('tr');
        var productoId = $(this).val();
        if (productoId) {
            cargarDatosProducto($fila, productoId);
        }
    });

    // ── Interceptar el envío del formulario para validar mapeo parcial ──
    $('#form_mapeo_xml').on('submit', function (e) {
        // Si ya estamos enviando de forma nativa, no interceptar
        if (this.enviando_nativo) return true;

        e.preventDefault();
        var $form = $(this);
        var self = this;

        var sinMapear = 0;
        $form.find('.select2-mapeo').each(function () {
            if (!$(this).val()) sinMapear++;
        });

        if (sinMapear > 0) {
            var msg = 'Hay ' + sinMapear + ' producto(s) sin vincular. Tu progreso actual se guardará correctamente. ¿Deseas continuar?';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Mapeo parcial',
                    text: msg,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar progreso',
                    cancelButtonText: 'Seguir editando'
                }).then(function (result) {
                    // Compatibilidad con diferentes versiones de SweetAlert2
                    if (result && (result.isConfirmed || result.value)) {
                        self.enviando_nativo = true;
                        self.submit();
                    }
                });
            } else {
                if (confirm(msg)) {
                    self.enviando_nativo = true;
                    self.submit();
                }
            }
        } else {
            self.enviando_nativo = true;
            self.submit();
        }
    });
});
