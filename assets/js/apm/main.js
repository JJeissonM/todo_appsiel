// main.js - Demo UI para Appsiel Print Manager (APM)

((global) => {
    const initDemoUI = () => {
        const statusSpan = document.getElementById('status');
        const messagesDiv = document.getElementById('messages');
        const sendPayloadButton = document.getElementById('sendPayloadButton');
        const reconnectButton = document.getElementById('reconnectButton');

        if (!statusSpan || !messagesDiv || !sendPayloadButton || !reconnectButton) {
            return;
        }

        if (!global.APM_CLIENT || !global.APM_TEMPLATES) {
            console.error('APM demo requiere assets/js/apm/client.js antes de main.js');
            return;
        }

        let activePayloadId = 'payloadTicket';

        const logMessage = (message, type = 'info') => {
            const p = document.createElement('p');
            p.className = `log-${type}`;
            p.textContent = message;
            messagesDiv.appendChild(p);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        };

        global.APM_CLIENT.setLogger((message, type) => {
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

        global.openTab = (tabName) => {
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach((btn) => btn.classList.remove('active'));

            const clickedBtn = Array.from(buttons).find((b) => b.dataset.tab === tabName);
            if (clickedBtn) {
                clickedBtn.classList.add('active');
            }

            const contents = document.querySelectorAll('.tab-content');
            contents.forEach((content) => content.classList.remove('active'));

            const selectedId = `payload${tabName.charAt(0).toUpperCase()}${tabName.slice(1)}`;
            document.getElementById(selectedId).classList.add('active');
            activePayloadId = selectedId;
        };

        sendPayloadButton.addEventListener('click', (event) => {
            event.preventDefault();
            const activeTextarea = document.getElementById(activePayloadId);
            if (activeTextarea) {
                global.APM_CLIENT.send(activeTextarea.value);
            } else {
                logMessage('Error: No se encontro el area de texto activa.', 'error');
            }
        });

        reconnectButton.addEventListener('click', () => {
            if (global.APM_CLIENT.socket) {
                global.APM_CLIENT.socket.close(1000, 'ReConexion solicitada por el usuario');
            }
            global.APM_CLIENT.connect();
        });

        global.openTab('ticket');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDemoUI);
    } else {
        initDemoUI();
    }
})(window);
