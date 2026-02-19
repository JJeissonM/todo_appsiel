// client.js - Cliente WebSocket para Appsiel Print Manager (APM)

((global) => {
    const DEFAULT_WS_URL = 'ws://localhost:7000/websocket/';

    const getWsUrl = () => {
        if (global.APM_CONFIG && global.APM_CONFIG.url) {
            return String(global.APM_CONFIG.url).trim();
        }

        const input = document.getElementById('apm_ws_url');
        if (input && input.value) {
            return String(input.value).trim();
        }

        return DEFAULT_WS_URL;
    };

    const getWsUrlCandidates = () => {
        const configured = getWsUrl();
        const urls = [
            configured,
            DEFAULT_WS_URL,
            'ws://localhost:7000/websocket',
            'ws://127.0.0.1:7000/websocket/',
            'ws://127.0.0.1:7000/websocket'
        ];

        return urls.filter((value, index, array) => value && array.indexOf(value) === index);
    };

    class APMClient {
        constructor(options = {}) {
            this.socket = null;
            this.queue = [];
            this.logger = null;
            this.autoReconnect = options.autoReconnect !== false;
            this.reconnectTimer = null;
            this.wsUrlIndex = 0;
            this.pendingJobs = {};
        }

        setLogger(loggerFn) {
            this.logger = typeof loggerFn === 'function' ? loggerFn : null;
        }

        log(message, type = 'info') {
            if (this.logger) {
                this.logger(message, type);
            }
            if (type === 'error') {
                console.error(message);
            }
        }

        connect() {
            if (this.socket && (this.socket.readyState === WebSocket.OPEN || this.socket.readyState === WebSocket.CONNECTING)) {
                return;
            }

            if (this.reconnectTimer) {
                clearTimeout(this.reconnectTimer);
                this.reconnectTimer = null;
            }

            const candidates = getWsUrlCandidates();
            if (this.wsUrlIndex >= candidates.length) {
                this.wsUrlIndex = 0;
            }

            const wsUrl = candidates[this.wsUrlIndex] || DEFAULT_WS_URL;

            try {
                this.socket = new WebSocket(wsUrl);
            } catch (error) {
                this.log(`Error al crear WebSocket APM: ${error.message}`, 'error');
                this.wsUrlIndex = (this.wsUrlIndex + 1) % candidates.length;
                return;
            }

            this.log(`Conectando a APM: ${wsUrl}`, 'info');

            this.socket.onopen = () => {
                this.wsUrlIndex = 0;
                this.log('Conexion APM establecida.', 'success');
                this.flushQueue();
            };

            this.socket.onmessage = (event) => {
                this.log(`Mensaje APM: ${event.data}`, 'info');
                this.resolvePendingJob(event.data);
            };

            this.socket.onclose = (event) => {
                const reason = event.reason || 'Conexion cerrada.';
                this.log(`Conexion APM cerrada. Codigo: ${event.code}, Razon: ${reason}`, 'warning');
                this.socket = null;

                Object.keys(this.pendingJobs).forEach((jobId) => {
                    const pending = this.pendingJobs[jobId];
                    clearTimeout(pending.timer);
                    pending.reject({
                        JobId: jobId,
                        Status: 'ERROR',
                        ErrorMessage: 'Conexion APM cerrada antes de recibir respuesta.'
                    });
                    delete this.pendingJobs[jobId];
                });

                if (this.autoReconnect) {
                    this.wsUrlIndex = (this.wsUrlIndex + 1) % candidates.length;
                    this.reconnectTimer = setTimeout(() => this.connect(), 2000);
                }
            };

            this.socket.onerror = (error) => {
                this.log('Error en WebSocket APM. Revisa la consola para detalles.', 'error');
                console.error('APM WebSocket Error:', error);
            };
        }

        resolvePendingJob(rawMessage) {
            let parsed;
            try {
                parsed = JSON.parse(rawMessage);
            } catch (e) {
                return;
            }

            if (!parsed || !parsed.JobId) {
                return;
            }

            const pending = this.pendingJobs[parsed.JobId];
            if (!pending) {
                return;
            }

            clearTimeout(pending.timer);
            delete this.pendingJobs[parsed.JobId];

            const status = String(parsed.Status || '').toUpperCase();
            if (status === 'ERROR') {
                pending.reject(parsed);
                return;
            }

            pending.resolve(parsed);
        }

        flushQueue() {
            if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
                return;
            }

            while (this.queue.length > 0) {
                const message = this.queue.shift();
                this.socket.send(message);
            }
        }

        send(payload) {
            const message = typeof payload === 'string' ? payload : JSON.stringify(payload);

            if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
                this.queue.push(message);
                this.connect();
                return false;
            }

            this.socket.send(message);
            return true;
        }

        sendAndWait(payload, timeoutMs = 12000) {
            let payloadObj = payload;

            if (typeof payloadObj === 'string') {
                try {
                    payloadObj = JSON.parse(payloadObj);
                } catch (e) {
                    return Promise.reject({
                        Status: 'ERROR',
                        ErrorMessage: 'Payload JSON invalido para APM.'
                    });
                }
            }

            if (!payloadObj || typeof payloadObj !== 'object') {
                return Promise.reject({
                    Status: 'ERROR',
                    ErrorMessage: 'Payload invalido para APM.'
                });
            }

            if (!payloadObj.JobId) {
                payloadObj.JobId = `APM-${Date.now()}`;
            }

            if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
                this.connect();
                return Promise.reject({
                    JobId: payloadObj.JobId,
                    Status: 'ERROR',
                    ErrorMessage: 'APM no conectado o en reconexion.'
                });
            }

            return new Promise((resolve, reject) => {
                const timer = setTimeout(() => {
                    delete this.pendingJobs[payloadObj.JobId];
                    reject({
                        JobId: payloadObj.JobId,
                        Status: 'ERROR',
                        ErrorMessage: 'APM no respondio a tiempo para el trabajo.'
                    });
                }, timeoutMs);

                this.pendingJobs[payloadObj.JobId] = { resolve, reject, timer };

                try {
                    this.socket.send(JSON.stringify(payloadObj));
                } catch (error) {
                    clearTimeout(timer);
                    delete this.pendingJobs[payloadObj.JobId];
                    reject({
                        JobId: payloadObj.JobId,
                        Status: 'ERROR',
                        ErrorMessage: error.message || 'No fue posible enviar el trabajo a APM.'
                    });
                }
            });
        }

        isConnected() {
            if (!this.socket || this.socket.readyState === WebSocket.CLOSED) {
                this.connect();
                return false;
            }

            return this.socket.readyState === WebSocket.OPEN;
        }
    }

    global.APM_CLIENT = global.APM_CLIENT || new APMClient();

    global.APM_TEMPLATES = global.APM_TEMPLATES || {
        ticket: {
            JobId: 'TICKET-001',
            StationId: 'CAJA_1',
            PrinterId: 'printHambuger',
            DocumentType: 'ticket_venta',
            Document: {
                company: { Name: 'Supermercado Demo', Nit: '900123456', Address: 'Calle Principal #123', Phone: '555-1234' },
                sale: {
                    Number: 'FV-1001',
                    Date: new Date().toISOString(),
                    Items: [
                        { Name: 'Leche Entera', Qty: 2, UnitPrice: 2500, Total: 5000 },
                        { Name: 'Pan Tajado', Qty: 1, UnitPrice: 3500, Total: 3500 }
                    ],
                    Subtotal: 8500,
                    IVA: 0,
                    Total: 8500
                },
                footer: ['Gracias por su compra']
            }
        },
        comanda: {
            JobId: 'CMD-001',
            StationId: 'COCINA',
            PrinterId: 'printHambuger',
            DocumentType: 'comanda',
            Document: {
                order: {
                    COPY: 'ORIGINAL',
                    Number: 'CMD-001',
                    Table: 'Mesa 5',
                    Waiter: 'Carlos',
                    Date: new Date().toISOString(),
                    RestaurantName: 'Restaurant Tremendo Chuzo',
                    Items: [
                        { Name: 'Hamburguesa Doble', Qty: 1, Notes: 'Sin cebolla' },
                        { Name: 'Papas Fritas', Qty: 1, Notes: 'Extra crocantes' },
                        { Name: 'Gaseosa', Qty: 2, Notes: '' }
                    ],
                    GeneratedDate: new Date().toISOString(),
                    CreatedBy: 'Jose Reyes'
                },
                Detail: 'Todoterreno sin pioa, full arepa sin maoz y la salchipapa sin lechuga'
            }
        },
        factura: {
            JobId: 'FE-2025',
            StationId: 'ADMIN',
            PrinterId: 'printHambuger',
            DocumentType: 'factura_electronica',
            Document: {
                header: { Title: 'FACTURA ELECTRoNICA DE VENTA', Number: 'FE-2025' },
                customer: { Name: 'Empresa Cliente S.A.S', Nit: '800.111.222-3', Address: 'Av. Empresarial 55' },
                totals: { Subtotal: 100000, Tax: 19000, Total: 119000 },
                cufe: 'abc1234567890def...'
            }
        },
        sticker: {
            JobId: 'STICKER-001',
            StationId: 'ALMACEN',
            PrinterId: 'printHambuger',
            DocumentType: 'sticker_codigo_barras',
            Document: {
                stickers: [
                    { ProductName: 'Manzana Roja', ProductCode: 'MZN-001', BarcodeValue: '1234567890128', BarcodeType: 'EAN13' },
                    { ProductName: 'Pera Verde', ProductCode: 'PER-002', BarcodeValue: '0987654321093', BarcodeType: 'EAN13' },
                    { ProductName: 'Uvas Importadas', ProductCode: 'UVA-003', BarcodeValue: '9876543210987', BarcodeType: 'EAN13' },
                    { ProductName: 'Banano Nacional', ProductCode: 'BAN-004', BarcodeValue: '1122334455667', BarcodeType: 'EAN13' }
                ]
            }
        }
    };
})(window);
