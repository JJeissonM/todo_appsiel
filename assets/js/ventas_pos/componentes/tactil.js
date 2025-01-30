$(document).ready(function () {

    $(document).on('click','#btn_mostrar_resumen_operaciones', function(){
        $(this).hide();
        $('#btn_ocultar_resumen_operaciones').show();
        $('#div_resumen_operaciones').fadeIn(500);
    });

    $(document).on('click','#btn_ocultar_resumen_operaciones', function(){
        $(this).hide();
        $('#btn_mostrar_resumen_operaciones').show();
        $('#div_resumen_operaciones').fadeOut(500);
    });

});
