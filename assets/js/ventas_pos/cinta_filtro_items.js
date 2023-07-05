function draw_items(lista_items)
{
    $('.filtros').html('');
    var num_items = 0;
    $.each(lista_items,function(key,item)
    {
        if (num_items > 15) {
            //return false;
        }

        var label = item.referencia + ' ' + item.descripcion;
        $('.filtros').append('<button onclick="mandar_codigo4(' + item.id + ')" class="icono_item" data-label_item="' + label.toLowerCase() + '">' + label  + ' $' + new Intl.NumberFormat("de-DE").format( get_precio(item.id).toFixed(2)) + '</button>');
        num_items++;
    });
}

function filterItems(query) {

    var textoFiltro = query.toLowerCase().replace(/\s/g,"-");

    if(textoFiltro == 'todos'){
        $('div.filtros button.hidden').fadeIn('slow').removeClass('hidden');
    }else{
        // Por Cada button hijo de la Div filtros
        $('.filtros button').each(function()
        {
            if ($(this).attr('data-label_item').indexOf(query) === -1) { // No existe
                $(this).fadeOut('normal').addClass('hidden');
            } else if ($(this).attr('data-label_item').indexOf(query) > -1) {
                $(this).fadeIn('slow').removeClass('hidden');
            }
        });
    }
    return false;

    /*
    return productos.filter(function(item) {
        return item.descripcion.toLowerCase().indexOf(query.toLowerCase()) > -1;
    })
    */
}

$(document).ready(function () {

    $('#textinput_filter_item').on('keyup', function (event) {

        $("[data-toggle='tooltip']").tooltip('hide');
        $('#popup_alerta').hide();

        var codigo_tecla_presionada = event.which || event.keyCode;

        switch (codigo_tecla_presionada) {
            case 27: // 27 = ESC

                $('#efectivo_recibido').select();
                $("html, body").animate({scrollTop: "870px"});
                break;
            
            case 13: // Al presionar Enter

                if ($(this).val() == '') {
                    return false;
                }

                $('#quantity').select();

                break;
            default :
                
                if ($(this).val().length < 2) {
                    $('.filtros').html('');
                    return false;
                }

                draw_items(productos);
                filterItems( $(this).val() );

                break;
        }

    });

    $(document).on('focus', '#textinput_filter_item', function () {
        $('#textinput_filter_item').select();
    });


    $(document).on('keyup', '#quantity', function (event) {
        var codigo_tecla_presionada = event.which || event.keyCode;
        if(codigo_tecla_presionada == 13){ // 13 = Enter
            $(this).next().focus();
        }
        if(codigo_tecla_presionada == 113){ // 113 = F2
            $('#textinput_filter_item').select();
        }
    });
    $(document).on('focus', '#quantity', function () {
        $('#quantity').select();
    });


    $(document).on('keyup', '.icono_item', function (event) {
        var codigo_tecla_presionada = event.which || event.keyCode;
        if(codigo_tecla_presionada == 113){ // 113 = F2
            $('#textinput_filter_item').select();
        }
    });
    $(document).on('focus', '.icono_item', function () {
        $(this).attr('style','background:#574696;color:white;');
    });

    $(document).on('blur', '.icono_item', function () {
        $(this).attr('style','background:#ddd;color:black;');
    });
});