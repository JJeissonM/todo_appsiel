(function (global) {
    function getSelectedJobIds() {
        var ids = [];
        $("input[type=checkbox]:checked").each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function isPrintAction(url) {
        return /apm_print_jobs\/id_fila\/mark_pending/.test(String(url || ''));
    }

    function loadApmClient() {
        if (global.APM_CLIENT && typeof global.APM_CLIENT.reprintQueuedJob === 'function') {
            return Promise.resolve(global.APM_CLIENT);
        }

        var existing = document.querySelector('script[src*="assets/js/apm/client.js"]');

        if (existing) {
            return waitForApmClient();
        }

        return new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = (typeof url_raiz !== 'undefined' ? url_raiz : '') + '/assets/js/apm/client.js?aux=' + Date.now();
            script.onload = function () {
                waitForApmClient().then(resolve).catch(reject);
            };
            script.onerror = function () {
                reject('No fue posible cargar assets/js/apm/client.js.');
            };
            document.body.appendChild(script);
        });
    }

    function waitForApmClient() {
        return new Promise(function (resolve, reject) {
            var attempts = 0;
            var timer = global.setInterval(function () {
                attempts++;
                if (global.APM_CLIENT && typeof global.APM_CLIENT.reprintQueuedJob === 'function') {
                    global.clearInterval(timer);
                    resolve(global.APM_CLIENT);
                } else if (attempts >= 40) {
                    global.clearInterval(timer);
                    reject('No fue posible inicializar el cliente APM.');
                }
            }, 100);
        });
    }

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

    function printJob(jobId) {
        if (global.Swal) {
            global.Swal.fire({
                title: 'Imprimiendo...',
                text: 'Conectando con Appsiel Print Manager.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                onOpen: function () {
                    global.Swal.showLoading();
                }
            });
        }

        loadApmClient()
            .then(function (client) {
                if (typeof client.connect === 'function') {
                    client.connect();
                }

                return client.reprintQueuedJob(jobId, 30000, {
                    retryOnly: true
                });
            })
            .then(function (response) {
                notify('success', 'Impresion enviada', (response.CopyLabel || 'Trabajo APM') + ' impresa correctamente.');
                global.setTimeout(function () {
                    global.location.reload();
                }, 900);
            })
            .catch(function (error) {
                var message = typeof error === 'string'
                    ? error
                    : (error && error.ErrorMessage ? error.ErrorMessage : 'No fue posible enviar la impresion a APM.');

                notify('error', 'Error al imprimir', message);
            });
    }

    var originalBotonElement = global.botonElement;

    global.botonElement = function (url) {
        if (!isPrintAction(url)) {
            if (typeof originalBotonElement === 'function') {
                return originalBotonElement(url);
            }
            return;
        }

        var ids = getSelectedJobIds();
        if (!ids.length) {
            notify('warning', 'Alerta', 'Debe seleccionar un trabajo de impresion APM.');
            return;
        }

        if (ids.length > 1) {
            notify('warning', 'Alerta', 'Seleccione solo un trabajo para imprimir.');
            return;
        }

        printJob(ids[0]);
    };
})(window);
