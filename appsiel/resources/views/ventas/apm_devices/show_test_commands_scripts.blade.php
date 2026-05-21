@if($registro->device_type == 'printer')
    <script src="{{ asset('assets/js/apm/client.js') }}"></script>
    <script>
        (function (global) {
            function notify(type, title, text) {
                if (global.Swal) {
                    global.Swal.fire({
                        icon: type,
                        title: title,
                        text: text
                    });
                    return;
                }

                alert(title + '\n' + text);
            }

            function showWaiting() {
                if (global.Swal) {
                    global.Swal.fire({
                        title: 'Enviando comando',
                        text: 'Esperando respuesta del servidor APM.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        onOpen: function () {
                            global.Swal.showLoading();
                        }
                    });
                }
            }

            function setButtonsDisabled(disabled) {
                $('.apm-device-test-command').prop('disabled', disabled);
            }

            function getApmClient() {
                if (!global.APM_CLIENT || typeof global.APM_CLIENT.sendDirectCommandFromEndpoint !== 'function') {
                    notify('error', 'APM no disponible', 'No fue posible inicializar el cliente APM.');
                    return null;
                }

                return global.APM_CLIENT;
            }

            $(document).on('click', '.apm-device-test-command', function () {
                var endpoint = $(this).data('endpoint');
                var client = getApmClient();

                if (!client || typeof client.sendDirectCommandFromEndpoint !== 'function') {
                    notify('error', 'APM no disponible', 'No fue posible preparar el cliente APM.');
                    return;
                }

                setButtonsDisabled(true);
                showWaiting();

                client.sendDirectCommandFromEndpoint(endpoint, 12000)
                    .then(function (response) {
                        var apmResponse = response.apm_response || {};
                        var status = apmResponse.Status ? ' Estado: ' + apmResponse.Status + '.' : '';
                        notify('success', 'Comando ejecutado', response.command + ' ejecutado en ' + response.printer_id + '.' + status);
                    })
                    .catch(function (error) {
                        var message = error && error.ErrorMessage
                            ? error.ErrorMessage
                            : (error && error.message ? error.message : 'No fue posible preparar o enviar el comando APM.');
                        notify('error', 'Error', message);
                    })
                    .then(function () {
                        setButtonsDisabled(false);
                    });
            });
        })(window);
    </script>
@endif
