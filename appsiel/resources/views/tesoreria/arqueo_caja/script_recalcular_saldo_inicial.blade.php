@if(\App\Tesoreria\ArqueoCaja::usuario_puede_recalcular_saldo_inicial(auth()->user()))
    $('#btn_recalcular_saldo_inicial').on('click', function (event) {
        event.preventDefault();

        var $boton = $(this);
        var $mensaje = $('#mensaje_recalcular_saldo_inicial');
        var fecha = $('#fecha').val();
        var cajaId = $('#teso_caja_id').val();

        if (!fecha || !cajaId) {
            alert('Debe seleccionar la fecha y la caja antes de recalcular el saldo inicial.');
            return;
        }

        $boton.prop('disabled', true);
        $boton.find('i').addClass('fa-spin');
        $mensaje.removeClass('text-danger text-success').addClass('text-muted').text('Calculando...');

        $.get({!! json_encode(url('tesoreria/arqueo_caja_recalcular_saldo_inicial')) !!}, {
            fecha: fecha,
            teso_caja_id: cajaId,
            fecha_hora_apertura: $('#fecha_hora_apertura').val()
        }).done(function (respuesta) {
            $('#base').val(respuesta.saldo_inicial);
            calcular_total_sistema();
            calcular_total_saldo();
            $mensaje.removeClass('text-muted text-danger').addClass('text-success').text(respuesta.message);
        }).fail(function (xhr) {
            var mensaje = 'No fue posible recalcular el saldo inicial.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            $mensaje.removeClass('text-muted text-success').addClass('text-danger').text(mensaje);
            alert(mensaje);
        }).always(function () {
            $boton.prop('disabled', false);
            $boton.find('i').removeClass('fa-spin');
        });
    });
@endif
