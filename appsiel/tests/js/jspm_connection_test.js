const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const flush = async () => { for (let i = 0; i < 30; i++) await Promise.resolve(); };

function setup(options = {}) {
    let now = 0, sequence = 0, resolveSend;
    const timers = new Map();
    const jobs = [];
    const status = { Closed: 0, Open: 1, Blocked: 2, WaitingForUserResponse: 3 };
    const manager = {
        websocket_status: status.Closed, starts: 0,
        start() {
            this.starts++;
            this.WS = { autoReconnect: this.auto_reconnect };
            return options.startError ? Promise.reject(new Error('Conexion rechazada')) : Promise.resolve();
        }
    };
    const context = {
        Promise, Error,
        Date: class extends Date { static now() { return now; } },
        setTimeout(callback, delay) { const id = ++sequence; timers.set(id, { callback, at: now + delay }); return id; },
        clearTimeout(id) { timers.delete(id); },
        JSPM: {
            JSPrintManager: manager, WSStatus: status,
            InstalledPrinter: function (name) { this.name = name; },
            ClientPrintJob: function () {
                this.sendToClient = () => {
                    jobs.push(this);
                    if (options.sendError) return Promise.reject('La conexion se cerro');
                    if (options.noResponse) return new Promise(resolve => { resolveSend = resolve; });
                    return Promise.resolve('enviado');
                };
            }
        }
    };
    context.window = context;
    vm.createContext(context);
    vm.runInContext(fs.readFileSync('assets/js/ventas_pos/external_print/jspm_client.js', 'utf8'), context);
    const client = context.APPSIEL_JSPM;
    return {
        client, manager, status, jobs, timers, context,
        async advance(ms) {
            const end = now + ms;
            await flush();
            while (true) {
                const entry = [...timers].sort((a, b) => a[1].at - b[1].at)[0];
                if (!entry || entry[1].at > end) break;
                now = entry[1].at;
                timers.delete(entry[0]);
                entry[1].callback();
                await flush();
            }
            now = end;
            await flush();
        },
        lateResponse() { resolveSend('enviado'); }
    };
}

(async () => {
    const slow = setup();
    slow.client.start();
    let completed = false;
    const commands = new Uint8Array([27, 64, 42]);
    slow.client.send('COCINA', commands).then(() => { completed = true; });
    await slow.advance(1000);
    assert.strictEqual(slow.jobs.length, 0, 'Esperar el handshake antes de enviar');
    slow.manager.websocket_status = slow.status.WaitingForUserResponse;
    await slow.advance(2000);
    assert.strictEqual(slow.jobs.length, 0, 'Esperar la autorizacion sin fallar inmediatamente');
    slow.manager.websocket_status = slow.status.Open;
    await slow.advance(100);
    assert.strictEqual(completed, true);
    assert.strictEqual(slow.jobs.length, 1);
    assert.strictEqual(slow.jobs[0].clientPrinter.name, 'COCINA');
    assert.strictEqual(slow.jobs[0].binaryPrinterCommands, commands);
    assert.strictEqual(slow.manager.starts, 1, 'Compartir la conexion iniciada al cargar la pagina');
    assert.strictEqual(slow.timers.size, 0);

    slow.manager.websocket_status = slow.status.Closed;
    slow.client.send('COCINA', commands);
    await slow.advance(1800);
    assert.strictEqual(slow.jobs.length, 1);
    slow.manager.websocket_status = slow.status.Open;
    await slow.advance(200);
    assert.strictEqual(slow.jobs.length, 2, 'Reanudar el nuevo envio despues de reconectar');
    assert.strictEqual(slow.manager.starts, 1, 'No competir con la reconexion de la biblioteca');
    assert.strictEqual(slow.manager.WS.autoReconnect, true);

    const offline = setup({ startError: true });
    let failure;
    offline.client.send('COCINA', commands).catch(error => { failure = error; });
    await offline.advance(12000);
    assert.ok(failure.message.includes('No se pudo conectar'));
    assert.strictEqual(offline.jobs.length, 0);
    assert.strictEqual(offline.timers.size, 0);
    offline.manager.websocket_status = offline.status.Open;
    await offline.advance(1000);
    assert.strictEqual(offline.jobs.length, 0, 'No enviar solicitudes vencidas al reconectar');
    await offline.client.send('COCINA', commands);
    assert.strictEqual(offline.jobs.length, 1, 'Permitir el reintento explicito');

    for (const state of ['Blocked', 'WaitingForUserResponse']) {
        const denied = setup();
        denied.manager.websocket_status = denied.status[state];
        let error;
        denied.client.send('COCINA', commands).catch(value => { error = value; });
        await denied.advance(state === 'Blocked' ? 0 : 12000);
        assert.ok(error.message.includes(state === 'Blocked' ? 'bloqueó' : 'autorización'));
        assert.strictEqual(denied.jobs.length, 0);
    }

    const uncertain = setup({ noResponse: true });
    uncertain.manager.websocket_status = uncertain.status.Open;
    let timeout;
    uncertain.client.send('COCINA', commands).catch(error => { timeout = error; });
    await uncertain.advance(30000);
    assert.ok(timeout.message.includes('Revise si la comanda salió'));
    assert.strictEqual(uncertain.jobs.length, 1);
    uncertain.lateResponse();
    await uncertain.advance(30000);
    assert.strictEqual(uncertain.jobs.length, 1, 'No repetir trabajos cuya impresion no se pudo confirmar');
    assert.strictEqual(uncertain.timers.size, 0);

    const rejected = setup({ sendError: true });
    rejected.manager.websocket_status = rejected.status.Open;
    await assert.rejects(rejected.client.send('COCINA', commands), /Revise la impresora/);
    assert.strictEqual(rejected.jobs.length, 1);
    assert.strictEqual(rejected.timers.size, 0);
    await assert.rejects(rejected.client.send('', commands), /Configure la impresora/);
    delete rejected.context.JSPM;
    await assert.rejects(rejected.client.send('COCINA', commands), /Recargue la página/);
    console.log('OK: JSPM conexion lenta, autorizacion, reconexion, tiempos limite y envio unico sin reintentos automaticos.');
})().catch(error => { console.error(error); process.exit(1); });
