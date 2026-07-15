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
        <?php
            $encabezado_tabla_doc_soporte = array_merge($encabezado_tabla, ['Último error']);
        ?>
        {{ Form::bsTableHeader($encabezado_tabla_doc_soporte) }}
        <tbody>
            @foreach ($registros as $fila)
                <tr id="fila_doc_{{ $fila->id }}" data-documento-id="{{ $fila->id }}">
                    <td>
                        <button type="button" class="btn btn-info btn-sm btn-enviar-individual" data-documento-id="{{ $fila->id }}">
                            <i class="fa fa-send"></i> Enviar
                        </button>
                    </td>
                    <td> {{ $fila->fecha }} </td>
                    <td> {{ $fila->get_value_to_show() }} </td>
                    <td> {{ $fila->empleado->tercero->descripcion }} </td>
                    <td class="col-estado"> {{ $fila->estado }} </td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm btn-ver-ultimo-error" data-documento-id="{{ $fila->id }}">
                            <i class="fa fa-exclamation-triangle"></i> Ver error
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="modal_ultimo_error_envio" tabindex="-1" role="dialog" aria-labelledby="modal_ultimo_error_envio_label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal_ultimo_error_envio_label">Último error de envío</h4>
            </div>
            <div class="modal-body" id="modal_ultimo_error_envio_body">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Consultando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnEnviarTodos = document.getElementById('btn_enviar_todos');
        var modalBody = document.getElementById('modal_ultimo_error_envio_body');
        var modalTitle = document.getElementById('modal_ultimo_error_envio_label');

        function escapeHtml(texto) {
            if (texto === null || texto === undefined) {
                return '';
            }

            return String(texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function mostrarModalUltimoError(documentoId) {
            modalTitle.textContent = 'Último error de envío';
            modalBody.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Consultando...</div>';
            $('#modal_ultimo_error_envio').modal('show');

            fetch("{{ url('nom_electronica_ultimo_error_envio') }}/" + documentoId, {
                method: 'GET',
                headers: {
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
            }).then(function (data) {
                var resultado = data.resultado || {};
                var mensajes = resultado.mensajes || [];
                var mensajesHtml = mensajes.length ? '<ul style="margin-bottom: 0;">' + mensajes.map(function (mensaje) {
                    return '<li>' + escapeHtml(mensaje) + '</li>';
                }).join('') + '</ul>' : '<span class="text-muted">Sin mensaje DIAN registrado.</span>';

                modalTitle.textContent = 'Último error de envío - ' + (data.documento || '');
                modalBody.innerHTML =
                    '<table class="table table-bordered table-condensed">' +
                        '<tbody>' +
                            '<tr><th style="width: 180px;">ID resultado</th><td>' + escapeHtml(resultado.id) + '</td></tr>' +
                            '<tr><th>Documento</th><td>' + escapeHtml(data.documento) + '</td></tr>' +
                            '<tr><th>Fecha</th><td>' + escapeHtml(resultado.fecha) + '</td></tr>' +
                            '<tr><th>Cod. respuesta</th><td>' + escapeHtml(resultado.codigo) + '</td></tr>' +
                            '<tr><th>DIAN Status</th><td>' + escapeHtml(resultado.dian_status) + '</td></tr>' +
                            '<tr><th>Email Status</th><td>' + escapeHtml(resultado.email_status) + '</td></tr>' +
                            '<tr><th>CUNE</th><td>' + escapeHtml(resultado.cune) + '</td></tr>' +
                            '<tr><th>Registrado</th><td>' + escapeHtml(resultado.created_at) + '</td></tr>' +
                            '<tr><th>Mensaje respuesta DIAN</th><td>' + mensajesHtml + '</td></tr>' +
                        '</tbody>' +
                    '</table>' +
                    '<label>Obj. JSON Enviado:</label>' +
                    '<pre style="max-height: 260px; overflow: auto; white-space: pre-wrap;">' + escapeHtml(resultado.objeto_json_enviado) + '</pre>';
            }).catch(function (error) {
                modalBody.innerHTML = '<div class="alert alert-warning" style="margin-bottom: 0;">' + escapeHtml(error.message || 'No fue posible consultar el último error de envío.') + '</div>';
            });
        }

        document.addEventListener('click', function (e) {
            var btnError = e.target.closest('.btn-ver-ultimo-error');
            if (!btnError) {
                return;
            }

            e.preventDefault();
            mostrarModalUltimoError(btnError.getAttribute('data-documento-id'));
        });

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

        function recalcularDocumento(documentoId) {
            return fetch("{{ url('nom_electronica_recalcular_doc_soporte') }}/" + documentoId, {
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

        function recalcularYEnviarDocumento(documentoId, fila) {
            return recalcularDocumento(documentoId).then(function () {
                if (fila) {
                    var estado = fila.querySelector('.col-estado');
                    if (estado) {
                        estado.textContent = 'Enviando...';
                    }
                }
                return enviarDocumento(documentoId);
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
                estado.textContent = 'Recalculando...';
            }

            recalcularYEnviarDocumento(documentoId, fila)
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
            var btnEnviarIndividual = e.target.closest('.btn-enviar-individual');
            if (!btnEnviarIndividual) {
                return;
            }

            e.preventDefault();

            if (procesando) {
                return;
            }

            var documentoId = btnEnviarIndividual.getAttribute('data-documento-id');
            var fila = document.getElementById('fila_doc_' + documentoId);

            if (!fila) {
                mostrarMensaje('warning', 'No fue posible identificar la fila del documento.');
                return;
            }

            ocultarMensaje();
            bloquearInterfaz(true);
            fila.classList.add('warning');
            var estado = fila.querySelector('.col-estado');
            if (estado) {
                estado.textContent = 'Recalculando...';
            }

            recalcularYEnviarDocumento(documentoId, fila)
                .then(function (data) {
                    enviados++;
                    pendientes = Math.max(pendientes - 1, 0);
                    actualizarContador();
                    marcarFilaEnviada(fila);
                    var tiempo = data.elapsed_seconds != null ? ' (' + data.elapsed_seconds + 's)' : '';
                    mostrarMensaje('success', 'Documento enviado correctamente.' + tiempo);
                })
                .catch(function (error) {
                    var mensajeError = error.message || 'Error no controlado durante el envio.';
                    if (error.elapsed_seconds != null) {
                        mensajeError += ' (' + error.elapsed_seconds + 's)';
                    }
                    marcarFilaError(fila, 'Pendiente - Error');
                    mostrarMensaje('warning', 'Doc. ' + documentoId + ': ' + mensajeError);
                })
                .then(function () {
                    bloquearInterfaz(false);
                    if (pendientes === 0 && btnEnviarTodos) {
                        btnEnviarTodos.style.display = 'none';
                    }
                });
        });

        document.addEventListener('click', function (e) {
            if (procesando && e.target.closest('.btn-ver-ultimo-error')) {
                e.preventDefault();
            }
        });
    });
</script>
