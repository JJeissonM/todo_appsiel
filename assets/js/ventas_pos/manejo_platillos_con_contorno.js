function reset_select_items_contorno() {

    $('#item_contorno_id').html('<option value="0">+ Contorno</option>');
    var items_contorno = JSON.parse( document.getElementById('items_contorno').value );

	$.each(items_contorno,function(key,item)
    {
        if ( item.estado == 'Activo') {
            var label = item.descripcion;
            $('#item_contorno_id').append('<option value="' + item.id + '" >' + label + '</option>');
        }        
    });
}

function add_item_contorno_id(object_select)
{
    if ( object_select.val() == 0) {
        return false;
    }

    var label = $('#item_contorno_id option:selected').text();

    $('#lista_contornos').append('<li data-label_item_contorno="' + label + '">' + label + ' <button class="btn btn-danger btn-xs remove_item_contorno" data-item_contorno_id="' + object_select.val() + '"><i class="fa fa-trash"></i></button></li>');

    $('#item_contorno_id option:selected').remove();
    
    update_lista_items_contorno_ids();
}

function remove_item_contorno_id(object_button)
{
    var linea = object_button.closest("li");

    $('#item_contorno_id').append('<option value="' + object_button.attr('data-item_contorno_id') + '">' + linea.attr('data-label_item_contorno') + ' </option>')

    linea.remove();
    
    update_lista_items_contorno_ids();
}

function update_lista_items_contorno_ids()
{
    var list_object = $('#lista_contornos li');

    var lista = '';
    var is_first = true;
    list_object.each(function(i)
    {
        if (is_first) {
            lista = $(this).find('.remove_item_contorno').attr('data-item_contorno_id');
            is_first = false;
        }else{
            lista += ',' + $(this).find('.remove_item_contorno').attr('data-item_contorno_id');
        }     
    });
    
    $('#lista_oculta_items_contorno_ids').text( lista );
}

function cambiar_descripcion_item_ingresado(descripcion_item)
{
    var list_object = $('#lista_contornos li');

    list_object.each(function(i)
    {
        descripcion_item += ' + ' + $(this).attr('data-label_item_contorno');
    });

    return descripcion_item;
}

function reset_component_items_contorno()
{
    reset_select_items_contorno();
    $('#lista_contornos').html('');
}

$(document).ready(function () {

    $('#inv_producto_id').after( '<div class="well"><input type="hidden" name="items_contorno" id="items_contorno" value=""><div><ul id="lista_contornos"></ul></div><div><select id="item_contorno_id" style="width:100%;"><option value="0">+ Contorno</option></select></div></div>' );

    $.get( url_raiz + '/inv_get_items_contorno' )
            .done(function (data) {
                document.getElementById('items_contorno').value =  JSON.stringify(data);
                reset_select_items_contorno();
            });

    $(document).on('change', '#item_contorno_id', function () {
        add_item_contorno_id($(this));

        $('#cantidad').select();
        
        return false;
    });

    $(document).on('click', '.remove_item_contorno', function () {
        remove_item_contorno_id($(this));
        
        return false;
    });
});