//var item_activo;
$(document).ready( function(){
	

	$(document).on('focus', '.text_input_sugerencias', function(e){
		e.preventDefault;
		$(this).select();
	});

	/*$(document).on('blur', '.text_input_sugerencias', function(e){
		
		//var codigo_tecla_presionada = event.which || event.keyCode;


		if( codigo_tecla_presionada != 38 && codigo_tecla_presionada != 40 && se_puede_quitar_lista  ) // flecha arriba (38), flecha abajo (40)
		{
			$('#lista_sugerencias').remove();
		}
	});*/

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

	$(document).on('keyup', '.text_input_sugerencias', function(){

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
				//$('#text_input_sugerencias').val( item_activo.next().html() );
    			break;

    		case 38:// Flecha hacia arriba

				// Si es el primer item, entonces no se mueve hacia arriba
				if( item_activo.attr('data-primer_item') == 1 )
				{
					return false;
				}

				item_activo.prev().attr('class','list-group-item list-group-item-sugerencia active');
				item_activo.attr('class','list-group-item list-group-item-sugerencia');
				//$('#text_input_sugerencias').val( item_activo.prev().html() );
    			break;

    		case 13:// Al presionar Enter

    			if ( $(this).val() == '' )
			    { 
			    	//$('#lista_sugerencias').html('');
			    	$('#lista_sugerencias').remove();
			    	return false;
			    }
			    
    			window[ejecutar_funcion_tecla_enter( item_activo, $(this) ) ];
    			
    			break;

    		default :
    			// Si no se presiona tecla especial, se muestra listado de sugerencias

		    	// Si la longitud es menor a dos, todavía no busca
			    if ( $(this).val() == '' )
			    { 
			    	//$('#lista_sugerencias').html('');
			    	$('#lista_sugerencias').remove();
			    	return false;
			    }

			    $('#div_cargando').show();
	    		var url = $(this).attr('data-url_busqueda');

				$.get( url, { texto_busqueda: $(this).val() } )
					.done(function( data ) {
			    		$('#div_cargando').hide();
						// Se llena el DIV con las sugerencias que arooja la consulta
		                $('#lista_sugerencias').show().html(data);
		                
		                $('a.list-group-item.active').focus();
					});
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
		obj_text_input.attr( 'data-registro_id', item_sugerencia.attr( 'data-registro_id' ) );
        obj_text_input.val( item_sugerencia.html() ); // 'la la la'
        obj_text_input.css( 'background-color','white' );

        $('#lista_sugerencias').remove();

        // Siempre el input text debe llevar un campo hidden despues donde almacena el value del registro_id
        obj_text_input.next().val( obj_text_input.attr('data-registro_id') );

		// Función propia de cada formulario de creación
        window[ ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input ) ];

    }



	function crear_div_lista_sugerencias( text_input_sugerencias )
	{
		if( $('#lista_sugerencias').html() === undefined )
		{
			// Se le asigna como atributo CLASS el atributo ID del text_input para validar su remoción
			text_input_sugerencias.after('<div id="lista_sugerencias" class="' + text_input_sugerencias.attr('id') + '" style="position: absolute; z-index: 99999;"> </div>');
		}
	}	
	

} );