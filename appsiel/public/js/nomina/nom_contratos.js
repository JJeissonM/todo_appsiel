(function () {
    function getSelectedRows() {
        var rows = [];
        var checks = document.querySelectorAll('input.btn-gmail-check[type="checkbox"]:checked');
        for (var i = 0; i < checks.length; i++) {
            var tr = checks[i].closest('tr');
            if (tr) {
                rows.push(tr);
            }
        }
        return rows;
    }

    function getEstadoFromRow(row) {
        var tds = row.querySelectorAll('td');
        if (!tds.length) {
            return '';
        }
        var lastTd = tds[tds.length - 1];
        return (lastTd.textContent || '').trim();
    }

    function mostrarMensaje(title, message, type) {
        if (typeof window.mensaje === 'function') {
            window.mensaje(title, message, type || 'warning');
            return;
        }
        alert(title + ' ' + message);
    }

    var originalBotonElement = window.botonElement;
    if (typeof originalBotonElement !== 'function') {
        return;
    }

    window.botonElement = function (url) {
        if (url && url.indexOf('nomina/duplicar_contrato_retirado') !== -1) {
            var rows = getSelectedRows();
            if (rows.length === 0) {
                mostrarMensaje('Alerta!', 'Debe seleccionar un contrato.', 'warning');
                return;
            }
            if (rows.length > 1) {
                mostrarMensaje('Alerta!', 'Seleccione solo un contrato para duplicar.', 'warning');
                return;
            }

            var estado = getEstadoFromRow(rows[0]);
            if (estado !== 'Retirado') {
                mostrarMensaje('Alerta!', 'Solo se pueden duplicar contratos en estado Retirado.', 'warning');
                return;
            }
        }

        return originalBotonElement(url);
    };
})();
