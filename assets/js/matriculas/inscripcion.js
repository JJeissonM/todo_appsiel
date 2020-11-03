$(document).ready(function(){

	var url;
	var datos;

	var direccion = location.href;

	var documento_inicial = parseInt( $("#numero_identificacion2").val() );

	$(document).on('blur, keyup','#numero_identificacion2',function(){
		
		get_datos_tercero( get_url_validacion_tercero() );

        if ( datos == '' ) 
        {
        	return false;
        	vaciar_campos_formulario();
        }

        // Si se está editando y se cambia el número de identificación por el de otro tercero existente  
        if( direccion.search("edit") !== -1 && parseInt( datos.numero_identificacion ) !== documento_inicial )
		{
			$('#bs_boton_guardar').hide();
			alert( "Ya existe otra persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro." );
		}

		$('#bs_boton_guardar').show();
        autollenar_formulario( datos );

	});

	function set_datos_tercero( respuesta )
	{
		datos = respuesta;
	}


	function get_datos_tercero( url )
	{
		var documento = $("#numero_identificacion2").val();		

		$.get( url + documento, function( respuesta ) 
		{
			set_datos_tercero( respuesta );
		});
	}

	function get_url_validacion_tercero()
	{
		if( direccion.search("edit") == -1)
		{
			// creando
			return '../core/validar_numero_identificacion2/';
		}else{
			// editando
			return '../../../core/validar_numero_identificacion2/';
		}
	}

	function autollenar_formulario( data )
	{
		var inputs = Array.prototype.slice.call(document.querySelectorAll('#form_create input, #form_create select, #form_create textarea'));
		console.log( data );
		for (dataItem in data)
		{
		  inputs.map(function (inputItem) {
		  	if ( inputItem.name != 'fecha' )
			{
				return ( inputItem.name === dataItem ) ? ( inputItem.value = data[dataItem] ) : false;
			}		    
		  });
		}
	}

	function vaciar_campos_formulario()
	{
		var inputs = Array.prototype.slice.call(document.querySelectorAll('#form_create input, #form_create select, #form_create textarea'));

		inputs.map(function (inputItem) {
			if ( inputItem.name != 'fecha' && inputItem.name != 'numero_identificacion2' )
			{
				return inputItem.value = "";
			}
		  });
	}

});