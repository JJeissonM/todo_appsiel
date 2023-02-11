function consultar_entradas_pendientes()
	{
		$('#div_entradas_pendientes').hide();
		url = '../compras_consultar_entradas_pendientes';
		$.get( url, { core_tercero_id: $('#core_tercero_id').val() } )
			.done(function( data ) {
				if ( data != 'sin_registros')
				{
					$('#div_entradas_pendientes').show();
					$('#listado_entradas_pendientes').html( data );
					$('.td_boton').show();
                	$('.btn_agregar_documento').show();
                	$('#div_ingreso_registros').hide();
				}else{
					$('#div_ingreso_registros').show();
				}
				return false;
			});/**/
	}

	$("#btn_cerrar_alert").on('click', function(){
		$('#div_entradas_pendientes').hide();
		$('#div_ingreso_registros').show();
	});
