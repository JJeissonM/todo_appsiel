<h4>{{ $model->modelo->descripcion }}</h4>
<div id="div_datos_doc_soporte">

    @if (count($registros) > 0)
        <div style="margin-bottom: 15px;">
            <button type="button" class="btn btn-primary btn-sm" id="btn_enviar_todos" data-ids='{{ $arr_ids_documentos_sin_enviar }}'>
                <i class="fa fa-send" id="icono_btn_enviar_todos"></i> <span id="texto_btn_enviar_todos">Enviar todos</span>
            </button>
            <span class="label label-default" id="contador_envio_masivo" style="margin-left: 10px;">Enviados: 0 | Pendientes: {{ count($registros) }}</span>
        </div>
    @endif

    <div id="mensaje_envio_masivo" style="display: none; margin-bottom: 15px;"></div>

    <table class="table table-bordered table-striped table-hover" id="tbDatos">
        {{ Form::bsTableHeader($encabezado_tabla) }}
        <tbody>
            @foreach ($registros as $fila)
                <tr id="fila_doc_{{ $fila->id }}" data-documento-id="{{ $fila->id }}">
                    <td>
                        <a href="{{ url('/nom_electronica_enviar_documentos') . '/[' . $fila->id . ']' }}" class="btn btn-info btn-sm btn-enviar-individual"> <i class="fa fa-send"></i> Enviar </a>
                    </td>
                    <td> {{ $fila->fecha }} </td>
                    <td> {{ $fila->get_value_to_show() }} </td>
                    <td> {{ $fila->empleado->tercero->descripcion }} </td>
                    <td class="col-estado"> {{ $fila->estado }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnEnviarTodos = document.getElementById('btn_enviar_todos');
        if (!btnEnviarTodos) {
            return;
        }

        var icono = document.getElementById('icono_btn_enviar_todos');
        var texto = document.getElementById('texto_btn_enviar_todos');
        var contador = document.getElementById('contador_envio_masivo');
        var mensaje = document.getElementById('mensaje_envio_masivo');
        var tabla = document.getElementById('tbDatos');
        var enviados = 0;
        var pendientes = tabla.querySelectorAll('tbody tr').length;
        var procesando = false;

        function actualizarContador() {
            contador.textContent = 'Enviados: ' + enviados + ' | Pendientes: ' + pendientes;
        }

        function bloquearInterfaz(estado) {
            procesando = estado;
            btnEnviarTodos.disabled = estado;

            var botones = document.querySelectorAll('.btn-enviar-individual');
            Array.prototype.forEach.call(botones, function (boton) {
                if (estado) {
                    boton.classList.add('disabled');
                    boton.setAttribute('aria-disabled', 'true');
                } else {
                    boton.classList.remove('disabled');
                    boton.removeAttribute('aria-disabled');
                }
            });

            if (estado) {
                icono.classList.remove('fa-send');
                icono.classList.add('fa-spinner', 'fa-spin');
                texto.textContent = 'Enviando...';
            } else {
                icono.classList.remove('fa-spinner', 'fa-spin');
                icono.classList.add('fa-send');
                texto.textContent = 'Enviar todos';
            }
        }

        function mostrarMensaje(tipo, textoHtml) {
            mensaje.className = 'alert alert-' + tipo;
            mensaje.innerHTML = textoHtml;
            mensaje.style.display = 'block';
        }

        function ocultarMensaje() {
            mensaje.style.display = 'none';
            mensaje.innerHTML = '';
            mensaje.className = '';
        }

        function getIdsPendientes() {
            var filas = tabla.querySelectorAll('tbody tr[data-documento-id]');
            var ids = [];

            Array.prototype.forEach.call(filas, function (fila) {
                ids.push(fila.getAttribute('data-documento-id'));
            });

            return ids;
        }

        function enviarDocumento(documentoId) {
            return fetch("{{ url('nom_electronica_enviar_documento_ajax') }}/" + documentoId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().catch(function () {
                    return {};
                }).then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            });
        }

        function marcarFilaError(fila, textoEstado) {
            fila.classList.remove('warning');
            fila.classList.add('danger');
            var estado = fila.querySelector('.col-estado');
            if (estado) {
                estado.textContent = textoEstado;
            }
        }

        function marcarFilaEnviada(fila) {
            fila.classList.remove('warning');
            fila.classList.add('success');
            var estado = fila.querySelector('.col-estado');
            if (estado) {
                estado.textContent = 'Enviado';
            }

            setTimeout(function () {
                if (fila.parentNode) {
                    fila.parentNode.removeChild(fila);
                }
            }, 800);
        }

        function procesarSiguiente(ids, indice, errores) {
            if (indice >= ids.length) {
                bloquearInterfaz(false);

                if (errores.length) {
                    mostrarMensaje('warning', 'Proceso finalizado. Enviados: ' + enviados + '. Pendientes: ' + pendientes + '. Errores: ' + errores.join('<br>'));
                } else {
                    mostrarMensaje('success', 'Proceso finalizado. Todos los documentos pendientes fueron enviados.');
                }

                if (pendientes === 0) {
                    btnEnviarTodos.style.display = 'none';
                }
                return;
            }

            var documentoId = ids[indice];
            var fila = document.getElementById('fila_doc_' + documentoId);

            if (!fila) {
                procesarSiguiente(ids, indice + 1, errores);
                return;
            }

            fila.classList.add('warning');
            var estado = fila.querySelector('.col-estado');
            if (estado) {
                estado.textContent = 'Enviando...';
            }

            enviarDocumento(documentoId)
                .then(function () {
                    enviados++;
                    pendientes = Math.max(pendientes - 1, 0);
                    actualizarContador();
                    marcarFilaEnviada(fila);
                })
                .catch(function (error) {
                    var mensajeError = error.message || 'Error no controlado durante el envio.';
                    errores.push('Doc. ' + documentoId + ': ' + mensajeError);
                    marcarFilaError(fila, 'Pendiente - Error');
                })
                .then(function () {
                    procesarSiguiente(ids, indice + 1, errores);
                });
        }

        actualizarContador();

        btnEnviarTodos.addEventListener('click', function () {
            if (procesando) {
                return;
            }

            var ids = getIdsPendientes();
            if (!ids.length) {
                mostrarMensaje('warning', 'No hay documentos pendientes para enviar.');
                return;
            }

            enviados = 0;
            pendientes = tabla.querySelectorAll('tbody tr').length;
            actualizarContador();
            ocultarMensaje();
            bloquearInterfaz(true);
            procesarSiguiente(ids, 0, []);
        });

        document.addEventListener('click', function (e) {
            if (procesando && e.target.closest('.btn-enviar-individual')) {
                e.preventDefault();
            }
        });
    });
</script>
