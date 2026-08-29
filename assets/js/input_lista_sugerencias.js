$(document).ready( function(){
	

	$(document).on('focus', '.text_input_sugerencias', function(e){
		e.preventDefault;
		$(this).select();
	});

	function closeAllLists( div_lista_sugerencias )
	{
		if( $('#lista_sugerencias').attr('class') !== div_lista_sugerencias.id )
		{
			$("#lista_sugerencias").remove();	
		}
	  }


	document.addEventListener("click", function (e) {
		
		if( $('#lista_sugerencias').html() !== undefined )
		{
			// Se envía el elemento donde se hizo click
			closeAllLists( e.target );
		}

	  });

	$(document).on('keyup', '.text_input_sugerencias', function(event){

		crear_div_lista_sugerencias( $(this) );

		var codigo_tecla_presionada = event.which || event.keyCode;

    	var item_activo = $("a.list-group-item.active");

    	switch( codigo_tecla_presionada )
    	{
    		case 27:// 27 = ESC
				$('#lista_sugerencias').html('');
    			break;

    		case 40:// Flecha hacia abajo

				// Si es el útimo item, entonces no se mueve hacia abajo
				if( item_activo.attr('data-ultimo_item') == 1 )
				{
					return false;
				}
			
				item_activo.next().attr('class','list-group-item list-group-item-sugerencia active');
				item_activo.attr('class','list-group-item list-group-item-sugerencia');
    			break;

    		case 38:// Flecha hacia arriba

				// Si es el primer item, entonces no se mueve hacia arriba
				if( item_activo.attr('data-primer_item') == 1 )
				{
					return false;
				}

				item_activo.prev().attr('class','list-group-item list-group-item-sugerencia active');
				item_activo.attr('class','list-group-item list-group-item-sugerencia');
    			break;

    		case 13:// Al presionar Enter

    			if ( $(this).val() == '' )
			    { 
			    	$('#lista_sugerencias').remove();
			    	return false;
			    }
			    
				ejecutar_funcion_tecla_enter( item_activo, $(this) );
    			
    			break;

			default :
				// Si no se presiona tecla especial, se muestra listado de sugerencias
				var obj_input = $(this);
				var hidden_input = obj_input.next('input[type="hidden"]');
				var selected_label = obj_input.attr('data-selected-label') || '';
				if (hidden_input.val() !== '' && obj_input.val() === selected_label) {
					return false;
				}
				obj_input.removeAttr('data-registro_id');
				hidden_input.val('');
				obj_input.attr('data-selected-label', '');

				// Una búsqueda vacía no consulta el backend.
			    if ( $(this).val() == '' )
			    { 
					clearTimeout(obj_input.data('suggestions-timeout'));
					var empty_request = obj_input.data('suggestions-request');
					if (empty_request && empty_request.readyState !== 4) {
						empty_request.abort();
					}
			    	$('#lista_sugerencias').remove();
			    	return false;
			    }

				clearTimeout(obj_input.data('suggestions-timeout'));
				var previous_request = obj_input.data('suggestions-request');
				if (previous_request && previous_request.readyState !== 4) {
					previous_request.abort();
				}

				obj_input.data('suggestions-timeout', setTimeout(function () {
					$('#div_cargando').show();
					var url = obj_input.attr('data-url_busqueda');
					var parameters = { texto_busqueda: obj_input.val() };
					var extra_fields = (obj_input.attr('data-ajax-fields') || '').split(',');
					$.each(extra_fields, function(index, field_name) {
						field_name = $.trim(field_name);
						if (field_name === '') {
							return;
						}
						var field = $('[name="' + field_name + '"]').first();
						if (field.length) {
							parameters[field_name] = field.val();
						}
					});

					var request = $.get(url, parameters)
						.done(function(data) {
							$('#lista_sugerencias').show().html(data);
							$('a.list-group-item.active').focus();
						})
						.always(function() {
							$('#div_cargando').hide();
						});
					obj_input.data('suggestions-request', request);
				}, 250));
				break;
    	}	

	});

    function ejecutar_funcion_tecla_enter( item, obj_text_input )
    {
    	if( item.attr('data-registro_id') === undefined && obj_text_input.val() != '' )
		{
			obj_text_input.css( 'background-color', '#FF8C8C' );
			$('#lista_sugerencias').html('');
			alert('No existe ninguna coincidencia.');
		}else{
			seleccionar_sugerencia( item, obj_text_input );
		}
    }


    //Al hacer click en alguna de las sugerencias (escoger un producto)
    $(document).on('click','.list-group-item-sugerencia', function(){
    	seleccionar_sugerencia( $(this), $(this).parent().parent().prev() );
    });



	function seleccionar_sugerencia( item_sugerencia, obj_text_input )
    {
		// Asignar descripción e ID al TextInput
		var selected_label = $.trim(item_sugerencia.text());
		obj_text_input.attr( 'data-registro_id', item_sugerencia.attr( 'data-registro_id' ) );
		obj_text_input.val(selected_label);
		obj_text_input.attr('data-selected-label', selected_label);
        obj_text_input.css( 'background-color','white' );

        $('#lista_sugerencias').remove();

        // Siempre el input text debe llevar un campo hidden despues donde almacena el value del registro_id
        obj_text_input.next().val( obj_text_input.attr('data-registro_id') );

		// Función propia de cada formulario de creación, cuando exista.
        if (typeof ejecutar_acciones_con_item_sugerencia === 'function') {
			ejecutar_acciones_con_item_sugerencia(item_sugerencia, obj_text_input);
        }

    }



	function crear_div_lista_sugerencias( text_input_sugerencias )
	{
		if( $('#lista_sugerencias').html() === undefined )
		{
			// Se le asigna como atributo CLASS el atributo ID del text_input para validar su remoción
			text_input_sugerencias.after('<div id="lista_sugerencias" class="' + text_input_sugerencias.attr('id') + '" style="position: absolute; z-index: 99999;"> </div>');
		}
	}

	$(document).on('change', '[name="pdv_id"]', function() {
		$('.turno-operativo-ajax').each(function() {
			$(this).val('').removeAttr('data-registro_id').attr('data-selected-label', '');
			$(this).next('input[type="hidden"]').val('');
		});
	});
	

} );
