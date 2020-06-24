$(document).ready(function(){

	$('#proveedor_input').focus();
	$('#saldo_pendiente').attr('readonly','readonly');

	$('#proveedor_input').on('focus',function(){
    	$(this).select();
    });

	$("#proveedor_input").after('<div id="proveedores_suggestions"> </div>');

	// Al ingresar código, descripción o código de barras del producto
    $('#proveedor_input').on('keyup',function(){

    	var x = event.which || event.keyCode; // Capturar la tecla presionada

		if( x == 27 ) // 27 = ESC
		{
			$('#proveedores_suggestions').html('');
        	$('#proveedores_suggestions').hide();
        	return false;
		}


		/*
			Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
	    */
		if ( x == 40) // Flecha hacia abajo
		{
			var item_activo = $("a.list-group-item.active");					
			item_activo.next().attr('class','list-group-item list-group-item-proveedor active');
			item_activo.attr('class','list-group-item list-group-item-proveedor');
			$('#proveedor_input').val( item_activo.next().html() );
			return false;

		}
			if ( x == 38) // Flecha hacia arriba
		{
			$(".flecha_mover:focus").prev().focus();
			var item_activo = $("a.list-group-item.active");					
			item_activo.prev().attr('class','list-group-item list-group-item-proveedor active');
			item_activo.attr('class','list-group-item list-group-item-proveedor');
			$('#proveedor_input').val( item_activo.prev().html() );
			return false;
		}

		// Al presionar Enter
		if( x == 13 )
		{
			var item = $('a.list-group-item.active');
			
			if( item.attr('data-proveedor_id') === undefined )
			{
				alert('El proveedor ingresado no existe.');
			}else{
				seleccionar_proveedor( item );
			}
		}

		// Manejo código de producto o nombre
		var campo_busqueda = 'descripcion';
		if( $.isNumeric( $(this).val() ) ){
    		var campo_busqueda = 'numero_identificacion';
    	}

    	// Si la longitud es menor a tres, todavía no busca
	    if ( $(this).val().length < 2 ) { return false; }

    	var url = '../compras_consultar_proveedores';

		$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
			.done(function( data ) {
				// Se llena el DIV con las sugerencias que arooja la consulta
                $('#proveedores_suggestions').show().html(data);
                $('a.list-group-item.active').focus();
			});
    });


    //Al hacer click en alguna de las sugerencias (escoger un producto)
    $(document).on('click','.list-group-item-proveedor', function(){
    	seleccionar_proveedor( $(this) );
    	return false;
    });

    $('#doc_proveedor_prefijo').on('keyup',function(){
    	// Al presionar Enter
		if( x == 13 )
		{
			$('#doc_proveedor_consecutivo').focus();
		}
	});

    $('#doc_proveedor_consecutivo').on('keyup',function(){
    	// Al presionar Enter
		if( x == 13 )
		{
			$('#fecha').focus();
		}
	});

    $('#valor_documento').on('keyup',function(){
    	if ( validar_input_numerico( $(this) ) )
    	{
    		$('#saldo_pendiente').val( $(this).val() );
    	}
	});

    $('#valor_pagado').on('keyup',function(){

    	if ( $('#valor_documento').val() == '')
    	{
    		alert('Primero debe ingresar un valor para el documento.');
    		$(this).val('');
    		$('#valor_documento').focus();
    		return false;
    	}

    	if ( validar_input_numerico( $(this) ) )
    	{
    		$('#saldo_pendiente').val( $('#valor_documento').val() - $(this).val() );
    	}
	});

    function seleccionar_proveedor(item_sugerencia)
    {

		// Asignar descripción al TextInput
        $('#proveedor_input').val( item_sugerencia.html() );
        $('#proveedor_input').css( 'background-color','white ' );

        // Asignar Campos ocultos
        $('#proveedor_id').val( item_sugerencia.attr('data-proveedor_id') );
        $('#referencia_tercero_id').val( item_sugerencia.attr('data-proveedor_id') );
        $('#core_tercero_id').val( item_sugerencia.attr('data-core_tercero_id') );


        var forma_pago = 'contado';
        var dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
        if ( dias_plazo > 0 ) { forma_pago = 'credito'; }

        // Para llenar la fecha de vencimiento
        var fecha = new Date( $('#fecha').val() );
		fecha.setDate( fecha.getDate() + (dias_plazo + 1) );
		
		var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
		var dia = fecha.getDate();// + 1; // Se le suma 1,

        if( mes < 10 )
        {
        	mes = '0' + mes;
        }

        if( dia < 10 )
        {
        	dia = '0' + dia;
        }
        $('#fecha_vencimiento').val( fecha.getFullYear() + '-' +  mes + '-' + dia );


        //Hacemos desaparecer el resto de sugerencias
        $('#proveedores_suggestions').html('');
        $('#proveedores_suggestions').hide();

        $('#doc_proveedor_prefijo').focus();
    }


});