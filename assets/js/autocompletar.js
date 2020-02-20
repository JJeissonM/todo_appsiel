$(document).ready( function(){
	
	var caja_autocompletar;

	
	/*$('.autocompletar').on('focus',function(){
		caja_autocompletar = $(this);
    	caja_autocompletar.select();
    });
	*/

	// Al ingresar código, descripción o código de barras del producto
    $('.autocompletar').on('keyup',function(){

    	caja_autocompletar = $(this);

    	var x = event.which || event.keyCode; // Capturar la tecla presionada

		if( x == 27 ) // 27 = ESC
		{
			$('.autocompletar_sugerencias').html('');
        	$('.autocompletar_sugerencias').hide();
        	return false;
		}


		/*
			Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
	    */
		if ( x == 40) // Flecha hacia abajo
		{
			//console.log( $(".flecha_mosadasdasver:focus") );
			var item_activo = $("a.list-group-item.active");					
			item_activo.next().attr('class','list-group-item list-group-item-autocompletar active');
			item_activo.attr('class','list-group-item list-group-item-autocompletar');
			caja_autocompletar.val( item_activo.next().html() );
			return false;

		}
		
		if ( x == 38) // Flecha hacia arriba
		{
			$(".flecha_mover:focus").prev().focus();
			var item_activo = $("a.list-group-item.active");					
			item_activo.prev().attr('class','list-group-item list-group-item-autocompletar active');
			item_activo.attr('class','list-group-item list-group-item-autocompletar');
			caja_autocompletar.val( item_activo.prev().html() );
			return false;
		}

		// Al presionar Enter
		if( x == 13 )
		{
			var item = $('a.list-group-item.active');
			
			if( item.attr('data-id') === undefined )
			{
				alert('El registro ingresado no existe.');
				procesar_item("item: "+item);
				return false;
			}else{
				seleccionar_item( item );
            	return false;
			}
		}

		// Manejo código de producto o nombre
		var campo_busqueda = campo_busqueda_texto;
		if( $.isNumeric( $(this).val() ) ){
    		var campo_busqueda = campo_busqueda_numerico;
    	}

    	// Si la longitud es menor a tres, todavía no busca
	    if ( $(this).val().length < 2 ) { return false; }

    	var url = url_consulta;

		$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
			.done(function( data ) {
				// Se llena el DIV con las sugerencias que arooja la consulta
                $('.autocompletar_sugerencias').show().html(data);
                $('a.list-group-item.active').focus();
			});
    });


    //Al hacer click en alguna de las sugerencias (escoger un producto)
    var item_seleccionado;
    $(document).on('click','.list-group-item-autocompletar', function(){

    	seleccionar_item( $(this) );    	

    	return false;
    });

    function seleccionar_item( item )
    {
    	// Se almacena el objeto del item seleccionado en una variable global
    	item_seleccionado = item;

    	// Asignar descripción al TextInput
        caja_autocompletar.val( item.html() );
        caja_autocompletar.css( 'background-color','white ' );

        //Hacemos desaparecer el resto de sugerencias
        $('.autocompletar_sugerencias').html('');
        $('.autocompletar_sugerencias').hide();

        procesar_item(item);
    }

} );