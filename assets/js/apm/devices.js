// Ajustes visuales del CRUD de dispositivos APM.
$(document).ready(function () {
    function toggleApmDeviceFields() {
        var type = $('#device_type').val();
        var printerFields = [
            'ip_address',
            'paper_width_mm',
            'code_page',
            'beep_after_print',
            'open_drawer_after_print',
            'cut_after_print'
        ];
        var scaleFields = [
            'serial_port',
            'baud_rate',
            'data_bits',
            'parity',
            'stop_bits'
        ];

        function setVisible(fieldName, visible) {
            var $field = $('#' + fieldName);
            var $container = $field.closest('.form-group');

            if (!$container.length) {
                $container = $field.closest('.row');
            }

            if (visible) {
                $container.show();
            } else {
                $container.hide();
            }
        }

        printerFields.forEach(function (fieldName) {
            setVisible(fieldName, type === 'printer');
        });

        scaleFields.forEach(function (fieldName) {
            setVisible(fieldName, type === 'scale');
        });
    }

    $('#device_type').on('change', toggleApmDeviceFields);
    toggleApmDeviceFields();
});
