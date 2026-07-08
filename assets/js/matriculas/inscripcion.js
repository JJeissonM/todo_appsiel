$(document).ready(function(){

	var url;
	var formulario_lleno = false;

	var direccion = location.href;

	var documento_inicial = parseInt( $("#numero_identificacion2").val() );
	var core_tercero_id_inicial = $("#core_tercero_id").val();

	if( direccion.search("edit") == -1)
	{
		$("#id_tipo_documento_id").val('');
		$("#numero_identificacion2").focus();
	}
	else
	{
		restaurar_tipo_documento();
		setTimeout(restaurar_tipo_documento, 300);
	}

	$(document).on('blur keyup','#numero_identificacion2',function(){

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

		$.get( url + documento, { core_tercero_id: core_tercero_id_inicial }, function( respuesta ) 
		{
			if ( respuesta == 'tercero_no_existe' ) 
	        {
	        	return false;
	        }

			if ( parseInt( documento ) === documento_inicial ) 
	        {
	        	return false;
	        }

	        if ( respuesta == 'ya_inscrito' )
	        {
	        	$('#bs_boton_guardar').hide();
				$("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Ya existe otra persona con ese número de documento de identidad. Cambie el número o no podrá guardar el registro.</div>');
	        	return false;
	        }

	        if ( respuesta.id != undefined && core_tercero_id_inicial != '' && parseInt( respuesta.id ) === parseInt( core_tercero_id_inicial ) ) 
	        {
	        	return false;
	        }

			// Si se cambia el número de identificación por el de otro tercero existente  
	        if( parseInt( respuesta.numero_identificacion ) !== documento_inicial )
			{
				$('#bs_boton_guardar').hide();
				$("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Ya existe otra persona con ese número de documento de identidad. Cambie el número o no podrá guardar el registro.</div>');
			}
		});
	}

	function restaurar_tipo_documento()
	{
		var $tipo_documento = $("#id_tipo_documento_id");
		var valor_inicial = $tipo_documento.attr("data-valor-inicial");

		if ( valor_inicial == undefined || valor_inicial == '' )
		{
			return false;
		}

		if ( $tipo_documento.val() != valor_inicial )
		{
			$tipo_documento.val(valor_inicial).trigger('change');
		}
	}

	function autollenar_formulario( data )
	{
		var inputs = Array.prototype.slice.call(document.querySelectorAll('#form_create input, #form_create select, #form_create textarea'));
		
		for (dataItem in data)
		{
		  inputs.map(function (inputItem) {
		  	if ( inputItem.name != 'fecha' && inputItem.name != 'creado_por' && inputItem.name != 'modificado_por' )
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
