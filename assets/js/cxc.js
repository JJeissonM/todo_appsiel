$(document).ready( function(){
	
	var sumas_iguales = 0;
	var LineaNum = 0;
	var debito, credito;
	var tercero_id;

	$('#core_tipo_doc_app_id').change(function(){
		$('#fecha').focus();
	});

	/*
	**	Agregar NUEVA línea de ingreso de datos
	*/
	$("#btn_nueva_linea").click(function(event){
		event.preventDefault();
		nueva_linea_ingreso_datos();
		//alert('stop');		
    });

    function nueva_linea_ingreso_datos()
    {
    	LineaNum ++;
    	$('#div_cargando').fadeIn();
		
		var url = '../cxc_get_fila/' + LineaNum;
		$.get( url, function( datos ) {
	        $('#div_cargando').hide();

	        $('#ingreso_registros').find('tbody:first').append( datos );

	        $('#combobox_servicios').focus();

	        $('#btn_nueva_linea').hide();
		});
    }

   	$(document).on('change', '#combobox_servicios', function() {
		if ( $(this).val() != '' ) {
			$('#combobox_terceros').focus();
		}else{ 
			$(this).focus();
		}
	} );

   	$(document).on('change', '#combobox_terceros', function() {
		if ( $(this).val() != '' ) {
			$('#col_valor').focus();
		}else{ 
			$(this).focus();
		}
	} );


   	// Agregar una línea
	$(document).on('click', '.btn_confirmar', function() 
	{
		var fila = $(this).closest("tr");
		var ok = validar_linea();
		if( ok ) {
			var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
	        var servicio_id = $('#combobox_servicios').val();
	        var servicio = $( "#combobox_servicios option:selected" ).text();
	        var tercero_id = $('#combobox_terceros').val();
	        var tercero = $( "#combobox_terceros option:selected" ).text();
	        if ( tercero_id == "") 
	        {
	        	tercero_id = "0a3p00";
	        }
	        var valor = $('#col_valor').val();
	        
	        if(valor == ''){
	        	valor = 0; // Para no sumar una caja de texto vacía
	        }

	        $('#ingreso_registros').find('tbody:last').append('<tr id="fila_'+LineaNum+'" >' +
															'<td id="servicio_id_'+LineaNum+'" style="display: none;">' + servicio_id + '</td>' +
															'<td id="tercero_id_'+LineaNum+'" style="display: none;">' + tercero_id + '</td>' +
															'<td id="linea_valor_'+LineaNum+'" style="display: none;" class="valor">' + valor + '</td>' +
															'<td id="servicio_'+LineaNum+'">' + servicio + '</td>'+
															'<td id="tercero_'+LineaNum+'">' + tercero + '</td>'+
															'<td id="valor_'+LineaNum+'">$' + new Intl.NumberFormat("de-DE").format(valor) + '</td>'+
															'<td>'+btn_borrar+'</td>'+
															'</tr>');
	       	

	       	calcular_totales();
	       	fila.remove();
	       	nueva_linea_ingreso_datos();
		}

	});

	/*
	** Al eliminar una fila
	*/
	// Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
	// es un elemento agregado por ajax (despues de que se cargó la página)
	$(document).on('click', '.btn_eliminar', function() {
		var fila = $(this).closest("tr");
		fila.remove();
		$('#btn_nueva_linea').show();
		calcular_totales();
		$('#btn_nueva_linea').focus();
	});


	// GUARDAR Cuenta de cobro
	$('#btn_guardar').click(function(event){
		event.preventDefault();

		if ( $('#total_valor_total').text() == 0) {
			alert('No ha ingresado registros.');
			$('#btn_nueva_linea').focus();
			return false;
		}

		asignar_codigo_tercero();

		// Se obtienen todos los datos del formulario y se envían
		// Se validan nuevamente los campos requeridos
		var control = 1;
		$( "*[required]" ).each(function() {
			if ( $(this).val() == "" ) {
			  $(this).focus();
			  control = 0;
			  alert('Este campo es requerido.' + $(this).attr('name'));
			  return false;
			}else{
			  control = 1;
			}
		});

		if (control==1)
		{
			// Desactivar el click del botón
			$( this ).off( event );

			// Eliminar fila de ingreso de registro vacia
			var object = $('#combobox_servicios').val();	
			if( typeof object == typeof undefined){
				// Si no hay linea de ingreso de registros
				// Todo bien
				//alert('Todo bien.');
			}else{
				var fila = $('#combobox_servicios').closest("tr");
				fila.remove();
			}

			// Se asigna la tabla de ingreso de registros a un campo hidden
			var tabla_registros_documento = $('#ingreso_registros').tableToJSON();
			$('#tabla_registros_documento').val( JSON.stringify(tabla_registros_documento) );

			// Enviar formulario
			$('#form_create').submit();
		}else{
			$(this).removeAttr('disabled');
			alert('Faltan campos por llenar.');
		}
	});

	
	function validar_linea(){
		var ok;

		if ( $('#combobox_servicios').val() != '' ) {

			var valor = $('#col_valor').val();
			
			if ( valor == '' ) {
				alert('Debe ingresar un valor.');
				$('#col_valor').focus();
				ok = false;
			}else{
				if ( $.isNumeric(valor)  && valor > 0 ) {
					ok = true;
				}else{
					$('#col_valor').attr('style','background-color:#FF8C8C;');
					$('#col_valor').focus();
					ok = false;
				}	
			}
		}else{
			alert('Debe seleccionar un servicio.');
			$('#combobox_servicios').focus();
			ok = false;
		}
		return ok;
	}

	function calcular_totales(){
		var sum = 0.0;

		// Sumar columna de los débitos
		sum = 0.0;
		$('.valor').each(function()
		{
		    var cadena = $(this).text();
		    sum += parseFloat( cadena );
		});
		$('#total_valor_total').text( sum );
		$('#total_valor_aux').text( "$" + new Intl.NumberFormat("de-DE").format( sum ) );
	}

	function validar_requeridos(){
		$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = true;
					}
				});
		return control;
	}


	function validar_valor_aplicar(celda){
		var fila = celda.closest("tr");
		var ok;

		var valor = celda.val();

		if( $.isNumeric( valor ) ){
			valor = parseFloat( valor );
		}

		var saldo_pendiente = fila.find('td.col_saldo_pendiente').text();
		saldo_pendiente = parseFloat( saldo_pendiente.replace( /\./g, "" ) );

		console.log(valor);
		console.log(saldo_pendiente);


		if( valor > 0  && valor <= saldo_pendiente) {
			celda.attr('style','background-color:white;');
			ok = true;
		}else{
			celda.attr('style','background-color:#FF8C8C;');
			celda.focus();
			ok = false;
		}

		return ok;
	}

	function calcular_totales_cruce(){
		var sum, cadena;
		sum = 0;
		$('.valor_total').each(function()
		{
		    
		    cadena = $(this).text();
		    //console.log( cadena );
		    sum+=parseFloat(cadena);
		});

		$('#total_valor_total').text( sum );
		$('#total_valor_aux').text( "$" + new Intl.NumberFormat("de-DE").format( sum ) );

		//console.log( sum );

		if ( sum == 0 ) {
	        $('#btn_guardar2').show();
	        $('#btn_guardar2').focus();
		}else{
			$('#btn_guardar2').hide();
		}
	}

	function deshabilitar_campos_form_create()
	{

		// Se cambia el de name al campo core_tercero_id, pues como está desabilitado 
		// el SUBMIT del FORM no lo envía en el REQUEST
		$('#fecha').attr('disabled','disabled');

		// se oculta la caja de texto del terceor y se muestra el select real
		$('.custom-combobox').hide();

		$('#core_tercero_id_no').show();
		$('#core_tercero_id_no').attr('disabled','disabled');

		$('#tercero_id').show();
		$('#tercero_id').attr('disabled','disabled');

		$('#tipo_recaudo').attr('disabled','disabled');


	}

	function habilitar_campos_form_create()
	{
		$('#fecha').removeAttr('disabled');
		
		$('.custom-combobox').show();
		$('#core_tercero_id_no').hide();
		$('#tercero_id').hide();

		$('#core_tercero_id_no').removeAttr('disabled');
		
		$('#tercero_id').removeAttr('disabled');

		$('#tipo_recaudo').removeAttr('disabled');
	}

	function asignar_codigo_tercero()
	{
		tercero_id = $('#tercero_id').val().split('a3p0');
		$('#core_tercero_id').val( tercero_id[0] );
		$('#codigo_referencia_tercero').val( tercero_id[1] );
	}

} );