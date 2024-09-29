$(document).ready(function () {

    $('#btn_anular_pedido').click(function (event){
        event.preventDefault();
        
        $("#modal_usuario_supervisor").modal({backdrop: "static"});      
    });
    
    $(document).on('click', '.btn_alfanumerico_teclado', function () {
        if($('input[name="modal_text_input"]:checked').val() == 'email')
        {
            var value = $('#email_supervisor').val();
            $('#email_supervisor').val( value + $(this).text() );
        }else{
            var value = $('#password_supervisor').val();
            $('#password_supervisor').val( value + $(this).text() );
        }
    });

    $(document).on('click', '#email_supervisor', function () {
        $("input[name=modal_text_input][value=email]").attr('checked', 'checked');
    });

    $(document).on('click', '#password_supervisor', function () {
        $("input[name=modal_text_input][value=password]").attr('checked', 'checked');
    });

    $(document).on('click', '#btn_clear_teclado_alfanumerico', function () {
        if($('input[name="modal_text_input"]:checked').val() == 'email')
        {
            $('#email_supervisor').val('');
        }else{
            $('#password_supervisor').val('');
            $('#password_supervisor').val( value + $(this).text() );
        }
        
        $('#lbl_error_password_supervisor').hide();
    });
    
    $("#modal_usuario_supervisor").on('shown.bs.modal', function(){
        $('#lbl_modal_usuario_supervisor').text( 'Confirmación para anular pedido ' + $('#btn_anular_pedido').attr('data-pedido_label') );
        $('#lbl_error_password_supervisor').hide();
        $('#password_supervisor').val('');
        $('#email_supervisor').val('');        
        $('#email_supervisor').focus();
    });

});