const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const flush = async () => { for (let i = 0; i < 30; i++) await Promise.resolve(); };

function setupConnection(mode) {
    let now = 0;
    let sequence = 0;
    const timers = new Map();
    const sockets = [];
    const sent = [];
    const setTimeout = (callback, delay) => {
        const id = ++sequence;
        timers.set(id, { callback, at: now + delay });
        return id;
    };
    const clearTimeout = id => timers.delete(id);
    class WebSocket {
        constructor(url) {
            this.url = url;
            this.readyState = 0;
            sockets.push(this);
            if (sockets.length === 1 && mode === 'throw') throw Error('Blocked URL');
            if (mode !== 'offline' && url === 'ws://localhost:7000/websocket/') {
                setTimeout(() => { this.readyState = 1; this.onopen(); }, 50);
            }
        }
        close() { this.readyState = 3; setTimeout(() => this.onclose({ code: 1006 }), 100); }
        send(payload) {
            sent.push(JSON.parse(payload));
            setTimeout(() => this.onmessage({ data: JSON.stringify({ JobId: sent[sent.length - 1].JobId, Status: 'OK' }) }), 10);
        }
    }
    Object.assign(WebSocket, { CONNECTING: 0, OPEN: 1, CLOSED: 3 });
    const context = {
        Promise, WebSocket, setTimeout, clearTimeout,
        Date: class extends Date { static now() { return now; } },
        console: { error() {} },
        document: { readyState: 'loading', addEventListener() {}, getElementById() { return { value: 'ws://unreachable:7000/websocket/' }; } }
    };
    context.window = context;
    vm.runInNewContext(fs.readFileSync('assets/js/apm/client.js', 'utf8'), context);
    const client = context.APM_CLIENT;
    client.refreshQueueUI = () => {};
    client.syncQueueItems = () => Promise.resolve([]);
    const requests = [];
    client.request = (method, path) => {
        requests.push(path);
        return Promise.resolve({ job: { id: 9, copy_number: 0, copy_label: 'ORIGINAL' }, payload: { JobId: 'CE-9' } });
    };
    return {
        client, sockets, sent, requests,
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
        }
    };
}

async function testConnections() {
    for (const mode of ['hanging', 'throw']) {
        for (const action of ['enqueue', 'reprint']) {
            const env = setupConnection(mode);
            const pending = action === 'enqueue'
                ? env.client.enqueuePrintJob({ payload: { JobId: 'CE-9' }, timeoutMs: 30000 })
                : env.client.reprintQueuedJob(9, 30000);
            await env.advance(4000);
            assert.strictEqual((await pending).QueueJobId, 9);
            assert.strictEqual(env.sent.length, 1);
            assert.strictEqual(env.sockets[1].url, 'ws://localhost:7000/websocket/');
            assert(env.requests.includes('apm_print_queue/9/mark_printed'));
            assert(!env.requests.includes('apm_print_queue/9/mark_failed'));
        }
    }
    const env = setupConnection('offline');
    let error;
    const pending = env.client.reprintQueuedJob(9, 30000).catch(value => { error = value; });
    await env.advance(6000);
    assert.strictEqual(error, undefined, 'El envio debe respetar los 30 segundos configurados');
    await env.advance(24500);
    await pending;
    assert.strictEqual(error.QueueJobId, 9);
    assert.strictEqual(env.sent.length, 0);
    assert(env.requests.includes('apm_print_queue/9/mark_failed'));
}

async function testShow(result) {
    const notifications = [];
    let click;
    let resolveJob, rejectJob, resolvePayload, rejectPayload;
    let enqueued = 0;
    const job = new Promise((resolve, reject) => { resolveJob = resolve; rejectJob = reject; });
    const values = { '#usar_apm_pago_cxp': '1', '#pago_cxp_apm_payload_url': '/payload', '#apm_printer_id_pago_cxp': 'tesoreria' };
    const document = {
        querySelector: selector => ({ value: values[selector] || '' }),
        getElementById: () => ({ value: 'apm' })
    };
    const $ = selector => ({
        ready(callback) { callback(); },
        on(event, callback) { if (selector === '#btn_print' && event === 'click') click = callback; },
        attr() { return '/print?formato_impresion_id=apm'; }
    });
    $.getJSON = () => ({
        done(callback) { resolvePayload = callback; return this; },
        fail(callback) { rejectPayload = callback; return this; },
        then() { throw Error('No usar la cadena Deferred de jQuery 2 para esperar promesas nativas'); }
    });
    const context = { document, Promise, jQuery: $, Swal: {
        fire(message) { notifications.push(message); }, isVisible() { return true; }, close() {}
    }, APM_CLIENT: { connect() {}, enqueuePrintJob() { enqueued++; return job; } } };
    context.window = context;
    vm.runInNewContext(fs.readFileSync('assets/js/tesoreria/pagos_cxp_apm.js', 'utf8'), context);
    click({ preventDefault() {} });
    if (result === 'payloadError') {
        rejectPayload({ responseJSON: { message: 'Payload invalido' } });
    } else {
        resolvePayload({ payload: { PrinterId: 'tesoreria' } });
        await flush();
        assert.strictEqual(enqueued, 1);
        assert(!notifications.some(n => n.icon === 'success'), 'No mostrar exito antes de recibir confirmacion APM');
        if (result === 'success') resolveJob({ CopyLabel: 'ORIGINAL' });
        else rejectJob({ ErrorMessage: 'APM no conectado o en reconexion.', QueueJobId: 9 });
    }
    await flush();
    const last = notifications[notifications.length - 1];
    assert.strictEqual(last.icon, result === 'success' ? 'success' : 'error');
    if (result === 'success') assert(last.text.includes('ORIGINAL'));
    if (result === 'failure') assert(last.text.includes('APM no conectado') && last.text.includes('cola APM'));
    if (result === 'payloadError') { assert.strictEqual(enqueued, 0); assert.strictEqual(last.text, 'Payload invalido'); }
}

(async () => {
    await testConnections();
    for (const result of ['success', 'failure', 'payloadError']) await testShow(result);
    console.log('OK: conexion alternativa, impresion y reenvio, timeout y confirmacion/errores desde pagos CxP.');
})().catch(error => { console.error(error); process.exitCode = 1; });
