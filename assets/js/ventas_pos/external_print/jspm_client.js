(function (global) {
    var startedManager = null;

    function start() {
        if (!global.JSPM) {
            return;
        }
        var manager = global.JSPM.JSPrintManager;
        manager.auto_reconnect = true;
        if (manager.WS) {
            manager.WS.autoReconnect = true;
            return;
        }
        if (startedManager === manager) {
            return;
        }
        startedManager = manager;
        try {
            // La biblioteca administra la reconexión. No abrir otro socket en cada clic.
            Promise.resolve(manager.start()).catch(function () {});
        } catch (error) {
            startedManager = null;
        }
    }

    function waitUntilReady() {
        if (!global.JSPM) {
            return Promise.reject(new Error('No se pudo cargar JSPrintManager. Recargue la página e intente nuevamente.'));
        }

        start();
        var deadline = Date.now() + client.connectionTimeoutMs;
        return new Promise(function (resolve, reject) {
            function check() {
                var status = global.JSPM.JSPrintManager.websocket_status;
                if (status === global.JSPM.WSStatus.Open) {
                    resolve();
                } else if (status === global.JSPM.WSStatus.Blocked) {
                    reject(new Error('JSPrintManager bloqueó este sitio. Autorice el acceso a este sitio en JSPM Client App e intente nuevamente.'));
                } else if (Date.now() >= deadline) {
                    var message = status === global.JSPM.WSStatus.WaitingForUserResponse
                        ? 'JSPrintManager está esperando autorización. Acepte el acceso a este sitio en JSPM Client App e intente nuevamente.'
                        : 'No se pudo conectar con JSPrintManager. Verifique que JSPM Client App esté ejecutándose en este computador y permita el acceso a este sitio.';
                    reject(new Error(message));
                } else {
                    setTimeout(check, 100);
                }
            }
            check();
        });
    }

    function send(printerName, commands) {
        if (!printerName || !String(printerName).trim()) {
            return Promise.reject(new Error('Configure la impresora de cocina en Ventas POS: impresora_cocina_por_defecto.'));
        }
        return waitUntilReady().then(function () {
            return new Promise(function (resolve, reject) {
                var timer = setTimeout(function () {
                    reject(new Error('No se recibió confirmación de JSPrintManager. Revise si la comanda salió en la impresora antes de volver a imprimir.'));
                }, client.sendTimeoutMs);
                Promise.resolve().then(function () {
                    var job = new global.JSPM.ClientPrintJob();
                    job.clientPrinter = new global.JSPM.InstalledPrinter(printerName);
                    job.binaryPrinterCommands = commands;
                    return job.sendToClient();
                }).then(function (response) {
                    clearTimeout(timer);
                    resolve(response);
                }, function (error) {
                    clearTimeout(timer);
                    reject(error instanceof Error ? error : new Error('JSPrintManager no confirmó el envío. Revise la impresora antes de reintentar. ' + String(error || '')));
                });
            });
        });
    }

    var client = { start: start, waitUntilReady: waitUntilReady, send: send, connectionTimeoutMs: 12000, sendTimeoutMs: 30000 };
    global.APPSIEL_JSPM = client;
})(window);
