$(document).ready(function(){

	var url;
	var formulario_lleno = false;

	var direccion = location.href;

	var documento_inicial = parseInt( $("#numero_identificacion2").val() );

	$("#id_tipo_documento_id").val('');
	$("#numero_identificacion2").focus();

	$(document).on('blur, keyup','#numero_identificacion2',function(){

		if( direccion.search("edit") == -1)
		{
			// Creando
			url = '../core/validar_numero_identificacion2/';

			validar_tercero_create( url );

		}else{
			// Editando
			url = '../../core/validar_numero_identificacion2/';

			validar_tercero_edit( url );
		}
	});

	function validar_tercero_create( url )
	{
		var documento = $("#numero_identificacion2").val();
		$('#tercero_existe').remove();
		$('#bs_boton_guardar').show();

		$.get( url + documento, function( respuesta ) 
		{
			if ( respuesta == 'tercero_no_existe' ) 
	        {
	        	//vaciar_campos_formulario();
	        	return false;
	        }

	        if ( respuesta == 'ya_inscrito' ) 
	        {
	        	vaciar_campos_formulario();
	        	$("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Esta persona ya se encuentra inscrita. Desde aquí no puede modificar sus datos.</div>');
	        	$('#bs_boton_guardar').hide();
	        	return false;
	        }

	        $("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Ya existe una persona con este número de identificación. Sus datos serán actualizados.</div>');
	        if ( confirm('Ya existe una persona con este número de identificación. \n ¿Desea cargar sus datos básicos?') )
	        {
	        	autollenar_formulario( respuesta );
	        }
		});
	}

	function validar_tercero_edit( url )
	{
		var documento = $("#numero_identificacion2").val();
		$('#tercero_existe').remove();
		$('#bs_boton_guardar').show();

		$.get( url + documento, function( respuesta ) 
		{
			if ( respuesta == 'tercero_no_existe' ) 
	        {
	        	return false;
	        }

			// Si se cambia el número de identificación por el de otro tercero existente  
	        if( parseInt( respuesta.numero_identificacion ) !== documento_inicial )
			{
				$('#bs_boton_guardar').hide();
				$("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Ya existe otra persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro.</div>');
			}
		});
	}

	function autollenar_formulario( data )
	{
		var inputs = Array.prototype.slice.call(document.querySelectorAll('#form_create input, #form_create select, #form_create textarea'));
		
		for (dataItem in data)
		{
		  inputs.map(function (inputItem) {
		  	if ( inputItem.name != 'fecha' )
			{
				return ( inputItem.name === dataItem ) ? ( inputItem.value = data[dataItem] ) : false;
			}		    
		  });
		}
		formulario_lleno = true;
	}

	function vaciar_campos_formulario()
	{
		var inputs = Array.prototype.slice.call(document.querySelectorAll('#form_create input, #form_create select, #form_create textarea'));

		inputs.map(function (inputItem) {
			if ( inputItem.name != 'fecha' && inputItem.name != 'numero_identificacion2' && inputItem.name != '_token' && inputItem.type != 'hidden' )
			{
				return inputItem.value = "";
			}
		  });
		formulario_lleno = false;
	}

});