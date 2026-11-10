const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const fields = {
    '#email': {value: 'principal@example.com', data: {}},
    '#email_lbl': {value: 'principal@example.com', data: {}},
    '#contacto_cliente_id': {selected: '', data: {}}
};
let changed;
function wrap(field) {
    return {
        data(key, value) { if (arguments.length === 2) field.data[key] = value; return field.data[key]; },
        val(value) { if (arguments.length) { field.value = value; return this; } return field.value; },
        on(event, callback) { changed = callback; },
        find() { return {attr() { return field.selected; }}; }
    };
}
function $(selector) {
    if (typeof selector === 'function') { selector(); return; }
    return wrap(typeof selector === 'string' ? fields[selector] : selector);
}
vm.runInNewContext(fs.readFileSync(__dirname + '/../../../assets/js/facturacion_electronica/contact_email.js', 'utf8'), {jQuery: $});
const contact = fields['#contacto_cliente_id'];
contact.selected = ' contacto@example.com ';
changed.call(contact);
assert.strictEqual(fields['#email'].value, 'contacto@example.com');
assert.strictEqual(fields['#email_lbl'].value, 'contacto@example.com');
contact.selected = '';
changed.call(contact);
assert.strictEqual(fields['#email'].value, 'principal@example.com');
fields['#email'].data['email-cliente'] = 'nuevo@example.com';
contact.selected = 'otro@example.com';
changed.call(contact);
assert.strictEqual(fields['#email'].value, 'otro@example.com');
contact.selected = '';
changed.call(contact);
assert.strictEqual(fields['#email'].value, 'nuevo@example.com');
console.log('OK: contacto, correo vacío, quitar contacto y cambio de cliente');
