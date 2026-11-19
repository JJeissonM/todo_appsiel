@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <p>Seleccione el nuevo grupo y pulse Confirmar para guardar el cambio de esa cuenta.</p>
    <div class="table-responsive" id="table_content">
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Código</th><th>Cuenta</th><th>Grupo</th><th>Confirmación</th></tr>
            </thead>
            <tbody>
                @foreach ($cuentas as $fila)
                    <tr class="fila-reasignacion" data-url="{{ url('reasignar_grupos_cuentas_save/'.$fila->id) }}" data-grupo-original="{{ (int)$fila->contab_cuenta_grupo_id }}">
                        <td>{{ $fila->codigo }}</td>
                        <td>{{ $fila->descripcion }}</td>
                        <td>
                            <input type="text" id="grupo-cuenta-{{ $fila->id }}"
                                class="form-control grupo-cuenta-busqueda" autocomplete="off"
                                data-clase="{{ $fila->contab_cuenta_clase_id }}"
                                value="{{ isset($grupos_por_clase[$fila->contab_cuenta_clase_id][$fila->contab_cuenta_grupo_id]) ? $grupos_por_clase[$fila->contab_cuenta_clase_id][$fila->contab_cuenta_grupo_id] : '' }}"
                                placeholder="Buscar grupo…" aria-label="Grupo de la cuenta {{ $fila->codigo }}">
                            <input type="hidden" class="grupo-cuenta" value="{{ (int)$fila->contab_cuenta_grupo_id }}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary confirmar-grupo" disabled>Confirmar</button>
                            <span class="estado-grupo" role="status" aria-live="polite"></span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <style>
        .grupos-cuentas-sugerencias { max-height: 280px; overflow-y: auto; overflow-x: hidden; z-index: 10000; }
        .grupos-cuentas-sugerencias .ui-menu-item-wrapper { white-space: normal; overflow-wrap: break-word; }
    </style>
    <script>
        $(function () {
            var gruposPorClase = {!! json_encode($grupos_por_clase, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
            var opcionesPorClase = {};
            $.each(gruposPorClase, function (clase, grupos) {
                opcionesPorClase[clase] = [];
                $.each(grupos, function (id, label) {
                    opcionesPorClase[clase].push({id: String(id), label: label, value: label});
                });
            });
            $('.grupo-cuenta-busqueda').each(function () {
                var input = $(this);
                input.autocomplete({
                    minLength: 0,
                    appendTo: 'body',
                    source: function (request, response) {
                        response($.ui.autocomplete.filter(opcionesPorClase[input.attr('data-clase')] || [], request.term).slice(0, 30));
                    },
                    focus: function () { return false; },
                    select: function (event, ui) {
                        input.val(ui.item.label);
                        input.closest('.fila-reasignacion').find('.grupo-cuenta').val(ui.item.id).trigger('change');
                        return false;
                    }
                }).on('input', function () {
                    input.closest('.fila-reasignacion').find('.grupo-cuenta').val('').trigger('change');
                }).on('focus', function () {
                    input.autocomplete('search', '');
                });
                input.autocomplete('widget').addClass('grupos-cuentas-sugerencias');
            });

            var csrfToken = {!! json_encode(csrf_token()) !!};

            $('#table_content').on('change', '.grupo-cuenta', function () {
                var row = $(this).closest('.fila-reasignacion');
                var changed = $(this).val() !== '' && String($(this).val()) !== String(row.attr('data-grupo-original'));
                row.toggleClass('warning', changed);
                row.find('.confirmar-grupo').prop('disabled', !changed);
                row.find('.estado-grupo').removeClass('text-success text-danger').text(changed ? 'Cambio pendiente de confirmar.' : '');
            });

            $('#table_content').on('click', '.confirmar-grupo', function () {
                var button = $(this);
                var row = button.closest('.fila-reasignacion');
                var select = row.find('.grupo-cuenta');
                var status = row.find('.estado-grupo');
                var groupId = select.val();
                if (button.prop('disabled') || !groupId || String(groupId) === String(row.attr('data-grupo-original'))) {
                    return;
                }
                button.prop('disabled', true).text('Guardando…');
                row.find('.grupo-cuenta-busqueda').autocomplete('close').prop('disabled', true);
                status.removeClass('text-success text-danger').text('');
                $.ajax({
                    url: row.attr('data-url'),
                    type: 'POST',
                    dataType: 'json',
                    data: {_token: csrfToken, grupo_id: groupId, grupo_anterior_id: row.attr('data-grupo-original')}
                }).done(function (response) {
                    row.attr('data-grupo-original', response.grupo_id).removeClass('warning');
                    status.addClass('text-success').text(response.message);
                }).fail(function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message;
                    status.addClass('text-danger').text(message || 'No se pudo guardar. Intente nuevamente.');
                }).always(function () {
                    row.find('.grupo-cuenta-busqueda').prop('disabled', false);
                    button.text('Confirmar').prop('disabled', !select.val() || String(select.val()) === String(row.attr('data-grupo-original')));
                });
            });
        });
    </script>
@endsection
