function reset_select_items_contorno() {

    $('#item_contorno_id').html('<option value="0">+ Contorno</option>');
    var items_contorno = JSON.parse( document.getElementById('items_contorno').value );

    // contornos_permitidos se llena en los Catalogos
    var producto = contornos_permitidos.find(item => item.item_maneja_contorno_id === inv_producto_id );
    
    var items_contornos_permitidos = []
    if ( typeof producto == 'object' ) {
        items_contornos_permitidos = producto.ids_lista_contornos_permitidos
    }    

	$.each(items_contorno,function(key,item)
    {
        if ( !items_contornos_permitidos.includes(item.id) ) {
            return;
        }

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

    $('#lista_contornos').append('<li data-label_item_contorno="' + label + '">' + label + ' <button class="btn btn-danger btn-xs remove_item_contorno" data-item_contorno_id="' + object_select.val() + '" onclick="remove_item_contorno_id(this)"><i class="fa fa-trash"></i></button></li>');

    $('#item_contorno_id option:selected').remove();

    var fila = object_select.closest("tr");
    fila.find('.btn_confirm_contornos').fadeIn(500);
}

function remove_item_contorno_id(object_button)
{
    var linea = object_button.closest("li");

    $('#item_contorno_id').append('<option value="' + object_button.dataset.item_contorno_id + '">' + linea.dataset.label_item_contorno + ' </option>')

    linea.remove();
}

function update_lista_items_contorno_ids(object_button)
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
    
    var fila = object_button.closest("tr");

    fila.find('.lista_oculta_items_contorno_ids').text(lista);

    return lista;
}

/**
 * 
 * @param {*} object_button 
 */
function cambiar_descripcion_item_ingresado(object_button)
{
    var fila = object_button.closest("tr");

    var descripcion_item = fila.find('.lbl_producto_descripcion').text().replace(/\s+/g, ' ').trim();

    var list_object = $('#lista_contornos li');

    list_object.each(function(i)
    {
        descripcion_item += ' + ' + $(this).attr('data-label_item_contorno');
    });

    fila.find('.lbl_producto_descripcion').text( descripcion_item );
}

/**
 * 
 * @param {*} object_button 
 */
function cambiar_precio_item_ingresado(object_button)
{
    var fila = object_button.closest("tr");

    var descripcion_item = fila.find('.lbl_producto_descripcion').text().replace(/\s+/g, ' ').trim();

    var producto = productos.find(item => item.descripcion === descripcion_item );

    console.log(descripcion_item,producto );

    fila.find('.precio_unitario').text( producto.precio_venta );

    calcular_precio_total_lbl_quantity(fila);
    calcular_totales();
}

function reset_component_items_contorno()
{
    reset_select_items_contorno();
    $('#lista_contornos').html('');
}

function show_form_add_contorno(object_button)
{
    var fila = object_button.closest("tr");
    
	fila.find('.lbl_producto_descripcion').after( '<div class="well" id="form_lista_contornos"><div><ul id="lista_contornos"></ul></div><div><select id="item_contorno_id" style="width:100%;"><option value="0">+ Contorno</option></select><br><br></div><button class="btn btn-success btn-xs btn_confirm_contornos" style="display:none;"><i class="fa fa-btn fa-check"></i>Confirmar</button><button class="btn btn-default btn-xs btn_cancelar_add_contornos"><i class="fa fa-btn fa-cancel"></i>Cancelar</button></div>' );
    
    inv_producto_id = parseInt( fila.find('.inv_producto_id').text() );

    reset_select_items_contorno();
}

function validar_producto_con_contorno()
{        
    var is_ok = true;
    $('.linea_registro').each(function(){
        if( $(this).find('.btn_add_contorno').length == 1 )
        {
            is_ok = false;
            return false
        }
        
        if( $(this).find('.btn_confirm_contornos').length == 1 )
        {
            is_ok = false;
            return false
        }
        
        if( $(this).find('.btn_cancelar_add_contornos').length == 1 )
        {
            is_ok = false;
            return false
        }
    });
    
    return is_ok
}

$(document).ready(function () {

    $('#manejar_platillos_con_contorno').after( '<input type="hidden" name="items_contorno" id="items_contorno" value="">' );

    $.get( url_raiz + '/inv_get_items_contorno' )
            .done(function (data) {
                document.getElementById('items_contorno').value =  JSON.stringify(data);
            });

    $(document).on('change', '#item_contorno_id', function () {
        add_item_contorno_id($(this));

        $('#cantidad').select();
        
        return false;
    });

    $(document).on('click', '.btn_add_contorno', function () {
        
        if( document.getElementById('form_lista_contornos') != null )
        {
            return false;
        }
        
        show_form_add_contorno($(this));

        $(this).remove();
        
        return false;
    });

    $(document).on('click', '.btn_cancelar_add_contornos', function () {

        //event.preventDefault();

        var fila = $(this).closest("tr");

        fila.find('.btn_eliminar').after("<button class='btn btn-primary btn-xs btn_add_contorno'><i class='fa fa-btn fa-plus'></i></button>");

        document.getElementById('form_lista_contornos').remove();
        
        return false;
    });

    $(document).on('click', '.btn_confirm_contornos', function () {
        cambiar_descripcion_item_ingresado($(this));

        cambiar_precio_item_ingresado( $(this) );
        
        update_lista_items_contorno_ids($(this));

        var fila = $(this).closest("tr");

        fila.find('.btn_add_contorno').remove();
        
        document.getElementById('form_lista_contornos').remove();
        
        return false;
    });
});