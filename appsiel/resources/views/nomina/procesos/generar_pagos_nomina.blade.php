@extends('core.procesos.layout')

@section('titulo', 'Generar pagos de nómina')

@section('detalles')
    <p>
        Genera un único documento de Pago de CxP para los empleados seleccionados y salda
        las cuentas por pagar originadas por el documento de nómina.
    </p>
@endsection

@section('formulario')
    <?php
        $documentos = ['' => 'Seleccione...'];
        $listaDocumentos = App\Nomina\NomDocEncabezado::where('core_empresa_id', Auth::user()->empresa_id)
            ->where('estado', App\Nomina\NomDocEncabezado::ESTADO_CONTABILIZADO)
            ->orderBy('fecha', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($listaDocumentos as $doc) {
            $documentos[$doc->id] = $doc->get_label_documento() . ' - ' . $doc->descripcion . ' (' . $doc->fecha . ')';
        }

        $opcionesPago = ['' => 'Seleccione...'];
        $cajasPermitidas = App\Tesoreria\TesoCaja::get_cajas_permitidas()->pluck('id')->map(function ($id) { return (int) $id; })->toArray();
        $cuentasPermitidas = App\Tesoreria\TesoCuentaBancaria::get_cuentas_permitidas()->pluck('id')->map(function ($id) { return (int) $id; })->toArray();
        $medios = App\Tesoreria\TesoMedioRecaudo::with(['destinos.caja', 'destinos.cuenta_bancaria'])
            ->where('estado', 'Activo')
            ->orderBy('descripcion')
            ->get();
        foreach ($medios as $medio) {
            foreach ($medio->destinos->where('estado', 'Activo') as $destino) {
                $recurso = $destino->caja;
                $tipoRecurso = 'Caja';
                if (is_null($recurso)) {
                    $recurso = $destino->cuenta_bancaria;
                    $tipoRecurso = 'Cuenta bancaria';
                }
                if (is_null($recurso) || (int) $recurso->core_empresa_id !== (int) Auth::user()->empresa_id || $recurso->estado !== 'Activo') {
                    continue;
                }
                if ($tipoRecurso === 'Caja' && !in_array((int) $recurso->id, $cajasPermitidas, true)) {
                    continue;
                }
                if ($tipoRecurso === 'Cuenta bancaria' && !in_array((int) $recurso->id, $cuentasPermitidas, true)) {
                    continue;
                }
                $opcionesPago[$medio->id . '|' . $destino->id] = $medio->descripcion . ' — ' . $tipoRecurso . ': ' . $recurso->descripcion;
            }
        }
    ?>

    <div class="row" id="div_formulario">
        <div class="col-md-12">
            <div class="marco_formulario">
                <div class="container-fluid">
                    <h4>Parámetros del pago</h4>
                    <hr>
                    @if ((int) config('nomina.tercero_id_salarios_por_pagar') > 0)
                        <div class="alert alert-info">
                            La configuración actual consolida salarios por pagar en un tercero general.
                            Para que las próximas contabilizaciones creen una CxP por empleado, establezca
                            <b>Tercero salarios por pagar</b> en cero antes de contabilizar la nómina.
                        </div>
                    @endif
                    @if (count($documentos) === 1)
                        <div class="alert alert-warning">
                            No hay documentos de nómina contabilizados disponibles. Primero debe contabilizar el documento para que existan las cuentas por pagar.
                        </div>
                    @endif
                    @if (count($opcionesPago) === 1)
                        <div class="alert alert-danger">
                            No hay medios de pago con una caja o cuenta bancaria activa asociada para este usuario.
                            Configure los destinos en el catálogo <b>Medios de recaudo</b> de Tesorería.
                        </div>
                    @endif
                    {{ Form::open(['url' => 'nomina/procesos/pagos/previsualizar', 'id' => 'formulario_pago_nomina']) }}
                        <div class="row" style="padding:5px;">
                            <label class="control-label col-sm-4"><b>*Documento de nómina:</b></label>
                            <div class="col-sm-8">
                                {{ Form::select('nom_doc_encabezado_id', $documentos, '', ['class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="row" style="padding:5px;">
                            <label class="control-label col-sm-4"><b>*Fecha de pago:</b></label>
                            <div class="col-sm-8">
                                {{ Form::date('fecha_pago', date('Y-m-d'), ['class' => 'form-control', 'id' => 'fecha_pago', 'required' => 'required', 'max' => date('Y-m-d')]) }}
                            </div>
                        </div>
                        <div class="row" style="padding:5px;">
                            <label class="control-label col-sm-4"><b>*Medio y origen del pago:</b></label>
                            <div class="col-sm-8">
                                {{ Form::select('opcion_pago', $opcionesPago, '', ['class' => 'form-control', 'id' => 'opcion_pago', 'required' => 'required']) }}
                                <span class="help-block">El origen determina la caja o cuenta bancaria y su cuenta contable.</span>
                            </div>
                        </div>
                        <div class="row" style="padding:5px; text-align:center;">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info" id="btn_previsualizar_pago" {{ count($documentos) === 1 || count($opcionesPago) === 1 ? 'disabled' : '' }}>
                                    <i class="fa fa-eye"></i> Previsualizar
                                </button>
                                <span class="pago-nomina-spinner" style="display:none; margin-left:10px;">
                                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                                </span>
                            </div>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="resultado_pago_nomina" style="margin-top:15px;"></div>
@endsection

@section('javascripts')
<script type="text/javascript">
$(document).ready(function () {
    function moneda(valor) {
        return (parseFloat(valor) || 0).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function escapar(texto) {
        return $('<div>').text(texto || '').html();
    }

    function mostrarCargando(activo) {
        $('.pago-nomina-spinner').toggle(activo);
        $('#btn_previsualizar_pago, #btn_pagar_nomina').prop('disabled', activo);
    }

    function nuevaSolicitud() {
        return Date.now().toString(36) + '-' + Math.random().toString(36).substr(2, 16) + '-' + Math.random().toString(36).substr(2, 16);
    }

    function actualizarSeleccion() {
        var cantidad = $('.empleado-pago:checked').length;
        var total = 0;
        $('.empleado-pago:checked').each(function () {
            total += parseFloat($(this).data('saldo')) || 0;
        });
        $('#cantidad_seleccionados').text(cantidad);
        $('#total_seleccionado').text(moneda(total));
        $('#btn_pagar_nomina').prop('disabled', cantidad === 0);
        var disponibles = $('.empleado-pago').length;
        $('#seleccionar_todos_empleados').prop('checked', disponibles > 0 && cantidad === disponibles);
    }

    $('#formulario_pago_nomina').on('submit', function (event) {
        event.preventDefault();
        var opcion = ($('#opcion_pago').val() || '').split('|');
        if (!$('#nom_doc_encabezado_id').val()) {
            alert('Seleccione un documento de nómina.');
            return;
        }
        if (!$('#fecha_pago').val()) {
            alert('Seleccione la fecha de pago.');
            return;
        }
        if (opcion.length !== 2 || !opcion[0] || !opcion[1]) {
            alert('Seleccione el medio y origen del pago.');
            return;
        }

        mostrarCargando(true);
        $('#resultado_pago_nomina').html('');
        $.ajax({
            url: $(this).attr('action'),
            type: 'post',
            dataType: 'html',
            data: {
                _token: '{{ csrf_token() }}',
                nom_doc_encabezado_id: $('#nom_doc_encabezado_id').val()
            }
        }).done(function (html) {
            $('#resultado_pago_nomina').html(html).hide().fadeIn(300);
            $('#pago_nomina_documento_id').val($('#nom_doc_encabezado_id').val());
            $('#pago_nomina_fecha').val($('#fecha_pago').val());
            $('#pago_nomina_medio_id').val(opcion[0]);
            $('#pago_nomina_destino_id').val(opcion[1]);
            $('#pago_nomina_token').val(nuevaSolicitud());
            actualizarSeleccion();
        }).fail(function (xhr) {
            $('#resultado_pago_nomina').html(xhr.responseText || '<div class="alert alert-danger">No fue posible cargar la previsualización.</div>');
        }).always(function () {
            mostrarCargando(false);
        });
    });

    $('#nom_doc_encabezado_id, #fecha_pago, #opcion_pago').on('change', function () {
        $('#resultado_pago_nomina').html('');
    });

    $(document).on('change', '#seleccionar_todos_empleados', function () {
        $('.empleado-pago').prop('checked', $(this).is(':checked'));
        actualizarSeleccion();
    });

    $(document).on('change', '.empleado-pago', actualizarSeleccion);

    $(document).on('submit', '#form_generar_pago_nomina', function (event) {
        event.preventDefault();
        var cantidad = $('.empleado-pago:checked').length;
        if (cantidad === 0) {
            alert('Seleccione al menos un empleado.');
            return;
        }
        var total = $('#total_seleccionado').text();
        if (!confirm('Se generará un solo Pago de CxP para ' + cantidad + ' empleado(s), por $' + total + '. ¿Desea continuar?')) {
            return;
        }

        mostrarCargando(true);
        $('#alerta_resultado_pago').remove();
        $.ajax({
            url: $(this).attr('action'),
            type: 'post',
            dataType: 'json',
            data: $(this).serialize()
        }).done(function (respuesta) {
            var html = '<div class="alert alert-success" id="alerta_resultado_pago"><b>' + escapar(respuesta.documento) + '</b><br>' + escapar(respuesta.mensaje) +
                '<br><a class="btn btn-success btn-sm" style="margin-top:8px" target="_blank" href="' + respuesta.url + '"><i class="fa fa-external-link"></i> Ver pago en Tesorería</a></div>';
            $('#resultado_pago_nomina').prepend(html);
            $('.empleado-pago, #seleccionar_todos_empleados, #btn_pagar_nomina').prop('disabled', true);
        }).fail(function (xhr) {
            var respuesta = xhr.responseJSON || {};
            var mensaje = respuesta.mensaje || 'No fue posible generar el pago.';
            $('#resultado_pago_nomina').prepend('<div class="alert alert-danger" id="alerta_resultado_pago">' + $('<div>').text(mensaje).html() + '</div>');
        }).always(function () {
            mostrarCargando(false);
        });
    });
});
</script>
@endsection
