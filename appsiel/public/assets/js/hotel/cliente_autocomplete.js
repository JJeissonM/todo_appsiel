(function ($) {
    var activeInput = null;
    var typingTimer = null;

    function rootUrl() {
        if (typeof url_raiz !== 'undefined') {
            return url_raiz;
        }

        return '';
    }

    function currentQueryParam(name) {
        if (typeof getParameterByName === 'function') {
            return getParameterByName(name);
        }

        return '';
    }

    function setModalFieldsEnabled(enabled) {
        var $modal = $('#hotelClienteAutocompleteModal');
        $modal.find(':input').not('[name="_token"]').prop('disabled', !enabled);
        $modal.find('[data-dismiss="modal"], .close').prop('disabled', false);
    }

    function setCliente($input, clienteId, nombreCliente) {
        var target = $input.attr('data-target');
        $('#' + target).val(clienteId);
        $input.val(nombreCliente);
        $input.closest('.hotel-cliente-autocomplete-wrap').find('.hotel-cliente-autocomplete-results').hide().empty();
    }

    function buscarClientes($input) {
        var term = $.trim($input.val());
        var $results = $input.closest('.hotel-cliente-autocomplete-wrap').find('.hotel-cliente-autocomplete-results');

        $('#' + $input.attr('data-target')).val('');

        if (term.length < 2) {
            $results.hide().empty();
            return;
        }

        $.get(rootUrl() + '/vtas_consultar_clientes', {
            campo_busqueda: $.isNumeric(term) ? 'numero_identificacion' : 'descripcion',
            texto_busqueda: term,
            enlace_tipo_boton: 'true',
            url_id: currentQueryParam('id')
        }).done(function (html) {
            $results.html(html).show();
        });
    }

    $(document).on('keyup', '.hotel-cliente-autocomplete-input', function () {
        var $input = $(this);
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function () {
            buscarClientes($input);
        }, 250);
    });

    $(document).on('click', '.hotel-cliente-autocomplete-results .list-group-item-cliente', function (event) {
        event.preventDefault();
        if (activeInput === null) {
            activeInput = $(this).closest('.hotel-cliente-autocomplete-wrap').find('.hotel-cliente-autocomplete-input');
        }

        setCliente(activeInput, $(this).attr('data-cliente_id'), $(this).attr('data-nombre_cliente'));
        activeInput = null;
    });

    $(document).on('mousedown', '.hotel-cliente-autocomplete-input', function () {
        activeInput = $(this);
    });

    $(document).on('click', '.hotel-cliente-autocomplete-results #btn_crear_nuevo_registro', function (event) {
        event.preventDefault();
        activeInput = $(this).closest('.hotel-cliente-autocomplete-wrap').find('.hotel-cliente-autocomplete-input');
        $('#hotelClienteAutocompleteModal').modal('show');
    });

    $(document).on('click', function (event) {
        if ($(event.target).closest('.hotel-cliente-autocomplete-wrap').length === 0) {
            $('.hotel-cliente-autocomplete-results').hide();
        }
    });

    $('#hotelClienteAutocompleteModal').on('show.bs.modal', function () {
        setModalFieldsEnabled(true);
    });

    $('#hotelClienteAutocompleteModal').on('hidden.bs.modal', function () {
        setModalFieldsEnabled(false);
    });

    $(document).ready(function () {
        setModalFieldsEnabled(false);
    });

    $(document).on('submit', '#hotel_cliente_autocomplete_form', function (event) {
        event.preventDefault();

        var $form = $(this);
        var $button = $('#hotel_cliente_autocomplete_save');
        var originalHtml = $button.html();

        $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            if (activeInput !== null && response && response.id) {
                setCliente(activeInput, response.id, response.text);
            }

            $('#hotelClienteAutocompleteModal').modal('hide');
            $form[0].reset();
        }).fail(function (xhr) {
            var message = 'No fue posible crear el huésped. Revise los campos requeridos.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            alert(message);
        }).always(function () {
            $button.prop('disabled', false).html(originalHtml);
        });
    });
})(jQuery);
