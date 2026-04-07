(function (global, $) {
    'use strict';

    const TIMEOUT_MS = 30000;

    const getValue = (selector) => {
        const node = document.querySelector(selector);
        return node ? String(node.value || '').trim() : '';
    };

    const buildFallbackMeta = () => ({
        core_empresa_id: parseInt(getValue('#pago_cxp_core_empresa_id') || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt(getValue('#pago_cxp_core_tipo_transaccion_id') || '0', 10) || 0,
        core_tipo_doc_app_id: parseInt(getValue('#pago_cxp_core_tipo_doc_app_id') || '0', 10) || 0,
        consecutivo: parseInt(getValue('#pago_cxp_consecutivo') || '0', 10) || 0,
        document_type: 'comprobante_egreso',
        document_label: getValue('#pago_cxp_document_label') || 'Pago CxP'
    });

    const getPayloadUrl = () => getValue('#pago_cxp_apm_payload_url');

    const isApmSelected = () => {
        const select = document.getElementById('formato_impresion_id');
        return !!select && select.value === 'apm';
    };

    const requestPayload = () => {
        const url = getPayloadUrl();
        if (!url) {
            return Promise.reject({ ErrorMessage: 'No se configuró la URL del payload APM para pagos CxP.' });
        }

        if ($ && typeof $.getJSON === 'function') {
            return $.getJSON(url);
        }

        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json'
            }
        }).then((response) => response.json().then((json) => {
            if (!response.ok) {
                throw {
                    ErrorMessage: json && json.message ? json.message : 'No fue posible preparar el payload APM.'
                };
            }
            return json;
        }));
    };

    const ensureApmClient = () => {
        if (!global.APM_CLIENT || typeof global.APM_CLIENT.enqueuePrintJob !== 'function') {
            throw { ErrorMessage: 'APM no está disponible en este navegador.' };
        }

        global.APM_CLIENT.connect();
        return global.APM_CLIENT;
    };

    const showLoading = () => {
        if (!global.Swal) {
            return;
        }

        global.Swal.fire({
            title: 'Enviando a APM...',
            text: 'Por favor espere.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: function () {
                global.Swal.showLoading();
            }
        });
    };

    const closeLoading = () => {
        if (global.Swal && global.Swal.isVisible()) {
            global.Swal.close();
        }
    };

    const showResult = (type, title, text) => {
        if (!global.Swal) {
            alert(text);
            return;
        }

        global.Swal.fire({
            icon: type,
            title: title,
            text: text
        });
    };

    const printViaApm = () => {
        let client;

        try {
            client = ensureApmClient();
        } catch (error) {
            const errorMessage = error && error.ErrorMessage ? error.ErrorMessage : 'APM no está disponible.';
            showResult('error', 'Error', errorMessage);
            return;
        }

        showLoading();

        requestPayload()
            .then((response) => {
                const payload = response && response.payload ? response.payload : null;
                const documentMeta = response && response.document_meta ? response.document_meta : buildFallbackMeta();
                const printerId = getValue('#apm_printer_id_pago_cxp');

                if (payload && !payload.PrinterId && printerId !== '') {
                    payload.PrinterId = printerId;
                }

                return client.enqueuePrintJob({
                    payload: payload,
                    documentMeta: documentMeta,
                    timeoutMs: TIMEOUT_MS
                });
            })
            .then((response) => {
                closeLoading();
                showResult('success', 'Impresión enviada', 'Pago CxP enviado a APM. ' + (response.CopyLabel || ''));
            })
            .catch((error) => {
                closeLoading();
                const errorMessage = error && error.ErrorMessage ? error.ErrorMessage : 'No fue posible imprimir el pago CxP por APM.';
                const pendingMessage = error && error.QueueJobId ? ' Quedó pendiente en la cola APM para reimpresión manual.' : '';
                showResult('error', 'Error de impresión', errorMessage + pendingMessage);
            });
    };

    $(document).ready(function () {
        if (getValue('#usar_apm_pago_cxp') !== '1') {
            return;
        }

        if (global.APM_CLIENT && typeof global.APM_CLIENT.connect === 'function') {
            global.APM_CLIENT.connect();
        }

        $('#btn_print').on('click', function (event) {
            if (!isApmSelected()) {
                return true;
            }

            event.preventDefault();
            printViaApm();
            return false;
        });
    });
})(window, window.jQuery);
