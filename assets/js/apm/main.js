// main.js - Cliente WebSocket para Appsiel Print Manager (APM)

(function (global) {
    const DEFAULT_WS_URL = 'ws://localhost:7000/websocket/';

    function getWsUrl() {
        if (global.APM_CONFIG && global.APM_CONFIG.url) {
            return global.APM_CONFIG.url;
        }
        const input = document.getElementById('apm_ws_url');
        if (input && input.value) {
            return input.value;
        }
        return DEFAULT_WS_URL;
    }

    function APMClient(options) {
        const opts = options || {};
        this.socket = null;
        this.queue = [];
        this.logger = null;
        this.autoReconnect = opts.autoReconnect !== false;
    }

    APMClient.prototype.setLogger = function (loggerFn) {
        this.logger = typeof loggerFn === 'function' ? loggerFn : null;
    };

    APMClient.prototype.log = function (message, type) {
        if (this.logger) {
            this.logger(message, type || 'info');
        }
        if (type === 'error') {
            console.error(message);
        }
    };

    APMClient.prototype.connect = function () {
        if (this.socket && (this.socket.readyState === WebSocket.OPEN || this.socket.readyState === WebSocket.CONNECTING)) {
            return;
        }

        const wsUrl = getWsUrl();
        try {
            this.socket = new WebSocket(wsUrl);
        } catch (error) {
            this.log('Error al crear WebSocket APM: ' + error.message, 'error');
            return;
        }

        this.log('Conectando a APM: ' + wsUrl, 'info');

        this.socket.onopen = () => {
            this.log('Conexion APM establecida.', 'success');
            this.flushQueue();
        };

        this.socket.onmessage = (event) => {
            this.log('Mensaje APM: ' + event.data, 'info');
        };

        this.socket.onclose = (event) => {
            const reason = event.reason || 'Conexion cerrada.';
            this.log('Conexion APM cerrada. Codigo: ' + event.code + ', Razon: ' + reason, 'warning');
            this.socket = null;
        };

        this.socket.onerror = (error) => {
            this.log('Error en WebSocket APM. Revisa la consola para detalles.', 'error');
            console.error('APM WebSocket Error:', error);
        };
    };

    APMClient.prototype.flushQueue = function () {
        if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
            return;
        }
        while (this.queue.length > 0) {
            const message = this.queue.shift();
            this.socket.send(message);
        }
    };

    APMClient.prototype.send = function (payload) {
        const message = (typeof payload === 'string') ? payload : JSON.stringify(payload);

        if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
            this.queue.push(message);
            this.connect();
            return false;
        }

        this.socket.send(message);
        return true;
    };

    APMClient.prototype.isConnected = function () {
        return this.socket && this.socket.readyState === WebSocket.OPEN;
    };

    global.APM_CLIENT = global.APM_CLIENT || new APMClient();

    // Plantillas JSON para cada tipo de documento
    global.APM_TEMPLATES = {
        ticket: {
            "JobId": "TICKET-001",
            "StationId": "CAJA_1",
            "PrinterId": "printHambuger",
            "DocumentType": "ticket_venta",
            "Document": {
                "company": { "Name": "Supermercado Demo", "Nit": "900123456", "Address": "Calle Principal #123", "Phone": "555-1234" },
                "sale": {
                    "Number": "FV-1001",
                    "Date": new Date().toISOString(),
                    "Items": [
                        { "Name": "Leche Entera", "Qty": 2, "UnitPrice": 2500, "Total": 5000 },
                        { "Name": "Pan Tajado", "Qty": 1, "UnitPrice": 3500, "Total": 3500 }
                    ],
                    "Subtotal": 8500,
                    "IVA": 0,
                    "Total": 8500
                },
                "footer": ["Gracias por su compra"]
            }
        },
        comanda: {
            "JobId": "CMD-001",
            "StationId": "COCINA",
            "PrinterId": "printHambuger",
            "DocumentType": "comanda",
            "Document": {
                "order": {
                    "COPY": "ORIGINAL",
                    "Number": "CMD-001",
                    "Table": "Mesa 5",
                    "Waiter": "Carlos",
                    "Date": new Date().toISOString(),
                    "RestaurantName": "Restaurant Tremendo Chuzo",
                    "Items": [
                        { "Name": "Hamburguesa Doble", "Qty": 1, "Notes": "Sin cebolla" },
                        { "Name": "Papas Fritas", "Qty": 1, "Notes": "Extra crocantes" },
                        { "Name": "Gaseosa", "Qty": 2, "Notes": "" }
                    ],
                    "GeneratedDate": new Date().toISOString(),
                    "CreatedBy": "Jose Reyes"
                },
                "Detail": "Todoterreno sin pioa, full arepa sin maoz y la salchipapa sin lechuga"
            }
        },
        factura: {
            "JobId": "FE-2025",
            "StationId": "ADMIN",
            "PrinterId": "printHambuger",
            "DocumentType": "factura_electronica",
            "Document": {
                "header": { "Title": "FACTURA ELECTRoNICA DE VENTA", "Number": "FE-2025" },
                "customer": { "Name": "Empresa Cliente S.A.S", "Nit": "800.111.222-3", "Address": "Av. Empresarial 55" },
                "totals": { "Subtotal": 100000, "Tax": 19000, "Total": 119000 },
                "cufe": "abc1234567890def..."
            }
        },
        sticker: {
            "JobId": "STICKER-001",
            "StationId": "ALMACEN",
            "PrinterId": "printHambuger",
            "DocumentType": "sticker_codigo_barras",
            "Document": {
                "stickers": [
                    {
                        "ProductName": "Manzana Roja",
                        "ProductCode": "MZN-001",
                        "BarcodeValue": "1234567890128",
                        "BarcodeType": "EAN13"
                    },
                    {
                        "ProductName": "Pera Verde",
                        "ProductCode": "PER-002",
                        "BarcodeValue": "0987654321093",
                        "BarcodeType": "EAN13"
                    },
                    {
                        "ProductName": "Uvas Importadas",
                        "ProductCode": "UVA-003",
                        "BarcodeValue": "9876543210987",
                        "BarcodeType": "EAN13"
                    },
                    {
                        "ProductName": "Banano Nacional",
                        "ProductCode": "BAN-004",
                        "BarcodeValue": "1122334455667",
                        "BarcodeType": "EAN13"
                    }
                ]
            }
        }
    };

    // Demo UI opcional (solo si existen los elementos en el DOM)
    function initDemoUI() {
        const statusSpan = document.getElementById('status');
        const messagesDiv = document.getElementById('messages');
        const sendPayloadButton = document.getElementById('sendPayloadButton');
        const reconnectButton = document.getElementById('reconnectButton');

        if (!statusSpan || !messagesDiv || !sendPayloadButton || !reconnectButton) {
            return;
        }

        let activePayloadId = 'payloadTicket';

        function logMessage(message, type) {
            const p = document.createElement('p');
            p.className = 'log-' + (type || 'info');
            p.textContent = message;
            messagesDiv.appendChild(p);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        global.APM_CLIENT.setLogger(function (message, type) {
            logMessage(message, type);
            if (type === 'success') {
                statusSpan.textContent = 'Conectado';
                statusSpan.style.color = 'green';
            }
            if (type === 'warning') {
                statusSpan.textContent = 'Desconectado';
                statusSpan.style.color = 'red';
            }
            if (type === 'info') {
                statusSpan.textContent = 'Intentando conectar...';
                statusSpan.style.color = 'orange';
            }
            if (type === 'error') {
                statusSpan.textContent = 'Error';
                statusSpan.style.color = 'red';
            }
        });

        global.APM_CLIENT.connect();

        document.getElementById('payloadTicket').value = JSON.stringify(global.APM_TEMPLATES.ticket, null, 4);
        document.getElementById('payloadComanda').value = JSON.stringify(global.APM_TEMPLATES.comanda, null, 4);
        document.getElementById('payloadFactura').value = JSON.stringify(global.APM_TEMPLATES.factura, null, 4);
        document.getElementById('payloadSticker').value = JSON.stringify(global.APM_TEMPLATES.sticker, null, 4);

        global.openTab = function (tabName) {
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            const clickedBtn = Array.from(buttons).find(b => b.dataset.tab === tabName);
            if (clickedBtn) {
                clickedBtn.classList.add('active');
            }

            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const selectedId = 'payload' + tabName.charAt(0).toUpperCase() + tabName.slice(1);
            document.getElementById(selectedId).classList.add('active');
            activePayloadId = selectedId;
        };

        sendPayloadButton.addEventListener('click', function (event) {
            event.preventDefault();
            const activeTextarea = document.getElementById(activePayloadId);
            if (activeTextarea) {
                global.APM_CLIENT.send(activeTextarea.value);
            } else {
                logMessage('Error: No se encontro el orea de texto activa.', 'error');
            }
        });

        reconnectButton.addEventListener('click', function () {
            if (global.APM_CLIENT.socket) {
                global.APM_CLIENT.socket.close(1000, 'ReConexion solicitada por el usuario');
            }
            global.APM_CLIENT.connect();
        });

        global.openTab('ticket');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDemoUI);
    } else {
        initDemoUI();
    }
})(window);
