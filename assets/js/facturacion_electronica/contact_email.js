(function ($) {
    'use strict';

    $(function () {
        var email = $('#email');
        if (email.data('email-cliente') === undefined) {
            email.data('email-cliente', email.val() || '');
        }

        $('#contacto_cliente_id').on('change.emailContactoFactura', function () {
            var selectedEmail = $(this).find('option:selected').attr('data-email') || '';
            selectedEmail = selectedEmail.replace(/[\s\u00a0\u200b\ufeff]+/g, '');
            var value = selectedEmail || email.data('email-cliente') || '';
            email.val(value);
            $('#email_lbl').val(value);
        });
    });
})(jQuery);
