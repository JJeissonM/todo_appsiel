(function ($) {
    'use strict';
    $(function () {
        var form = $('#cert-filtros'), enviando = false;
        var base = form.attr('action').replace(/\/previsualizar$/, '');
        function opciones() {
            var periodo = $('#cert-periodo').val(), ica = $('#cert-impuesto').val() === 'ICA';
            $('#cert-bimestre').toggle(periodo === 'bimestral');
            $('#cert-fechas').toggle(periodo === 'fechas').find('input').prop('disabled', periodo !== 'fechas').prop('required', periodo === 'fechas');
            $('#cert-ica').toggle(ica).find('input').prop('disabled', !ica);
            $('#cert-ica input:not([type=checkbox])').prop('required', ica);
            var alcance = $('#cert-alcance').val();
            $('#cert-tercero').toggle(alcance === 'tercero').find('select').prop('disabled', alcance !== 'tercero').prop('required', alcance === 'tercero');
            $('#cert-grupo').toggle(alcance === 'grupo').find('select').prop('disabled', alcance !== 'grupo').prop('required', alcance === 'grupo');
        }
        $('#cert-periodo,#cert-impuesto,#cert-alcance').on('change', opciones);
        opciones();
        function error(xhr) { return xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo completar la operación. Revise su sesión e intente nuevamente.'; }
        form.on('change input', function () { if (!enviando) { $('#cert-resultados').empty(); } });
        form.on('submit', function (event) {
            event.preventDefault();
            if (enviando) { return; }
            $('#cert-error').hide(); $('#cert-resultados').empty();
            $('#cert-previsualizar').prop('disabled', true); $('#cert-cargando').text('Consultando operaciones…');
            $.ajax({url: form.attr('action'), type: 'POST', data: new FormData(form[0]), processData: false, contentType: false})
                .done(function (html) { $('#cert-resultados').html(html); })
                .fail(function (xhr) { $('#cert-error').text(error(xhr)).show(); })
                .always(function () { $('#cert-previsualizar').prop('disabled', false); $('#cert-cargando').text(''); });
        });
        function enviar(token, estado) {
            estado.text('Enviando…');
            return $.ajax({url: base + '/' + encodeURIComponent(token) + '/enviar', type: 'POST', dataType: 'json', data: {_token: form.find('[name=_token]').val()}})
                .done(function (r) { estado.text(r.mensaje).removeClass('text-danger').addClass('text-success'); })
                .fail(function (xhr) { estado.text(error(xhr)).removeClass('text-success').addClass('text-danger'); });
        }
        $(document).on('change', '#cert-seleccionar-todos', function () {
            if (!enviando) { $('.cert-seleccion:not(:disabled)').prop('checked', this.checked); }
        });
        $(document).on('click', '#cert-descargar-lote', function () {
            if (enviando) { return; }
            var seleccion = $('.cert-seleccion:checked:not(:disabled)').closest('tr');
            if (!seleccion.length || seleccion.length > 200) { $('#cert-progreso').text('Seleccione entre 1 y 200 certificados.'); return; }
            var descarga = $('<form>', {method: 'POST', target: '_blank', action: base + '/descargar'}).hide();
            descarga.append($('<input>', {type: 'hidden', name: '_token', value: form.find('[name=_token]').val()}));
            seleccion.each(function () { descarga.append($('<input>', {type: 'hidden', name: 'tokens[]', value: $(this).attr('data-token')})); });
            descarga.appendTo(document.body).trigger('submit').remove();
        });
        $(document).on('click', '#cert-enviar-todos', function () {
            if (enviando) { return; }
            var filas = $('.cert-seleccion:checked:not(:disabled)').closest('tr').toArray();
            if (!filas.length) { $('#cert-progreso').text('Seleccione al menos un proveedor.'); return; }
            if (!window.confirm('¿Enviar por correo los ' + filas.length + ' certificados seleccionados?')) { return; }
            enviando = true; var i = 0, enviados = 0, fallidos = 0;
            $('#cert-enviar-todos,#cert-previsualizar').prop('disabled', true);
            function siguiente() {
                $('#cert-progreso').text('Enviados: ' + enviados + ' · No enviados: ' + fallidos + ' · Restantes: ' + (filas.length - i));
                if (i >= filas.length) { enviando = false; $('#cert-enviar-todos,#cert-previsualizar').prop('disabled', false); return; }
                var fila = $(filas[i++]);
                enviar(fila.attr('data-token'), fila.find('.cert-estado'))
                    .done(function () { enviados++; fila.find('.cert-seleccion').prop('checked', false).prop('disabled', true); })
                    .fail(function () { fallidos++; }).always(siguiente);
            }
            siguiente();
        });
        $('.cert-enviar-historial').on('click', function () {
            var boton = $(this);
            if (!window.confirm('¿Enviar este certificado al correo del proveedor registrado en la previsualización?')) { return; }
            boton.prop('disabled', true);
            enviar(boton.attr('data-token'), boton.siblings('.cert-historial-estado')).fail(function () { boton.prop('disabled', false); });
        });
    });
})(jQuery);
