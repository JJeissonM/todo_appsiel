// Listener de bascula APM para el create de ventas POS.
((global) => {
    const getValue = (selector) => {
        const element = document.querySelector(selector);
        return element ? String(element.value || '').trim() : '';
    };

    const isEnabled = () => parseInt(getValue('#apm_scale_listener_activo') || '0', 10) === 1;

    const getScaleId = () => getValue('#apm_scale_id_pos');

    const updateQuantityInput = (weightData) => {
        const input = document.getElementById('cantidad');
        if (!input || document.activeElement !== input) {
            return;
        }

        input.value = weightData.Weight;

        if (typeof global.cantidad !== 'undefined') {
            global.cantidad = parseFloat(weightData.Weight) || 0;
        }

        if (typeof global.calcular_valor_descuento === 'function') {
            global.calcular_valor_descuento();
        }

        if (typeof global.calcular_impuestos === 'function') {
            global.calcular_impuestos();
        }

        if (typeof global.calcular_precio_total === 'function') {
            global.calcular_precio_total();
        }
    };

    const startListening = () => {
        if (!isEnabled() || !global.APM_CLIENT || typeof global.APM_CLIENT.startScaleListening !== 'function') {
            return;
        }

        const scaleId = getScaleId();
        if (!scaleId) {
            return;
        }

        global.APM_CLIENT.connect();

        if (typeof global.APM_CLIENT.waitForSocketReady === 'function') {
            global.APM_CLIENT.waitForSocketReady()
                .then(() => global.APM_CLIENT.startScaleListening(scaleId))
                .catch(() => {});
            return;
        }

        global.APM_CLIENT.startScaleListening(scaleId);
    };

    const stopListening = () => {
        if (!isEnabled() || !global.APM_CLIENT || typeof global.APM_CLIENT.stopScaleListening !== 'function') {
            return;
        }

        global.APM_CLIENT.stopScaleListening();
    };

    const init = () => {
        const input = document.getElementById('cantidad');
        if (!input || !isEnabled() || !global.APM_CLIENT) {
            return;
        }

        if (typeof global.APM_CLIENT.addScaleListener === 'function') {
            global.APM_CLIENT.addScaleListener(updateQuantityInput);
        }

        input.addEventListener('focus', startListening);
        input.addEventListener('keydown', (event) => {
            const key = event.key || event.keyCode;
            if (key === 'Enter' || key === 13) {
                stopListening();
            }
        });
        input.addEventListener('blur', stopListening);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window);
