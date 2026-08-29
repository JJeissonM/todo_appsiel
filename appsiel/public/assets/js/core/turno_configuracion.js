(function () {
    'use strict';

    function syncContext() {
        var type = document.querySelector('[name="contexto_tipo"]');
        var id = document.querySelector('[name="contexto_id"]');
        if (!type || !id || id.disabled) {
            return;
        }
        if (type.value === '*') {
            id.value = '0';
            id.setAttribute('disabled', 'disabled');
            var hidden = document.getElementById('turno_contexto_global_id');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.id = 'turno_contexto_global_id';
                hidden.name = 'contexto_id';
                id.parentNode.appendChild(hidden);
            }
            hidden.value = '0';
        } else {
            id.removeAttribute('disabled');
            var globalId = document.getElementById('turno_contexto_global_id');
            if (globalId && globalId.parentNode) {
                globalId.parentNode.removeChild(globalId);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var type = document.querySelector('[name="contexto_tipo"]');
        if (type) {
            type.addEventListener('change', syncContext);
            syncContext();
        }
    });
}());
