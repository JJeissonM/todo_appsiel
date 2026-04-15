$(document).ready(function () {

    // Activar select2 en todos los selects del mapeo
    $('.select2-mapeo').select2({
        placeholder: '-- Seleccionar producto --',
        allowClear: true,
        width: '100%'
    });

    // Confirmar antes de guardar si hay ítems sin mapear
    $('#btn_guardar_mapeo').on('click', function (e) {
        e.preventDefault();

        var sinMapear = 0;
        $('#form_mapeo_xml select').each(function () {
            if (!$(this).val()) sinMapear++;
        });

        if (sinMapear > 0) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: sinMapear + ' producto(s) sin vincular. ¿Desea guardar de todas formas?',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#form_mapeo_xml').submit();
                }
            });
        } else {
            $('#form_mapeo_xml').submit();
        }
    });
});
