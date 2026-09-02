(function ($) {
    'use strict';

    function turnMarker(form) {
        return form.find('[data-turno-validation="1"]').first();
    }

    function selectedTurnId(form) {
        var field = form.find('[name="turno_operativo_id"]:not(:disabled)').first();
        return field.length ? $.trim(field.val() || '') : '';
    }

    function selectedState(form) {
        var input = form.find('.turno-operativo-ajax').first();
        var state = (input.attr('data-turno-state') || '').toUpperCase();
        if (state !== '') {
            return state;
        }
        var match = (input.val() || '').match(/\|\s*(ABIERTO|CERRADO|AUDITANDO|AUDITADO)\s*$/i);
        return match ? match[1].toUpperCase() : '';
    }

    function showError(form, message, fieldName) {
        var container = form.find('.turno-validation-error').first();
        if (!container.length) {
            container = $('<div class="alert alert-danger turno-validation-error" role="alert" style="margin: 10px 5px;"></div>');
            form.prepend(container);
        }
        container.text(message).show();

        var field = form.find('[name="' + (fieldName || 'turno_operativo_id') + '"]').filter(':visible').first();
        if (!field.length && fieldName === 'turno_operativo_id') {
            field = form.find('.turno-operativo-ajax').first();
        }
        if (field.length) {
            field.focus();
        }
        $('html, body').animate({ scrollTop: Math.max(container.offset().top - 120, 0) }, 150);
    }

    function hideError(form) {
        form.find('.turno-validation-error').hide().text('');
    }

    function localValidation(form, marker) {
        var turnId = selectedTurnId(form);
        if (marker.attr('data-turno-locked') === '1' && turnId === '') {
            return {
                message: 'No existe un turno abierto asignado al usuario cajero. Debe realizar la apertura antes de continuar.',
                field: 'turno_operativo_id'
            };
        }

        if (marker.attr('data-turno-domain-validating') === '1') {
            return { message: 'Espere a que termine la validación del turno operativo.', field: 'turno_operativo_id' };
        }
        if (turnId !== '' && marker.attr('data-turno-domain-valid') === '0') {
            return {
                message: marker.attr('data-turno-domain-message') || 'El turno seleccionado no es válido para esta transacción.',
                field: 'turno_operativo_id'
            };
        }

        var state = selectedState(form);
        var reason = $.trim(form.find('[name="turno_ajuste_motivo"]').first().val() || '');
        if ((state === 'CERRADO' || state === 'AUDITADO') && reason === '') {
            return {
                message: 'Debe indicar el motivo del ajuste para utilizar un turno cerrado o auditado.',
                field: 'turno_ajuste_motivo'
            };
        }
        if (state === 'AUDITANDO') {
            return { message: 'El turno seleccionado está en auditoría y no admite nuevas operaciones.', field: 'turno_operativo_id' };
        }
        return null;
    }

    function validateAndContinue(form, marker, continuation) {
        var localError = localValidation(form, marker);
        if (localError) {
            showError(form, localError.message, localError.field);
            return;
        }
        if (form.data('turno-validating')) {
            return;
        }

        form.data('turno-validating', true);
        hideError(form);
        $('#div_cargando').show();

        $.ajax({
            url: marker.attr('data-turno-validation-url'),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                modulo: marker.attr('data-turno-module'),
                turno_operativo_id: selectedTurnId(form),
                turno_ajuste_motivo: $.trim(form.find('[name="turno_ajuste_motivo"]').first().val() || '')
            }
        }).done(function (response) {
            if (response.turno_operativo_id && !selectedTurnId(form)) {
                form.find('input[type="hidden"][name="turno_operativo_id"]').first().val(response.turno_operativo_id);
            }
            form.removeData('turno-validating');
            $('#div_cargando').hide();
            continuation();
        }).fail(function (xhr) {
            var response = xhr.responseJSON || {};
            showError(
                form,
                response.message || 'No fue posible validar el turno operativo. Verifique la selección e intente nuevamente.',
                response.field || 'turno_operativo_id'
            );
        }).always(function () {
            form.removeData('turno-validating');
            $('#div_cargando').hide();
        });
    }

    $(document).on('input change', '[name="turno_ajuste_motivo"], .turno-operativo-ajax, [name="turno_operativo_id"]', function () {
        hideError($(this).closest('form'));
    });

    // Valida antes de que los formularios heredados serialicen o eliminen sus
    // líneas. triggerHandler continúa con su manejador original sin generar un
    // segundo clic del navegador ni deshabilitar Guardar.
    document.addEventListener('click', function (event) {
        var button = event.target;
        while (button && button !== document && button.id !== 'bs_boton_guardar' && button.id !== 'btn_guardar') {
            button = button.parentNode;
        }
        if (!button || button === document) {
            return;
        }

        var form = $(button).closest('form');
        var marker = turnMarker(form);
        if (!marker.length || form.data('turno-click-passed')) {
            form.removeData('turno-click-passed');
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        validateAndContinue(form, marker, function () {
            form.data('turno-click-passed', true);
            form.data('turno-submit-passed', true);
            $(button).triggerHandler('click');
            setTimeout(function () {
                form.removeData('turno-click-passed');
                form.removeData('turno-submit-passed');
            }, 0);
        });
    }, true);

    // Respaldo para Enter y envíos programáticos.
    $(document).on('submit', 'form', function (event) {
        var form = $(this);
        var marker = turnMarker(form);
        if (!marker.length) {
            return true;
        }
        if (form.data('turno-submit-passed')) {
            form.removeData('turno-submit-passed');
            return true;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        validateAndContinue(form, marker, function () {
            form.data('turno-submit-passed', true);
            form.trigger('submit');
        });
        return false;
    });
}(jQuery));
