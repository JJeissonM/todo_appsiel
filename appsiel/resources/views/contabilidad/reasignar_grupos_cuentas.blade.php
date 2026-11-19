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
                            <select id="grupo-cuenta-{{ $fila->id }}" class="form-control grupo-cuenta" aria-label="Grupo de la cuenta {{ $fila->codigo }}">
                                <option value="">Seleccione un grupo</option>
                                @foreach (isset($grupos_por_clase[$fila->contab_cuenta_clase_id]) ? $grupos_por_clase[$fila->contab_cuenta_clase_id] : [] as $grupo_id => $descripcion)
                                    <option value="{{ $grupo_id }}" {{ (int)$grupo_id === (int)$fila->contab_cuenta_grupo_id ? 'selected' : '' }}>{{ $descripcion }}</option>
                                @endforeach
                            </select>
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
    <script>
        $(function () {
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
                select.prop('disabled', true);
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
                    select.prop('disabled', false);
                    button.text('Confirmar').prop('disabled', String(select.val()) === String(row.attr('data-grupo-original')));
                });
            });
        });
    </script>
@endsection
