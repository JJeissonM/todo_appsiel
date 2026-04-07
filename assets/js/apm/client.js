// client.js - Cliente WebSocket para Appsiel Print Manager (APM)

((global) => {
    const DEFAULT_WS_URL = 'ws://localhost:7000/websocket/';
    const DEFAULT_TIMEOUT_MS = 12000;
    const QUEUE_BUTTON_ID = 'apm-queue-toggle';
    const QUEUE_BADGE_ID = 'apm-queue-badge';
    const QUEUE_STYLE_ID = 'apm-queue-style';

    const safeJsonParse = (value, fallback) => {
        try {
            return JSON.parse(value);
        } catch (error) {
            return fallback;
        }
    };

    const cloneData = (data) => JSON.parse(JSON.stringify(data || {}));

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
            this.logger = null;
            this.autoReconnect = options.autoReconnect !== false;
            this.reconnectTimer = null;
            this.wsUrlIndex = 0;
            this.pendingJobs = {};
            this.dispatchQueue = [];
            this.activeDispatch = null;
            this.processingQueueJobs = {};
            this.processingDocumentKeys = {};
            this.queueItems = [];
            this.canRetireQueue = false;
            this.queueModalOpen = false;
            this.uiInitialized = false;
            this.queueSyncInterval = null;
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
                this.refreshQueueUI();
            };

            this.socket.onmessage = (event) => {
                this.log(`Mensaje APM: ${event.data}`, 'info');
                this.resolvePendingJob(event.data);
            };

            this.socket.onclose = (event) => {
                const reason = event.reason || 'Conexion cerrada.';
                const candidatesOnClose = getWsUrlCandidates();

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

                this.refreshQueueUI();

                if (this.autoReconnect) {
                    this.wsUrlIndex = (this.wsUrlIndex + 1) % candidatesOnClose.length;
                    this.reconnectTimer = setTimeout(() => this.connect(), 2000);
                }
            };

            this.socket.onerror = (error) => {
                this.log('Error en WebSocket APM. Revisa la consola para detalles.', 'error');
                console.error('APM WebSocket Error:', error);
            };
        }

        resolvePendingJob(rawMessage) {
            const parsed = safeJsonParse(rawMessage, null);
            if (!parsed || !parsed.JobId) {
                return;
            }

            const pending = this.pendingJobs[parsed.JobId];
            if (!pending) {
                return;
            }

            clearTimeout(pending.timer);
            delete this.pendingJobs[parsed.JobId];

            if (String(parsed.Status || '').toUpperCase() === 'ERROR') {
                pending.reject(parsed);
                return;
            }

            pending.resolve(parsed);
        }

        sendAndWait(payload, timeoutMs = DEFAULT_TIMEOUT_MS) {
            let payloadObj = payload;

            if (typeof payloadObj === 'string') {
                payloadObj = safeJsonParse(payloadObj, null);
                if (!payloadObj) {
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

        buildDocumentKey(documentMeta = {}) {
            const coreTipoTransaccionId = parseInt(documentMeta.core_tipo_transaccion_id || 0, 10) || 0;
            const coreTipoDocAppId = parseInt(documentMeta.core_tipo_doc_app_id || 0, 10) || 0;
            const consecutivo = parseInt(documentMeta.consecutivo || 0, 10) || 0;
            const documentType = String(documentMeta.document_type || '').trim();

            if (!coreTipoTransaccionId || !coreTipoDocAppId || !consecutivo || !documentType) {
                return '';
            }

            return [coreTipoTransaccionId, coreTipoDocAppId, consecutivo, documentType].join(':');
        }

        getDispatchPromise(options = {}) {
            const queueJobId = options.queueJobId ? String(options.queueJobId) : '';
            const documentKey = options.documentKey ? String(options.documentKey) : '';

            if (queueJobId && this.processingQueueJobs[queueJobId]) {
                return this.processingQueueJobs[queueJobId];
            }

            if (documentKey && this.processingDocumentKeys[documentKey]) {
                return this.processingDocumentKeys[documentKey];
            }

            return null;
        }

        registerDispatchPromise(promise, options = {}) {
            const queueJobId = options.queueJobId ? String(options.queueJobId) : '';
            const documentKey = options.documentKey ? String(options.documentKey) : '';

            if (queueJobId) {
                this.processingQueueJobs[queueJobId] = promise;
            }

            if (documentKey) {
                this.processingDocumentKeys[documentKey] = promise;
            }

            const cleanup = () => {
                if (queueJobId && this.processingQueueJobs[queueJobId] === promise) {
                    delete this.processingQueueJobs[queueJobId];
                }

                if (documentKey && this.processingDocumentKeys[documentKey] === promise) {
                    delete this.processingDocumentKeys[documentKey];
                }
            };

            promise.then(cleanup, cleanup);
            return promise;
        }

        queueDispatch(options = {}) {
            const payload = options.payload || null;
            const timeoutMs = options.timeoutMs || DEFAULT_TIMEOUT_MS;

            return new Promise((resolve, reject) => {
                this.dispatchQueue.push({
                    payload: payload,
                    timeoutMs: timeoutMs,
                    resolve: resolve,
                    reject: reject
                });

                this.processDispatchQueue();
            });
        }

        processDispatchQueue() {
            if (this.activeDispatch || !this.dispatchQueue.length) {
                return;
            }

            const nextDispatch = this.dispatchQueue.shift();
            this.activeDispatch = nextDispatch;
            this.waitForSocketReady()
                .then(() => this.sendAndWait(nextDispatch.payload, nextDispatch.timeoutMs))
                .then((response) => {
                    nextDispatch.resolve(response);
                })
                .catch((error) => {
                    nextDispatch.reject(error);
                })
                .then(() => {
                    this.activeDispatch = null;
                    this.processDispatchQueue();
                });
        }

        waitForSocketReady(timeoutMs = 5000) {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                return Promise.resolve();
            }

            this.connect();

            return new Promise((resolve, reject) => {
                const startedAt = Date.now();

                const poll = () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        resolve();
                        return;
                    }

                    if (Date.now() - startedAt >= timeoutMs) {
                        reject({
                            Status: 'ERROR',
                            ErrorMessage: 'APM no conectado o en reconexion.'
                        });
                        return;
                    }

                    global.setTimeout(poll, 150);
                };

                poll();
            });
        }

        completeQueuedJob(job, apmResponse) {
            return this.request('POST', `apm_print_queue/${job.id}/mark_printed`, {})
                .then(() => {
                    this.syncQueueItems();
                    return Object.assign({}, apmResponse, {
                        QueueJobId: job.id,
                        CopyNumber: job.copy_number,
                        CopyLabel: job.copy_label
                    });
                });
        }

        failQueuedJob(job, apmError) {
            const errorWithQueueMeta = Object.assign({}, apmError, {
                QueueJobId: job.id,
                CopyNumber: job.copy_number,
                CopyLabel: job.copy_label
            });

            return this.request('POST', `apm_print_queue/${job.id}/mark_failed`, {
                error_message: apmError && apmError.ErrorMessage ? apmError.ErrorMessage : 'Error desconocido al imprimir con APM.'
            }).then(() => {
                this.syncQueueItems();
                throw errorWithQueueMeta;
            }, () => {
                this.syncQueueItems();
                throw errorWithQueueMeta;
            });
        }

        isConnected() {
            if (!this.socket || this.socket.readyState === WebSocket.CLOSED) {
                this.connect();
                return false;
            }

            return this.socket.readyState === WebSocket.OPEN;
        }

        getBaseUrl() {
            if (typeof global.url_raiz !== 'undefined' && global.url_raiz) {
                return String(global.url_raiz).replace(/\/$/, '');
            }

            return '';
        }

        getApiUrl(path) {
            const cleanPath = String(path || '').replace(/^\//, '');
            const baseUrl = this.getBaseUrl();
            return baseUrl ? `${baseUrl}/${cleanPath}` : `/${cleanPath}`;
        }

        getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                return meta.getAttribute('content');
            }

            const input = document.querySelector('input[name="_token"]');
            return input ? input.value : '';
        }

        request(method, path, data) {
            const url = this.getApiUrl(path);
            const payload = data || {};

            if (global.jQuery && typeof global.jQuery.ajax === 'function') {
                return new Promise((resolve, reject) => {
                    const ajaxOptions = {
                        url: url,
                        method: method,
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    };

                    if (method !== 'GET') {
                        ajaxOptions.data = JSON.stringify(payload);
                        ajaxOptions.contentType = 'application/json; charset=utf-8';
                    }

                    ajaxOptions.success = function (response) {
                            resolve(response);
                    };

                    ajaxOptions.error = function (xhr) {
                        const message = xhr && xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'No fue posible procesar la cola APM en el servidor.';

                        reject({
                            Status: 'ERROR',
                            ErrorMessage: message,
                            xhr: xhr
                        });
                    };

                    global.jQuery.ajax(ajaxOptions);
                });
            }

            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: method === 'GET' ? undefined : JSON.stringify(payload),
                credentials: 'same-origin'
            }).then((response) => {
                return response.json().then((json) => {
                    if (!response.ok) {
                        throw {
                            Status: 'ERROR',
                            ErrorMessage: json && json.message ? json.message : 'No fue posible procesar la cola APM en el servidor.'
                        };
                    }
                    return json;
                });
            });
        }

        syncQueueItems() {
            return this.request('GET', 'apm_print_queue')
                .then((response) => {
                    this.queueItems = response && response.data ? response.data : [];
                    this.canRetireQueue = !!(response && response.permissions && response.permissions.can_retire);
                    this.renderQueueButton();
                    this.renderQueueModalContent();
                    return this.queueItems;
                })
                .catch(() => {
                    this.queueItems = [];
                    this.canRetireQueue = false;
                    this.renderQueueButton();
                    this.renderQueueModalContent();
                    return [];
                });
        }

        enqueuePrintJob(options = {}) {
            const payload = options.payload || null;
            const documentMeta = options.documentMeta || {};
            const timeoutMs = options.timeoutMs || DEFAULT_TIMEOUT_MS;
            const documentKey = this.buildDocumentKey(documentMeta);
            const existingPromise = this.getDispatchPromise({ documentKey: documentKey });

            if (existingPromise) {
                return existingPromise;
            }

            const enqueuePromise = this.request('POST', 'apm_print_queue/prepare', {
                payload: payload,
                document_meta: documentMeta
            }).then((prepared) => {
                const job = prepared.job;
                const payloadToSend = prepared.payload;

                return this.queueDispatch({
                    payload: payloadToSend,
                    timeoutMs: timeoutMs,
                    queueJobId: job.id
                })
                    .then((apmResponse) => this.completeQueuedJob(job, apmResponse))
                    .catch((apmError) => {
                        return this.failQueuedJob(job, apmError);
                    });
            });

            return this.registerDispatchPromise(enqueuePromise, { documentKey: documentKey });
        }

        reprintQueuedJob(jobId, timeoutMs = DEFAULT_TIMEOUT_MS) {
            const existingPromise = this.getDispatchPromise({ queueJobId: jobId });
            if (existingPromise) {
                return existingPromise;
            }

            const reprintPromise = this.request('POST', `apm_print_queue/${jobId}/prepare_reprint`, {})
                .then((prepared) => {
                    const job = prepared.job;
                    return this.queueDispatch({
                        payload: prepared.payload,
                        timeoutMs: timeoutMs,
                        queueJobId: job.id
                    })
                        .then((apmResponse) => this.completeQueuedJob(job, apmResponse))
                        .catch((apmError) => {
                            return this.failQueuedJob(job, apmError);
                        });
                });

            return this.registerDispatchPromise(reprintPromise, { queueJobId: jobId });
        }

        retireQueuedJob(jobId) {
            return this.request('POST', `apm_print_queue/${jobId}/mark_retired`, {})
                .then((response) => {
                    this.syncQueueItems();
                    return response;
                });
        }

        ensureQueueUI() {
            if (this.uiInitialized || !document.body) {
                return;
            }

            this.uiInitialized = true;

            if (!document.getElementById(QUEUE_STYLE_ID)) {
                const style = document.createElement('style');
                style.id = QUEUE_STYLE_ID;
                style.textContent = `
                    #${QUEUE_BUTTON_ID} {
                        position: fixed;
                        right: 18px;
                        bottom: 18px;
                        z-index: 10050;
                        border: 0;
                        border-radius: 999px;
                        background: #574696;
                        color: #fff;
                        padding: 10px 16px;
                        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
                        display: none;
                    }
                    #${QUEUE_BUTTON_ID} .apm-queue-label {
                        margin-right: 8px;
                    }
                    #${QUEUE_BADGE_ID} {
                        display: inline-block;
                        min-width: 20px;
                        height: 20px;
                        padding: 0 6px;
                        border-radius: 999px;
                        background: #d9534f;
                        font-size: 12px;
                        line-height: 20px;
                        text-align: center;
                    }
                    .apm-queue-item {
                        padding: 10px 0;
                        border-bottom: 1px solid #e5e5e5;
                        text-align: left;
                    }
                    .apm-queue-item:last-child {
                        border-bottom: 0;
                    }
                    .apm-queue-item-title {
                        font-weight: 600;
                        margin-bottom: 4px;
                    }
                    .apm-queue-item-meta,
                    .apm-queue-item-error {
                        font-size: 12px;
                        color: #666;
                        margin-bottom: 6px;
                    }
                    .apm-queue-item-error {
                        color: #b94a48;
                    }
                `;
                document.head.appendChild(style);
            }

            if (!document.getElementById(QUEUE_BUTTON_ID)) {
                const button = document.createElement('button');
                button.id = QUEUE_BUTTON_ID;
                button.type = 'button';
                button.innerHTML = '<span class="apm-queue-label">Cola APM</span><span id="' + QUEUE_BADGE_ID + '">0</span>';
                button.addEventListener('click', () => this.openQueueModal());
                document.body.appendChild(button);
            }

            this.renderQueueButton();
            this.syncQueueItems();

            if (!this.queueSyncInterval) {
                this.queueSyncInterval = global.setInterval(() => this.syncQueueItems(), 15000);
            }
        }

        renderQueueButton() {
            const button = document.getElementById(QUEUE_BUTTON_ID);
            const badge = document.getElementById(QUEUE_BADGE_ID);

            if (!button || !badge) {
                return;
            }

            badge.textContent = String(this.queueItems.length || 0);
            button.style.display = this.queueItems.length ? 'inline-flex' : 'none';
        }

        buildQueueModalHtml() {
            if (!this.queueItems.length) {
                return '<p style="margin:0;">No hay documentos pendientes en la cola de APM.</p>';
            }

            return this.queueItems.map((item) => {
                const label = item.document_label || `${item.document_type} ${item.consecutivo}`;
                const retireButtonHtml = this.canRetireQueue
                    ? `<button type="button" class="btn btn-default btn-xs apm-retire-btn" data-job-id="${item.id}" style="margin-left:6px;">Retirar</button>`
                    : '';

                return `
                    <div class="apm-queue-item">
                        <div class="apm-queue-item-title">${label}</div>
                        <div class="apm-queue-item-meta">
                            Copia: ${item.copy_label}<br>
                        </div>
                        <div class="apm-queue-item-error">${item.last_error || 'Pendiente de reimpresion manual.'}</div>
                        <button type="button" class="btn btn-primary btn-xs apm-reprint-btn" data-job-id="${item.id}">Reimprimir</button>
                        ${retireButtonHtml}
                    </div>
                `;
            }).join('');
        }

        renderQueueModalContent() {
            if (!this.queueModalOpen || !global.Swal || !global.Swal.isVisible()) {
                return;
            }

            const container = global.Swal.getHtmlContainer();
            if (!container) {
                return;
            }

            container.innerHTML = this.buildQueueModalHtml();
            this.bindQueueModalActions();
        }

        bindQueueModalActions() {
            const buttons = document.querySelectorAll('.apm-reprint-btn');
            buttons.forEach((button) => {
                button.onclick = () => {
                    this.handleManualReprint(button.getAttribute('data-job-id'), button);
                };
            });

            const retireButtons = document.querySelectorAll('.apm-retire-btn');
            retireButtons.forEach((button) => {
                button.onclick = () => {
                    this.handleManualRetire(button.getAttribute('data-job-id'), button);
                };
            });
        }

        handleManualReprint(jobId, button) {
            if (!jobId) {
                return;
            }

            if (button) {
                button.disabled = true;
                button.textContent = 'Reimprimiendo...';
            }

            this.connect();

            this.reprintQueuedJob(jobId, 30000)
                .then((response) => {
                    if (global.Swal) {
                        global.Swal.fire({
                            icon: 'success',
                            title: 'Impresion enviada',
                            text: (response.CopyLabel || 'Copia enviada') + ' procesada por APM.'
                        });
                    }
                })
                .catch((error) => {
                    if (global.Swal) {
                        global.Swal.fire({
                            icon: 'error',
                            title: 'Error de reimpresion',
                            text: error && error.ErrorMessage ? error.ErrorMessage : 'No fue posible reimprimir el documento pendiente.'
                        });
                    }
                })
                .then(() => this.syncQueueItems())
                .then(() => {
                    if (this.queueModalOpen) {
                        this.openQueueModal();
                    }
                });
        }

        handleManualRetire(jobId, button) {
            if (!jobId || !this.canRetireQueue) {
                return;
            }

            const executeRetire = () => {
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Retirando...';
                }

                this.retireQueuedJob(jobId)
                    .then(() => {
                        if (global.Swal) {
                            global.Swal.fire({
                                icon: 'success',
                                title: 'Documento retirado',
                                text: 'El documento fue retirado de la cola APM y ya no requiere impresión.'
                            });
                        }
                    })
                    .catch((error) => {
                        if (global.Swal) {
                            global.Swal.fire({
                                icon: 'error',
                                title: 'Error al retirar',
                                text: error && error.ErrorMessage ? error.ErrorMessage : 'No fue posible retirar el documento de la cola APM.'
                            });
                        }
                    })
                    .then(() => this.syncQueueItems())
                    .then(() => {
                        if (this.queueModalOpen) {
                            this.openQueueModal();
                        }
                    });
            };

            if (!global.Swal) {
                executeRetire();
                return;
            }

            global.Swal.fire({
                icon: 'warning',
                title: 'Retirar documento',
                text: 'Este documento se marcará como retirado y saldrá de la cola de impresión. ¿Desea continuar?',
                showCancelButton: true,
                confirmButtonText: 'Sí, retirar',
                cancelButtonText: 'No'
            }).then((result) => {
                const isConfirmed = !!(result && (result.isConfirmed === true || result.value === true));
                if (isConfirmed) {
                    executeRetire();
                }
            });
        }

        openQueueModal() {
            this.queueModalOpen = true;

            if (!global.Swal) {
                alert('Pendientes en cola APM: ' + this.queueItems.length);
                return;
            }

            this.syncQueueItems().then(() => {
                global.Swal.fire({
                    title: 'Cola de impresion APM',
                    html: this.buildQueueModalHtml(),
                    width: 700,
                    showConfirmButton: false,
                    showCloseButton: true,
                    onOpen: () => this.bindQueueModalActions(),
                    onClose: () => {
                        this.queueModalOpen = false;
                    }
                });
            });
        }

        refreshQueueUI() {
            this.syncQueueItems();
        }
    }

    global.APM_CLIENT = global.APM_CLIENT || new APMClient();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => global.APM_CLIENT.ensureQueueUI());
    } else {
        global.APM_CLIENT.ensureQueueUI();
    }

    global.APM_TEMPLATES = global.APM_TEMPLATES || {
        ticket: {
            JobId: 'TICKET-001',
            StationId: 'CAJA_1',
            PrinterId: 'printHambuger',
            DocumentType: 'ticket_venta',
            Document: {
                company: { Name: 'Supermercado Demo', Nit: '900123456', Address: 'Calle Principal #123', Phone: '555-1234' },
                sale: {
                    COPY: 'COPIA # 1',
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
                    COPY: 'COPIA # 1',
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
                header: { Title: 'FACTURA ELECTRONICA DE VENTA', Number: 'FE-2025', COPY: 'COPIA # 1' },
                customer: { Name: 'Empresa Cliente S.A.S', Nit: '800.111.222-3', Address: 'Av. Empresarial 55' },
                totals: { Subtotal: 100000, Tax: 19000, Total: 119000 },
                cufe: 'abc1234567890def...'
            }
        },
        egreso: {
            JobId: 'UNI-001',
            StationId: 'TESORERIA',
            PrinterId: 'LX300_LOCAL',
            DocumentType: 'comprobante_egreso',
            Document: {
                cheque: {
                    Number: '002216',
                    DateInfo: {
                        Day: '15',
                        Month: '02',
                        Year: '2026'
                    },
                    PayTo: 'JUAN SEBASTIAN CAMACHO MUNOZ',
                    AmountText: 'UN MILLON DE PESOS M/CTE',
                    Amount: '1.000.000,00',
                    City: 'VALLEDUPAR'
                },
                egreso: {
                    Number: 'CE-456',
                    DateInfo: {
                        Day: '13',
                        Month: '02',
                        Year: '2025'
                    },
                    BankCode: '007',
                    ReceiverName: 'JUAN SEBASTIAN CAMACHO MUNOZ',
                    ReceiverId: '1.003.376.130',
                    Concept: [
                        'PAGO DE SERVICIO DE DESARROLLO DE SOFTWARE',
                        'APM ENERO - FEBRERO 2026',
                        'ADICIONAL LINEA 3',
                        'ADICIONAL LINEA 4'
                    ],
                    Description: 'F\nF',
                    Items: [
                        { Account: '250501', CO: '001', ThirdParty: '1121825689', Reference: '-', Debit: '$939,360,532.00', Credit: '$0.00' },
                        { Account: '11100506', CO: '001', ThirdParty: '1121825689', Reference: '-', Debit: '$0.00', Credit: '$939,360,532.00' }
                    ],
                    TotalDebit: '$939,360,532.00',
                    TotalCredit: '$939,360,532.00',
                    CreatedBy: 'S. CAMACHO'
                }
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
